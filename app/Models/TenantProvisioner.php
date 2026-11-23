<?php

namespace App\Models;

use Database\Seeders\PermissionSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\TenantSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class TenantProvisioner
{
    /**
     * Create a fully seeded organization and make it the active tenant.
     */
    public function provision(string $name): Tenant
    {
        return DB::transaction(function () use ($name): Tenant {
            $tenant = Tenant::create([
                'name' => $name,
                'slug' => $this->uniqueSlug($name),
                'status' => 'active',
                'subscription_status' => 'trialing',
                'trial_ends_at' => now()->addDays(14),
            ]);

            app(TenantContext::class)->set($tenant);

            (new PlanSeeder)->run();
            (new TenantSeeder)->run();
            (new PermissionSeeder)->run();

            $freePlan = Plan::where('slug', 'free')->firstOrFail();

            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $freePlan->id,
                'status' => 'trialing',
                'billing_interval' => 'monthly',
                'current_period_start' => now(),
                'trial_ends_at' => now()->addDays(14),
            ]);

            return $tenant;
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'tenant';
        $slug = $base;
        $suffix = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}

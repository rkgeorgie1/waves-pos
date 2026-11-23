<?php

namespace Tests;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantContext;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('tenants')) {
            return;
        }

        $tenant = Tenant::firstOrCreate(
            ['slug' => 'default'],
            [
                'name' => 'Default Organization',
                'status' => 'active',
                'subscription_status' => 'active',
            ]
        );

        app(TenantContext::class)->set($tenant);

        (new PlanSeeder)->run();

        Subscription::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'plan_id' => Plan::where('slug', 'free')->firstOrFail()->id,
                'status' => 'active',
                'billing_interval' => 'monthly',
                'current_period_start' => now(),
            ]
        );
    }
}

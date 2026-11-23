<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantContext;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'default'],
            [
                'name' => 'Airwaves Wholesalers',
                'status' => 'active',
                'subscription_status' => 'active',
            ]
        );

        app(TenantContext::class)->set($tenant);

        $this->call(PlanSeeder::class);
        $this->call(TenantSeeder::class);
        $this->call(PermissionSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $email = (string) config('app.seed_admin_email');
        $password = config('app.seed_admin_password');

        if (! $password) {
            $password = Str::password(20);
            $this->command?->warn("Generated admin password for {$email}: {$password}");
        }

        User::firstOrCreate(['email' => $email], [
            'name' => 'Administrator',
            'password' => Hash::make($password),
            'role_id' => $adminRole->id,
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);
    }
}

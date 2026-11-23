<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Single-location starter plan.',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'currency' => 'KES',
                'max_users' => 2,
                'max_products' => 25,
                'max_warehouses' => 1,
                'max_sales_per_month' => 50,
                'features' => ['pos', 'inventory', 'basic_reports'],
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'For a growing retail shop.',
                'monthly_price' => 29,
                'yearly_price' => 290,
                'currency' => 'KES',
                'max_users' => 5,
                'max_products' => 500,
                'max_warehouses' => 2,
                'max_sales_per_month' => 2000,
                'features' => ['pos', 'inventory', 'reports', 'exports'],
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'description' => 'For multi-location wholesalers.',
                'monthly_price' => 79,
                'yearly_price' => 790,
                'currency' => 'KES',
                'max_users' => 20,
                'max_products' => 5000,
                'max_warehouses' => 10,
                'max_sales_per_month' => 20000,
                'features' => ['pos', 'inventory', 'reports', 'exports', 'multi_warehouse'],
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Unlimited usage with priority support.',
                'monthly_price' => 199,
                'yearly_price' => 1990,
                'currency' => 'KES',
                'max_users' => null,
                'max_products' => null,
                'max_warehouses' => null,
                'max_sales_per_month' => null,
                'features' => ['pos', 'inventory', 'reports', 'exports', 'multi_warehouse', 'priority_support'],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreignId('plan_id')->constrained('plans');
            $table->string('status')->default('trialing');
            $table->string('billing_interval')->default('monthly');
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // Existing merchants keep working on the free plan until they subscribe.
        $freePlanId = DB::table('plans')->where('slug', 'free')->value('id');

        if ($freePlanId === null) {
            $freePlanId = DB::table('plans')->insertGetId([
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Single-location starter plan.',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'currency' => 'USD',
                'max_users' => 2,
                'max_products' => 25,
                'max_warehouses' => 1,
                'max_sales_per_month' => 50,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (DB::table('tenants')->pluck('id') as $tenantId) {
            DB::table('subscriptions')->insert([
                'tenant_id' => $tenantId,
                'plan_id' => $freePlanId,
                'status' => 'active',
                'billing_interval' => 'monthly',
                'current_period_start' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

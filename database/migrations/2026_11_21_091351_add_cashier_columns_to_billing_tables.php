<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
        });

        Schema::table('plans', function (Blueprint $table): void {
            $table->string('stripe_price_id')->nullable();
            $table->string('stripe_yearly_price_id')->nullable();
        });

        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->string('type')->default('default')->after('tenant_id');
            $table->string('stripe_id')->nullable()->unique()->after('type');
            $table->string('stripe_status')->nullable()->after('stripe_id');
            $table->string('stripe_price')->nullable()->after('stripe_status');
            $table->integer('quantity')->nullable()->after('stripe_price');
            $table->timestamp('ends_at')->nullable()->after('trial_ends_at');

            $table->index(['tenant_id', 'stripe_status']);
        });

        Schema::create('subscription_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_id');
            $table->string('stripe_id')->unique();
            $table->string('stripe_product');
            $table->string('stripe_price');
            $table->string('meter_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('meter_event_name')->nullable();
            $table->timestamps();

            $table->index(['subscription_id', 'stripe_price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_items');

        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropIndex(['tenant_id', 'stripe_status']);
            $table->dropColumn([
                'type', 'stripe_id', 'stripe_status', 'stripe_price', 'quantity', 'ends_at',
            ]);
        });

        Schema::table('plans', function (Blueprint $table): void {
            $table->dropColumn(['stripe_price_id', 'stripe_yearly_price_id']);
        });

        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropIndex(['stripe_id']);
            $table->dropColumn(['stripe_id', 'pm_type', 'pm_last_four']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (! Schema::hasColumn('purchases', 'discount_type')) {
                $table->string('discount_type')->default('none')->after('status');
            }

            if (! Schema::hasColumn('purchases', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_type');
            }

            if (! Schema::hasColumn('purchases', 'shipping_charges')) {
                $table->decimal('shipping_charges', 15, 2)->default(0)->after('tax');
            }

            if (! Schema::hasColumn('purchases', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('balance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'payment_method')) {
                $table->dropColumn('payment_method');
            }

            if (Schema::hasColumn('purchases', 'shipping_charges')) {
                $table->dropColumn('shipping_charges');
            }

            if (Schema::hasColumn('purchases', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }

            if (Schema::hasColumn('purchases', 'discount_type')) {
                $table->dropColumn('discount_type');
            }
        });
    }
};

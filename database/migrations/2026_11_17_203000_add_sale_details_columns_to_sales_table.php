<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_status');
            }
            if (! Schema::hasColumn('sales', 'shipping_status')) {
                $table->string('shipping_status')->nullable()->after('payment_method');
            }
            if (! Schema::hasColumn('sales', 'shipping_details')) {
                $table->text('shipping_details')->nullable()->after('shipping_status');
            }
            if (! Schema::hasColumn('sales', 'staff_note')) {
                $table->text('staff_note')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'shipping_status', 'shipping_details', 'staff_note']);
        });
    }
};

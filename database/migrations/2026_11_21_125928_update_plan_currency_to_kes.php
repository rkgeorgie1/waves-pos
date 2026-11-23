<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->string('currency', 3)->default('KES')->change();
        });

        DB::table('plans')->where('currency', 'USD')->update(['currency' => 'KES']);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->string('currency', 3)->default('USD')->change();
        });

        // Existing prices are denominated in KES, so they keep their currency.
    }
};

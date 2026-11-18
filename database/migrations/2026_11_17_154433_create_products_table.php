<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('image')->nullable();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('retail_price', 15, 2)->default(0);
            $table->decimal('wholesale_price', 15, 2)->default(0);
            $table->decimal('contractor_price', 15, 2)->default(0);
            $table->string('vat_category')->default('standard');
            $table->decimal('vat_rate', 5, 2)->default(16);
            $table->integer('min_stock_level')->default(0);
            $table->integer('warranty_period')->nullable();
            $table->unsignedBigInteger('default_supplier_id')->nullable();
            $table->boolean('track_serial')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

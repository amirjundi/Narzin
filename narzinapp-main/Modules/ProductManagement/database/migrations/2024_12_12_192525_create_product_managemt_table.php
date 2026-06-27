<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name_arabic');
            $table->string('name_german');
            $table->string('slug_arabic')->unique();
            $table->string('slug_german')->unique();
            $table->text('description_arabic')->nullable();
            $table->text('description_german')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->timestamps();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });


        Schema::create('products_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('image');
            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });



        
        // Variant Attributes table (for color, size, etc.)
        Schema::create('variant_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name_arabic');
            $table->string('name_german');
            $table->timestamps();
        });

        // Product Variants table
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->date('expiry_date')->nullable();
            $table->integer('expiry_days')->nullable();
            $table->string('sku')->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_out_of_stock')->default(true);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        // Variant Values table (to store actual values like 'red', '42')
        Schema::create('variant_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_variants_id');
            $table->unsignedBigInteger('variant_attribute_id');
            $table->string('value'); // e.g., 'red', '42'
            $table->timestamps();

            $table->foreign('product_variants_id')->references('id')->on('product_variants')->onDelete('cascade');
            $table->foreign('variant_attribute_id')->references('id')->on('variant_attributes')->onDelete('cascade');
        });

        // Pivot table to connect variants with their attribute values
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_values');
        Schema::dropIfExists('variant_values');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('variant_attributes');
        Schema::dropIfExists('products');
    }
};
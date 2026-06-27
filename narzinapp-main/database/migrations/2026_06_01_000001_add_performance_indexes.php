<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('vendor_id');
            $table->index('is_active');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('is_active');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('payment_status');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('vendor_id');
            $table->index('product_id');
        });
        
        Schema::table('reviews', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['vendor_id']);
            $table->dropIndex(['is_active']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['is_active']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['payment_status']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['vendor_id']);
            $table->dropIndex(['product_id']);
        });
        
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['user_id']);
        });
    }
};

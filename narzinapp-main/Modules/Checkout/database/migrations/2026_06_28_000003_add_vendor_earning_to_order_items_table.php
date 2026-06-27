<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('vendor_base_subtotal', 10, 2)->nullable();
            $table->decimal('vendor_commission_amount', 10, 2)->nullable();
            $table->decimal('vendor_discount_absorbed', 10, 2)->nullable();
            $table->decimal('vendor_earning', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['vendor_base_subtotal', 'vendor_commission_amount', 'vendor_discount_absorbed', 'vendor_earning']);
        });
    }
};

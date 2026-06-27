<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Track when coupon/wallet were applied to prevent double application
            $table->timestamp('coupon_applied_at')->nullable()->after('paid_at');
            $table->timestamp('wallet_deducted_at')->nullable()->after('coupon_applied_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['coupon_applied_at', 'wallet_deducted_at']);
        });
    }
};
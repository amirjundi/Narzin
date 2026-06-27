<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('action'); // created, stock_reserved, payment_verified, expired, stock_released, refunded, etc.
            $table->string('old_payment_status')->nullable();
            $table->string('new_payment_status')->nullable();
            $table->string('old_order_status')->nullable();
            $table->string('new_order_status')->nullable();
            $table->json('data')->nullable(); // Additional data (stock changes, refund amount, etc.)
            $table->string('triggered_by'); // system, user, webhook, cron
            $table->unsignedBigInteger('user_id')->nullable(); // Who triggered (null for system/webhook)
            $table->string('ip_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_audits');
    }
};
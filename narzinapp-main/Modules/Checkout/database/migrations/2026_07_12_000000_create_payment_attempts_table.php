<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('gateway');
            $table->enum('status', ['initiated', 'success', 'failed']);
            $table->string('response_code')->nullable()->index();
            $table->decimal('amount', 12, 2)->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};

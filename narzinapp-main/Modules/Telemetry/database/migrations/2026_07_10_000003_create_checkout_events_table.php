<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_events', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('step', ['checkout_start', 'address', 'shipping', 'payment', 'placed']);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_events');
    }
};

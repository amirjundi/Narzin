<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_events', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->enum('action', ['add', 'remove', 'update']);
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_events');
    }
};

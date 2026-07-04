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
        Schema::create('user_product_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Null if guest user');
            $table->string('session_id')->index()->comment('To track guests');
            $table->integer('dwell_time_seconds')->default(0)->comment('How long the user looked at this product');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            // If users table is soft-deleted, we might just set null, or cascade. Cascade is fine for hard deletes.
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Index for fast lookups by user or session
            $table->index(['user_id', 'product_id']);
            $table->index(['session_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_product_views');
    }
};

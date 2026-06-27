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
        Schema::create('shipment_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->enum('status', [
                'pending',
                'collecting',
                'collected',
                'shipped',
                'delivered'
            ])->default('pending');
            $table->foreignId('admin_id')->constrained('users');
            $table->text('notes')->nullable();
            $table->unsignedInteger('total_items')->default(0);
            $table->unsignedInteger('collected_items')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_batches');
    }
};

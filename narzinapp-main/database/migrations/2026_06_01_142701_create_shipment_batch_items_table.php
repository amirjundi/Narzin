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
        Schema::create('shipment_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_batch_id')->constrained('shipment_batches')->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained('order_items');
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->enum('collection_status', [
                'pending',
                'collected',
                'unavailable'
            ])->default('pending');
            $table->timestamp('collected_at')->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('users');
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Prevent an item from being in multiple active batches
            $table->unique(['order_item_id', 'shipment_batch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_batch_items');
    }
};

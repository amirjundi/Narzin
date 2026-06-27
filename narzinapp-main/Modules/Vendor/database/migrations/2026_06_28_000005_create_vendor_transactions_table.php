<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vendor_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->index();
            $table->enum('type', ['earning', 'reversal', 'payout', 'adjustment']);
            $table->decimal('amount', 10, 2); // signed: + earning, - reversal/payout
            $table->unsignedBigInteger('order_item_id')->nullable()->index();
            $table->unsignedBigInteger('payout_id')->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->unique(['order_item_id', 'type']);
            $table->foreign('payout_id')->references('id')->on('vendor_payouts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_transactions');
    }
};

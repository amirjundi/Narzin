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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('nass_rrn')->nullable();
            $table->string('nass_int_ref')->nullable();
            $table->text('callback_data')->nullable();
            $table->timestamp('paid_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('nass_rrn');
            $table->dropColumn('nass_int_ref');
            $table->dropColumn('callback_data');
            $table->dropColumn('paid_at');
        });
    }
};

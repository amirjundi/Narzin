<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('commission_percentage', 5, 2)->nullable();
            $table->decimal('discount_absorption_percentage', 5, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['commission_percentage', 'discount_absorption_percentage']);
        });
    }
};

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
        Schema::table('user_address', function (Blueprint $table) {
            
            $table->string('title')->nullable();
            $table->boolean('is_default')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_address', function (Blueprint $table) {
            
            $table->dropColumn('title');
            $table->dropColumn('is_default');
        });
    }
};

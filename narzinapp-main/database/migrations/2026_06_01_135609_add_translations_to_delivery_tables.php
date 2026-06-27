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
        Schema::table('delivery_zones', function (Blueprint $table) {
            // Drop the unique index before the column it references, otherwise
            // the column drop fails (sqlite) or leaves an orphaned index.
            $table->dropUnique('delivery_zones_name_unique');
            $table->dropColumn('name');
        });

        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->string('name_english');
            $table->string('name_german');
            $table->string('name_arabic');
        });

        Schema::table('delivery_methods', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('delivery_methods', function (Blueprint $table) {
            $table->string('name_english');
            $table->string('name_german');
            $table->string('name_arabic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropColumn(['name_english', 'name_german', 'name_arabic']);
        });

        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->string('name')->unique();
        });

        Schema::table('delivery_methods', function (Blueprint $table) {
            $table->dropColumn(['name_english', 'name_german', 'name_arabic']);
        });

        Schema::table('delivery_methods', function (Blueprint $table) {
            $table->string('name');
        });
    }
};

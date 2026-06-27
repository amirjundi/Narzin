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
        Schema::create('user_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Customer, Vendor, Admin
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            // Add user_type_id if it doesn't exist
            if (!Schema::hasColumn('users', 'user_type_id')) {
                $table->foreignId('user_type_id')->nullable()->constrained('user_types')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'user_type_id')) {
                $table->dropForeign(['user_type_id']);
                $table->dropColumn('user_type_id');
            }
        });

        Schema::dropIfExists('user_types');
    }
};

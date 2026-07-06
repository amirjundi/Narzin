<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->boolean('is_public')->default(false);
            $table->string('group')->nullable();
            $table->timestamps();
        });
        // No seed rows: the admin Settings page creates the whatsapp_number /
        // support_hours rows on first save via updateOrCreate (is_public=true).
        // Seeding them here would collide with tests that create the same keys.
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};

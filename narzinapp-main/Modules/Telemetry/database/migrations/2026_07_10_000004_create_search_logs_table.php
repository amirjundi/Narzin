<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('query');
            $table->string('normalized_query')->index();
            $table->integer('results_count')->default(0);
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_logs');
    }
};

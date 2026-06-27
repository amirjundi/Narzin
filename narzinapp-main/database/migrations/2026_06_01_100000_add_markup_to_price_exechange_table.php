<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_exechange', function (Blueprint $table) {
            $table->decimal('markup_percentage', 5, 2)->default(0)->after('price_rate');
        });
    }

    public function down(): void
    {
        Schema::table('price_exechange', function (Blueprint $table) {
            $table->dropColumn('markup_percentage');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Why-cancelled, captured at the 4 paths that set order_status=cancelled.
            // Nullable: historical cancellations stay unlabeled; report tolerates it.
            $table->string('cancellation_reason')->nullable()->after('notes')->index();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['cancellation_reason']);
            $table->dropColumn('cancellation_reason');
        });
    }
};

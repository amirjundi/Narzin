<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ensures the `user_types` reference rows exist on every environment.
 *
 * RegisterController assigns new customers `user_type_id = 1`, which is a
 * foreign key into `user_types`. On a freshly provisioned database this row
 * only exists if the (non-production) UserTypeSeeder happens to be run — and
 * deploys run `migrate` but not `db:seed`, so production signups were failing
 * with a FK violation. Guaranteeing the data via a migration makes it part of
 * every deploy and every fresh setup, independent of seeders.
 *
 * Idempotent: insertOrIgnore leaves any existing rows untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_types')->insertOrIgnore([
            ['id' => 1, 'name' => 'Customer', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Vendor',   'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Admin',    'created_at' => now(), 'updated_at' => now()],
        ]);

        // On Postgres, inserting explicit ids does not advance the identity
        // sequence; bump it past the seeded rows so any future auto-id insert
        // does not collide with the fixed reference ids.
        if (DB::getDriverName() === 'pgsql') {
            $seq = "pg_get_serial_sequence('user_types', 'id')";
            DB::statement("SELECT setval($seq, GREATEST((SELECT MAX(id) FROM user_types), 1))");
        }
    }

    public function down(): void
    {
        // Reference data is intentionally left in place on rollback.
    }
};

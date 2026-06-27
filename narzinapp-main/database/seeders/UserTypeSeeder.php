<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['id' => 1, 'name' => 'Customer'],
            ['id' => 2, 'name' => 'Vendor'],
            ['id' => 3, 'name' => 'Admin'],
        ];

        foreach ($types as $type) {
            DB::table('user_types')->updateOrInsert(
                ['id' => $type['id']],
                ['name' => $type['name'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}

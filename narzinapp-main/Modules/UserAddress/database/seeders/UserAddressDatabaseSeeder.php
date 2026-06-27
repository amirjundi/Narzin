<?php

namespace Modules\UserAddress\Database\Seeders;

use Illuminate\Database\Seeder;

class UserAddressDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            UserAddressSeeder::class
        ]);
    }
}

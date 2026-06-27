<?php

namespace Modules\ProductManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Admin\Models\ColorTag;
use Modules\ProductManagement\Models\Product;

class ProductManagementDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->callSilent(ProductSeeder::class);
    }
}

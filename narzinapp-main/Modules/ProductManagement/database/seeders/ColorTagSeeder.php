<?php 
namespace Modules\ProductManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Admin\Models\ColorTag;

class ColorTagSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            'Reg', 
            'Green', 
            'Blue', 
            'Purple', 
            'Yellow', 
        ];

        foreach ($colors as $color) {
            ColorTag::create(['tag' => $color]);
        }
    }
}

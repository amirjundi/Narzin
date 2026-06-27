<?php 

namespace Modules\ProductManagement\Database\Seeders;


use Illuminate\Database\Seeder;
use Modules\ProductManagement\Models\VariantAttribute;

class VariantAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            // [
            //     'name_arabic' => 'اللون',
            //     'name_german' => 'Farbe',
            //     'type' => 'color',
            // ],
            // [
            //     'name_arabic' => 'الحجم',
            //     'name_german' => 'Größe',
            //     'type' => 'size',
            //     'type_values' => 'S,M,L,XL,XXL'
            // ],
            [
                'name_arabic' => 'المقاس',
                'name_german' => 'Größeas',
                'type' => 'select',
                'type_values' => '39,40,41,42,43,44,45'
            ],
            
        ];

        foreach ($attributes as $attribute) {
            VariantAttribute::create($attribute);
        }
    }
}

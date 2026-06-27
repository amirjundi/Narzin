<?php 

namespace Modules\ProductManagement\Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\ProductManagement\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Parent categories
        $parentCategories = [
            [
                'name_arabic' => 'ملابس',
                'name_german' => 'Kleidung',
                'image' => 'categories/clothing.jpg',
            ],
            [
                'name_arabic' => 'إلكترونيات',
                'name_german' => 'Elektronik',
                'image' => 'categories/electronics.jpg',
            ],
        ];

        foreach ($parentCategories as $category) {
            Category::create([
                'name_arabic' => $category['name_arabic'],
                'name_german' => $category['name_german'],
                'slug_arabic' => Str::slug($category['name_arabic']),
                'slug_german' => Str::slug($category['name_german']),
                'image' => $category['image'],
            ]);
        }

        // Child categories
        $childCategories = [
            [
                'name_arabic' => 'قمصان',
                'name_german' => 'Hemden',
                'parent_name_arabic' => 'ملابس',
                'image' => 'categories/shirts.jpg',
            ],
            [
                'name_arabic' => 'سراويل',
                'name_german' => 'Hosen',
                'parent_name_arabic' => 'ملابس',
                'image' => 'categories/pants.jpg',
            ],
            [
                'name_arabic' => 'هواتف',
                'name_german' => 'Telefone',
                'parent_name_arabic' => 'إلكترونيات',
                'image' => 'categories/phones.jpg',
            ],
        ];
        $childCategories[] = [
            'name_arabic' => 'أجهزة الكمبيوتر المحمولة',
            'name_german' => 'Laptops',
            'parent_name_arabic' => 'إلكترونيات',
            'image' => 'categories/laptops.jpg',
        ];

        $childCategories[] = [
            'name_arabic' => 'أحذية',
            'name_german' => 'Schuhe',
            'parent_name_arabic' => 'ملابس',
            'image' => 'categories/shoes.jpg',
        ];

        $childCategories[] = [
            'name_arabic' => 'ساعات',
            'name_german' => 'Uhren',
            'parent_name_arabic' => 'إلكترونيات',
            'image' => 'categories/watches.jpg',
        ];
        $childCategories[] = [
            'name_arabic' => 'كاميرات',
            'name_german' => 'Kameras',
            'parent_name_arabic' => 'إلكترونيات',
            'image' => 'categories/cameras.jpg',
        ];

        $childCategories[] = [
            'name_arabic' => 'ملابس رياضية',
            'name_german' => 'Sportkleidung',
            'parent_name_arabic' => 'ملابس',
            'image' => 'categories/sportswear.jpg',
        ];

        $childCategories[] = [
            'name_arabic' => 'أجهزة لوحية',
            'name_german' => 'Tablets',
            'parent_name_arabic' => 'إلكترونيات',
            'image' => 'categories/tablets.jpg',
        ];

        $childCategories[] = [
            'name_arabic' => 'فساتين',
            'name_german' => 'Kleider',
            'parent_name_arabic' => 'ملابس',
            'image' => 'categories/dresses.jpg',
        ];

        foreach ($childCategories as $category) {
            $parent = Category::where('name_arabic', $category['parent_name_arabic'])->first();
            
            Category::create([
                'name_arabic' => $category['name_arabic'],
                'name_german' => $category['name_german'],
                'slug_arabic' => Str::slug($category['name_arabic']),
                'slug_german' => Str::slug($category['name_german']),
                'image' => $category['image'],
                'parent_id' => $parent->id,
            ]);
        }
    }
}

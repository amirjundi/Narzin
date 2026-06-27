<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Tests\TestCase;

class ProductSizeChartTest extends TestCase
{
    use RefreshDatabase;

    private array $chart = [
        'unit' => 'cm',
        'columns' => ['Shoulder', 'Chest'],
        'rows' => [
            ['size' => 'S', 'values' => ['Shoulder' => 42, 'Chest' => 96]],
            ['size' => 'M', 'values' => ['Shoulder' => 44, 'Chest' => 100]],
        ],
    ];

    /** Create a real category so the products.category_id foreign key is satisfied. */
    private function categoryId(): int
    {
        return Category::create([
            'name_arabic' => 'تصنيف',
            'name_german' => 'Kategorie',
            'slug_arabic' => 'cat-ar-' . uniqid(),
            'slug_german' => 'cat-de-' . uniqid(),
        ])->id;
    }

    public function test_product_persists_and_casts_size_chart(): void
    {
        $product = Product::create([
            'name_arabic' => 'قميص',
            'name_german' => 'Hemd',
            'slug_arabic' => 'qamis-' . uniqid(),
            'slug_german' => 'hemd-' . uniqid(),
            'category_id' => $this->categoryId(),
            'is_active' => true,
            'size_chart' => $this->chart,
        ]);

        $fresh = Product::find($product->id);
        $this->assertIsArray($fresh->size_chart);
        $this->assertSame('cm', $fresh->size_chart['unit']);
        $this->assertSame(42, $fresh->size_chart['rows'][0]['values']['Shoulder']);
    }

    public function test_size_chart_defaults_to_null(): void
    {
        $product = Product::create([
            'name_arabic' => 'حذاء',
            'name_german' => 'Schuh',
            'slug_arabic' => 'hidaa-' . uniqid(),
            'slug_german' => 'schuh-' . uniqid(),
            'category_id' => $this->categoryId(),
            'is_active' => true,
        ]);

        $this->assertNull(Product::find($product->id)->size_chart);
    }
}

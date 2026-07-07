<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Tests\TestCase;

/**
 * Covers Product::scopeSearch — keyword, case-insensitive matching across the
 * product name, description, and category name (Arabic + German).
 */
class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $de, string $ar = 'فئة'): Category
    {
        return Category::create([
            'name_arabic' => $ar, 'name_german' => $de,
            'slug_arabic' => 'c-ar-' . uniqid(), 'slug_german' => 'c-de-' . uniqid(),
        ]);
    }

    private function product(array $attrs): Product
    {
        return Product::create(array_merge([
            'name_arabic' => 'منتج', 'name_german' => 'Produkt',
            'slug_arabic' => 'p-ar-' . uniqid(), 'slug_german' => 'p-de-' . uniqid(),
            'is_active' => true,
        ], $attrs));
    }

    public function test_search_is_case_insensitive(): void
    {
        $cat = $this->category('Clothing');
        $shirt = $this->product(['name_german' => 'Blue Running Shirt', 'category_id' => $cat->id]);

        foreach (['shirt', 'SHIRT', 'Shirt'] as $term) {
            $ids = Product::search($term)->pluck('id')->all();
            $this->assertContains($shirt->id, $ids, "case '{$term}' should match");
        }
    }

    public function test_keywords_match_in_any_order(): void
    {
        $cat = $this->category('Clothing');
        $shirt = $this->product(['name_german' => 'Cotton Shirt Blue', 'category_id' => $cat->id]);
        $hat = $this->product(['name_german' => 'Red Hat', 'category_id' => $cat->id]);

        foreach (['blue shirt', 'shirt blue', 'cotton blue'] as $term) {
            $ids = Product::search($term)->pluck('id')->all();
            $this->assertContains($shirt->id, $ids, "'{$term}' should match the shirt");
            $this->assertNotContains($hat->id, $ids, "'{$term}' should not match the hat");
        }
    }

    public function test_matches_category_name(): void
    {
        $shoes = $this->category('Shoes');
        $sneaker = $this->product(['name_german' => 'Red Sneaker', 'category_id' => $shoes->id]);

        $ids = Product::search('shoes')->pluck('id')->all();
        $this->assertContains($sneaker->id, $ids);
    }

    public function test_matches_description(): void
    {
        $cat = $this->category('Clothing');
        $p = $this->product([
            'name_german' => 'Item',
            'description_german' => 'Made of waterproof leather',
            'category_id' => $cat->id,
        ]);

        $ids = Product::search('waterproof')->pluck('id')->all();
        $this->assertContains($p->id, $ids);
    }

    public function test_no_match_returns_nothing_and_empty_term_is_a_noop(): void
    {
        $cat = $this->category('Clothing');
        $this->product(['name_german' => 'Blue Shirt', 'category_id' => $cat->id]);

        $this->assertEmpty(Product::search('zzznope')->get());
        // An empty / null term must not filter anything out.
        $this->assertEquals(Product::count(), Product::search('')->count());
        $this->assertEquals(Product::count(), Product::search(null)->count());
    }
}

<?php

namespace Tests\Feature\Telemetry;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Tests\TestCase;

class SearchCaptureTest extends TestCase
{
    use RefreshDatabase;

    private function seedProduct(string $nameDe): void
    {
        $cat = Category::create([
            'name_arabic' => 'فئة', 'name_german' => 'Kategorie',
            'slug_arabic' => 'c-ar-' . uniqid(), 'slug_german' => 'c-de-' . uniqid(),
        ]);
        Product::create([
            'name_arabic' => 'منتج', 'name_german' => $nameDe,
            'slug_arabic' => 'p-ar-' . uniqid(), 'slug_german' => 'p-de-' . uniqid(),
            'category_id' => $cat->id, 'is_active' => true,
        ]);
    }

    public function test_search_request_logs_query_and_result_count(): void
    {
        $this->seedProduct('Blue Running Shirt');

        $this->getJson('/api/v1/products/search?search=shirt&session_id=sess-1')
            ->assertStatus(200);

        $this->assertDatabaseHas('search_logs', [
            'session_id' => 'sess-1',
            'normalized_query' => 'shirt',
            'results_count' => 1,
        ]);
    }

    public function test_zero_result_search_is_logged_with_zero_count(): void
    {
        $this->seedProduct('Blue Running Shirt');

        $this->getJson('/api/v1/products/search?search=nonexistentxyz')
            ->assertStatus(200);

        $this->assertDatabaseHas('search_logs', [
            'normalized_query' => 'nonexistentxyz',
            'results_count' => 0,
        ]);
    }
}

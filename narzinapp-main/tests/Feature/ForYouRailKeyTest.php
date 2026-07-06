<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\Telemetry\Models\UserProductView;
use Tests\TestCase;

class ForYouRailKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_recently_viewed_rail_has_stable_key(): void
    {
        // Minimal purchasable product (mirrors ProductRailResolverTest setup).
        $categoryId = Category::create([
            'name_arabic' => 'فئة', 'name_german' => 'Kategorie',
            'slug_arabic' => 'cat-ar-' . uniqid(), 'slug_german' => 'cat-de-' . uniqid(),
        ])->id;

        $product = Product::create([
            'name_arabic' => 'منتج', 'name_german' => 'Produkt',
            'slug_arabic' => 'p-ar-' . uniqid(), 'slug_german' => 'p-de-' . uniqid(),
            'category_id' => $categoryId, 'is_active' => true,
        ]);
        ProductVariant::create([
            'product_id' => $product->id, 'price' => 50, 'stock' => 10,
            'sku' => 'sku-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false,
        ]);

        $user = User::create([
            'name' => 'V', 'email' => 'v' . uniqid() . '@t.test',
            'password' => 'x', 'email_verified_at' => now(),
        ]);
        UserProductView::create([
            'user_id' => $user->id, 'product_id' => $product->id,
            'session_id' => 's-' . uniqid(), 'dwell_time_seconds' => 5,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/home/for-you?locale=du')
            ->assertOk();

        $keys = collect($response->json('data'))->pluck('content.key');
        $this->assertTrue($keys->contains('recently_viewed'));
    }
}

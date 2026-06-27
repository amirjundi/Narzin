<?php

namespace Tests\Feature\Checkout;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Services\NassPaymentService;
use Tests\TestCase;

/**
 * Covers the money path in CheckoutController::placeOrder() — the most
 * critical, previously-untested business logic. Focuses on the atomic stock
 * reservation guarantee and the happy path (payment gateway mocked).
 */
class PlaceOrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build the full fixture graph a place-order request needs and return the
     * IDs the endpoint references. $stock controls the variant's stock level.
     */
    private function seedCheckoutFixtures(User $user, int $stock, int $cartQuantity): array
    {
        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id,
            'address' => '123 Test Street',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $vendorId = DB::table('vendors')->insertGetId([
            'store_name_in_arabic' => 'متجر', 'store_name_in_german' => 'Laden',
            'user_id' => User::factory()->create()->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $categoryId = DB::table('categories')->insertGetId([
            'name_arabic' => 'فئة', 'name_german' => 'Kategorie',
            'slug_arabic' => 'cat-ar', 'slug_german' => 'cat-de',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $productId = DB::table('products')->insertGetId([
            'name_arabic' => 'منتج', 'name_german' => 'Produkt',
            'slug_arabic' => 'prod-ar', 'slug_german' => 'prod-de',
            'category_id' => $categoryId, 'vendor_id' => $vendorId,
            'is_active' => true, 'weight' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $variantId = DB::table('product_variants')->insertGetId([
            'product_id' => $productId, 'price' => 100, 'stock' => $stock,
            'sku' => 'SKU-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $zoneId = DB::table('delivery_zones')->insertGetId([
            'name_english' => 'Zone', 'name_german' => 'Zone', 'name_arabic' => 'Zone',
            'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $methodId = DB::table('delivery_methods')->insertGetId([
            'delivery_zone_id' => $zoneId,
            'name_english' => 'Standard', 'name_german' => 'Standard', 'name_arabic' => 'Standard',
            'base_price' => 5, 'price_per_kg' => 0, 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('cart')->insert([
            'user_id' => $user->id, 'product_id' => $productId,
            'product_variant_id' => $variantId, 'quantity' => $cartQuantity,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return [
            'address_id' => $addressId,
            'delivery_method_id' => $methodId,
            'variant_id' => $variantId,
        ];
    }

    public function test_place_order_requires_authentication(): void
    {
        $this->postJson('/api/v1/place-order')->assertUnauthorized();
    }

    public function test_insufficient_stock_is_rejected_and_stock_is_not_deducted(): void
    {
        $user = User::factory()->create();
        $ids = $this->seedCheckoutFixtures($user, stock: 1, cartQuantity: 5);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/place-order', [
            'address_id' => $ids['address_id'],
            'delivery_method_id' => $ids['delivery_method_id'],
        ]);

        $response->assertStatus(400);
        $this->assertStringContainsStringIgnoringCase('stock', $response->json('message'));

        // The transaction must have rolled back: stock untouched, no order created.
        $this->assertEquals(1, DB::table('product_variants')->where('id', $ids['variant_id'])->value('stock'));
        $this->assertEquals(0, DB::table('orders')->where('user_id', $user->id)->count());
    }

    public function test_successful_order_deducts_stock_and_clears_cart(): void
    {
        // Mock the external Nass gateway so the test never makes a network call.
        $this->mock(NassPaymentService::class, function ($mock) {
            $mock->shouldReceive('createTransaction')->once()->andReturn([
                'success' => true,
                'data' => [
                    'url' => 'https://pay.example/redirect',
                    'transactionParams' => [],
                ],
            ]);
        });

        $user = User::factory()->create();
        $ids = $this->seedCheckoutFixtures($user, stock: 10, cartQuantity: 2);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/place-order', [
            'address_id' => $ids['address_id'],
            'delivery_method_id' => $ids['delivery_method_id'],
        ]);

        $response->assertStatus(200)->assertJson(['status' => true]);

        // Stock reserved (10 - 2), cart cleared, one order persisted.
        $this->assertEquals(8, DB::table('product_variants')->where('id', $ids['variant_id'])->value('stock'));
        $this->assertEquals(0, DB::table('cart')->where('user_id', $user->id)->count());
        $this->assertEquals(1, DB::table('orders')->where('user_id', $user->id)->count());
    }
}

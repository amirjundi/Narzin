<?php

namespace Tests\Feature\Checkout;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Coupon;
use Modules\Checkout\Models\Promotion;
use Modules\Checkout\Services\NassPaymentService;
use Tests\TestCase;

/**
 * Integration tests for promotion-related paths in CheckoutController::placeOrder().
 * Covers the coupon-burn prevention (promotion beats coupon) and the free-shipping
 * promo that zeros out shipping cost.
 */
class PromotionPlaceOrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Mirrors the fixture helper from PlaceOrderTest exactly.
     * Returns IDs the endpoint references.
     */
    private function seedCheckoutFixtures(User $user, int $stock = 10, int $cartQuantity = 1): array
    {
        $addressId = DB::table('user_address')->insertGetId([
            'user_id'    => $user->id,
            'address'    => '123 Test Street',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $vendorId = DB::table('vendors')->insertGetId([
            'store_name_in_arabic' => 'متجر', 'store_name_in_german' => 'Laden',
            'user_id'              => User::factory()->create()->id,
            'created_at'           => now(), 'updated_at' => now(),
        ]);

        $categoryId = DB::table('categories')->insertGetId([
            'name_arabic'  => 'فئة',   'name_german'  => 'Kategorie',
            'slug_arabic'  => 'cat-ar', 'slug_german'  => 'cat-de',
            'created_at'   => now(),    'updated_at'   => now(),
        ]);

        $productId = DB::table('products')->insertGetId([
            'name_arabic'  => 'منتج',    'name_german'  => 'Produkt',
            'slug_arabic'  => 'prod-ar', 'slug_german'  => 'prod-de',
            'category_id'  => $categoryId, 'vendor_id'  => $vendorId,
            'is_active'    => true, 'weight' => 1,
            'created_at'   => now(), 'updated_at' => now(),
        ]);

        $variantId = DB::table('product_variants')->insertGetId([
            'product_id'      => $productId, 'price' => 100, 'stock' => $stock,
            'sku'             => 'SKU-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false,
            'created_at'      => now(), 'updated_at' => now(),
        ]);

        $zoneId = DB::table('delivery_zones')->insertGetId([
            'name_english' => 'Zone', 'name_german' => 'Zone', 'name_arabic' => 'Zone',
            'is_active'    => true,
            'created_at'   => now(), 'updated_at' => now(),
        ]);

        $methodId = DB::table('delivery_methods')->insertGetId([
            'delivery_zone_id' => $zoneId,
            'name_english'     => 'Standard', 'name_german' => 'Standard', 'name_arabic' => 'Standard',
            'base_price'       => 5, 'price_per_kg' => 0, 'is_active' => true,
            'created_at'       => now(), 'updated_at' => now(),
        ]);

        DB::table('cart')->insert([
            'user_id'            => $user->id, 'product_id'         => $productId,
            'product_variant_id' => $variantId, 'quantity'          => $cartQuantity,
            'created_at'         => now(), 'updated_at'            => now(),
        ]);

        return [
            'address_id'         => $addressId,
            'delivery_method_id' => $methodId,
            'variant_id'         => $variantId,
        ];
    }

    private function mockNass(): void
    {
        $this->mock(NassPaymentService::class, function ($mock) {
            $mock->shouldReceive('createTransaction')->once()->andReturn([
                'success' => true,
                'data'    => [
                    'url'               => 'https://pay.example/redirect',
                    'transactionParams' => [],
                ],
            ]);
        });
    }

    /**
     * When a promotion's discount is larger than the coupon's, the promotion
     * wins the "best-one" contest and the coupon must NOT be consumed
     * (order.coupon_id should be null). The order_item.vendor_discount_absorbed
     * should be driven by the promo's absorbed_by_vendor_percentage (60 %), not
     * the vendor's default (0 %).
     */
    public function test_promo_beats_coupon_coupon_not_consumed(): void
    {
        $this->mockNass();

        $user = User::factory()->create();
        $ids  = $this->seedCheckoutFixtures($user, stock: 10, cartQuantity: 1);

        // Coupon: fixed 5 off — will lose to the promotion.
        $coupon = Coupon::create([
            'code'            => 'SAVE5',
            'discount_amount' => 5,
            'discount_type'   => 'fixed',
            'is_active'       => true,
            'used'            => 0,
        ]);

        // Promotion: fixed 20 off, absorbed_by_vendor_percentage = 60 %.
        // Vendor default absorption is 0 % (PayoutSetting seeded on the fly with 0),
        // so 60 % is a meaningfully distinct value.
        $promo = Promotion::create([
            'name'                         => '20 Off',
            'type'                         => 'fixed',
            'value'                        => 20,
            'minimum_cart_amount'          => 0,
            'absorbed_by_vendor_percentage' => 60,
            'is_active'                    => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/place-order', [
            'address_id'         => $ids['address_id'],
            'delivery_method_id' => $ids['delivery_method_id'],
            'coupon'             => 'SAVE5',
        ]);

        $response->assertStatus(200)->assertJson(['status' => true]);

        $order = DB::table('orders')->where('user_id', $user->id)->first();
        $this->assertNotNull($order, 'Order must be created.');

        // Coupon NOT consumed — promotion won the best-one-wins contest.
        $this->assertNull($order->coupon_id, 'coupon_id must be null when promotion wins.');
        $this->assertEquals($promo->id, $order->promotion_id, 'promotion_id must be set to the winning promo.');

        // vendor_discount_absorbed must reflect the PROMO's 60 % absorption, not the vendor's 0 %.
        // cart: 1 item, price 100, no markup → totalAmount = 100, discountAmount = 20
        // allocatedDiscount = 20 * (100/100) = 20 → absorbed = round(20 * 60/100, 2) = 12.0
        $item = DB::table('order_items')->where('order_id', $order->id)->first();
        $this->assertNotNull($item, 'Order item must be created.');
        $this->assertEquals(12.0, (float) $item->vendor_discount_absorbed,
            'vendor_discount_absorbed must use the promo absorption rate (60 %), not the vendor default (0 %).');
    }

    /**
     * When a free_shipping promotion qualifies (cart subtotal >= minimum_cart_amount),
     * the order's shipping_cost must be zeroed out.
     */
    public function test_free_shipping_promo_zeros_shipping_cost(): void
    {
        $this->mockNass();

        $user = User::factory()->create();
        $ids  = $this->seedCheckoutFixtures($user, stock: 10, cartQuantity: 1);

        // Free-shipping promo: minimum cart 50, cart total will be 100 → qualifies.
        Promotion::create([
            'name'                => 'Free Ship',
            'type'                => 'free_shipping',
            'minimum_cart_amount' => 50,
            'is_active'           => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/place-order', [
            'address_id'         => $ids['address_id'],
            'delivery_method_id' => $ids['delivery_method_id'],
        ]);

        $response->assertStatus(200)->assertJson(['status' => true]);

        $order = DB::table('orders')->where('user_id', $user->id)->first();
        $this->assertNotNull($order, 'Order must be created.');

        // Delivery method base_price = 5; free_shipping promo must zero it out.
        $this->assertEquals(0, (float) $order->shipping_cost,
            'shipping_cost must be 0 when a free_shipping promotion qualifies.');
    }
}

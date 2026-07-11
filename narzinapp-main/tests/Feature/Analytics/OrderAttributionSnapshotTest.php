<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Services\NassPaymentService;
use Modules\Telemetry\Models\VisitSession;
use Tests\TestCase;

class OrderAttributionSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_placed_order_snapshots_session_utm(): void
    {
        $user = User::factory()->create();

        $addressId = DB::table('user_address')->insertGetId(['user_id' => $user->id, 'address' => '123 St', 'created_at' => now(), 'updated_at' => now()]);
        $vendorId = DB::table('vendors')->insertGetId(['store_name_in_arabic' => 'م', 'store_name_in_german' => 'L', 'user_id' => User::factory()->create()->id, 'created_at' => now(), 'updated_at' => now()]);
        $categoryId = DB::table('categories')->insertGetId(['name_arabic' => 'ف', 'name_german' => 'K', 'slug_arabic' => 'cat-ar', 'slug_german' => 'cat-de', 'created_at' => now(), 'updated_at' => now()]);
        $productId = DB::table('products')->insertGetId(['name_arabic' => 'م', 'name_german' => 'P', 'slug_arabic' => 'p-ar', 'slug_german' => 'p-de', 'category_id' => $categoryId, 'vendor_id' => $vendorId, 'is_active' => true, 'weight' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $variantId = DB::table('product_variants')->insertGetId(['product_id' => $productId, 'price' => 100, 'stock' => 10, 'sku' => 'SKU-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false, 'created_at' => now(), 'updated_at' => now()]);
        $zoneId = DB::table('delivery_zones')->insertGetId(['name_english' => 'Z', 'name_german' => 'Z', 'name_arabic' => 'Z', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $methodId = DB::table('delivery_methods')->insertGetId(['delivery_zone_id' => $zoneId, 'name_english' => 'S', 'name_german' => 'S', 'name_arabic' => 'S', 'base_price' => 5, 'price_per_kg' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cart')->insert(['user_id' => $user->id, 'product_id' => $productId, 'product_variant_id' => $variantId, 'quantity' => 1, 'created_at' => now(), 'updated_at' => now()]);

        VisitSession::create([
            'session_id' => 'sess-attr', 'utm_source' => 'google', 'utm_medium' => 'cpc',
            'utm_campaign' => 'july', 'first_seen_at' => now(), 'last_seen_at' => now(),
        ]);

        $this->mock(NassPaymentService::class, function ($mock) {
            $mock->shouldReceive('createTransaction')->once()->andReturn([
                'success' => true, 'data' => ['url' => 'https://pay.example/x', 'transactionParams' => []],
            ]);
        });

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/place-order', [
            'session_id' => 'sess-attr', 'address_id' => $addressId, 'delivery_method_id' => $methodId,
        ])->assertStatus(200);

        $order = DB::table('orders')->where('user_id', $user->id)->first();
        $this->assertSame('google', $order->utm_source);
        $this->assertSame('july', $order->utm_campaign);
        $this->assertSame('sess-attr', $order->attributed_session_id);
    }
}

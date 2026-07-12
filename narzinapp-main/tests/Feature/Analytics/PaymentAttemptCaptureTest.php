<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Services\NassPaymentService;
use Modules\Checkout\Services\PaymentAttemptRecorder;
use Tests\TestCase;

class PaymentAttemptCaptureTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_writes_an_attempt(): void
    {
        PaymentAttemptRecorder::record(null, 5, 'nass', 'success', '00', 40.00);

        $this->assertDatabaseHas('payment_attempts', [
            'user_id' => 5, 'gateway' => 'nass', 'status' => 'success', 'response_code' => '00',
        ]);
    }

    public function test_record_never_throws_on_bad_input(): void
    {
        // order_id has an enforced FK to orders; a non-existent id throws at the
        // DB — the recorder must swallow it and not propagate (reliable across
        // SQLite/MySQL, unlike relying on enum CHECK enforcement).
        PaymentAttemptRecorder::record(999999, null, 'nass', 'success', '00', 10.00);

        $this->assertDatabaseCount('payment_attempts', 0); // swallowed, nothing written
    }

    public function test_place_order_records_initiated_payment_attempt(): void
    {
        $user = User::factory()->create();

        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id, 'address' => '123 Test Street',
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
            'product_id' => $productId, 'price' => 100, 'stock' => 10,
            'sku' => 'SKU-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $zoneId = DB::table('delivery_zones')->insertGetId([
            'name_english' => 'Zone', 'name_german' => 'Zone', 'name_arabic' => 'Zone',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $methodId = DB::table('delivery_methods')->insertGetId([
            'delivery_zone_id' => $zoneId,
            'name_english' => 'Standard', 'name_german' => 'Standard', 'name_arabic' => 'Standard',
            'base_price' => 5, 'price_per_kg' => 0, 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('cart')->insert([
            'user_id' => $user->id, 'product_id' => $productId, 'product_variant_id' => $variantId,
            'quantity' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        // Mock the external Nass gateway so the test never makes a network call
        // (matches tests/Feature/Telemetry/CheckoutCaptureTest.php).
        $this->mock(NassPaymentService::class, function ($mock) {
            $mock->shouldReceive('createTransaction')->once()->andReturn([
                'success' => true,
                'data' => ['url' => 'https://pay.example/redirect', 'transactionParams' => []],
            ]);
        });

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/place-order', [
            'session_id' => 'sess-pa',
            'address_id' => $addressId,
            'delivery_method_id' => $methodId,
        ])->assertStatus(200);

        $this->assertDatabaseHas('payment_attempts', ['status' => 'initiated', 'gateway' => 'nass']);
    }
}

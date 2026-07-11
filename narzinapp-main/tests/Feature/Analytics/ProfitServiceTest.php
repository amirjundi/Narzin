<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\ProfitService;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\Vendor\Models\VendorTransaction;
use Tests\TestCase;

class ProfitServiceTest extends TestCase
{
    use RefreshDatabase;

    private function range(): DateRange
    {
        return new DateRange(now()->subDays(30)->startOfDay(), now()->endOfDay());
    }

    // orders.address_id is a NOT NULL FK — seed a user_address (mirrors OrderAttributionColumnsTest).
    private function order(array $attrs = []): Order
    {
        $user = User::factory()->create();
        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id, 'address' => '1 St', 'created_at' => now(), 'updated_at' => now(),
        ]);
        return Order::create(array_merge([
            'user_id' => $user->id,
            'address_id' => $addressId,
            'order_number' => 'T-' . uniqid(),
            'order_status' => 'pending',
            'payment_status' => 'completed',
            'total_amount' => 100.00,
            'price_after_discount' => 100.00,
        ], $attrs));
    }

    private array $catalog = [];

    // order_items.{product_id,product_variant_id,vendor_id} are enforced,
    // non-nullable FKs; order_items.final_price is NOT NULL. Seed one
    // vendor+category+product+variant once and reuse (mirrors PlaceOrderTest).
    private function catalog(): array
    {
        if ($this->catalog) return $this->catalog;
        $vendorId = DB::table('vendors')->insertGetId([
            'store_name_in_arabic' => 'متجر', 'store_name_in_german' => 'Laden',
            'user_id' => User::factory()->create()->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'name_arabic' => 'فئة', 'name_german' => 'Kat',
            'slug_arabic' => 'c-' . uniqid(), 'slug_german' => 'c-' . uniqid(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $productId = DB::table('products')->insertGetId([
            'name_arabic' => 'م', 'name_german' => 'P',
            'slug_arabic' => 'p-' . uniqid(), 'slug_german' => 'p-' . uniqid(),
            'category_id' => $categoryId, 'vendor_id' => $vendorId,
            'is_active' => true, 'weight' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $variantId = DB::table('product_variants')->insertGetId([
            'product_id' => $productId, 'price' => 100, 'stock' => 10,
            'sku' => 'SKU-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return $this->catalog = ['vendor_id' => $vendorId, 'product_id' => $productId, 'variant_id' => $variantId];
    }

    private function vendor(): int
    {
        return DB::table('vendors')->insertGetId([
            'store_name_in_arabic' => 'م', 'store_name_in_german' => 'L',
            'user_id' => User::factory()->create()->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function item(Order $order, float $vendorEarning, float $commission = 0): void
    {
        $c = $this->catalog();
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $c['product_id'],
            'product_variant_id' => $c['variant_id'], 'quantity' => 1,
            'vendor_id' => $c['vendor_id'], 'unit_price' => 100, 'subtotal' => 100,
            'final_price' => 100,
            'vendor_earning' => $vendorEarning, 'vendor_commission_amount' => $commission,
        ]);
    }

    public function test_placed_profit_is_revenue_minus_vendor_earnings(): void
    {
        // paid order: revenue 100, vendor earning 70 → profit 30
        $o1 = $this->order(['price_after_discount' => 100, 'payment_status' => 'completed']);
        $this->item($o1, vendorEarning: 70, commission: 10);
        // unpaid order: revenue 200, vendor earning 150 → in PLACED only
        $o2 = $this->order(['price_after_discount' => 200, 'payment_status' => 'not_paid']);
        $this->item($o2, vendorEarning: 150, commission: 20);

        $s = (new ProfitService())->summary($this->range());

        // placed = both orders: revenue 300, earnings 220, profit 80
        $this->assertEquals(300.00, $s['placed']['revenue']);
        $this->assertEquals(220.00, $s['placed']['vendor_earnings']);
        $this->assertEquals(80.00, $s['placed']['platform_profit']);
        $this->assertSame(2, $s['placed']['orders']);

        // paid = only o1: revenue 100, earnings 70, profit 30
        $this->assertEquals(100.00, $s['paid']['revenue']);
        $this->assertEquals(70.00, $s['paid']['vendor_earnings']);
        $this->assertEquals(30.00, $s['paid']['platform_profit']);
        $this->assertSame(1, $s['paid']['orders']);
        $this->assertEquals(0.3, $s['paid']['margin']); // 30/100
        $this->assertEquals(10.00, $s['commission_collected']); // only paid o1's commission; o2 (not_paid) excluded
    }

    public function test_null_vendor_earning_coalesces_to_zero(): void
    {
        $o = $this->order(['price_after_discount' => 100, 'payment_status' => 'completed']);
        $this->item($o, vendorEarning: 0); // simulate pre-feature: set null explicitly
        OrderItem::where('order_id', $o->id)->update(['vendor_earning' => null]);

        $s = (new ProfitService())->summary($this->range());
        $this->assertEquals(100.00, $s['paid']['revenue']);
        $this->assertEquals(0.00, $s['paid']['vendor_earnings']); // null → 0
        $this->assertEquals(100.00, $s['paid']['platform_profit']);
    }

    public function test_total_owed_sums_vendor_transactions(): void
    {
        // vendor_transactions.vendor_id is an enforced FK — seed real vendors.
        $v1 = $this->vendor();
        $v2 = $this->vendor();
        VendorTransaction::create(['vendor_id' => $v1, 'type' => 'earning', 'amount' => 70]);
        VendorTransaction::create(['vendor_id' => $v1, 'type' => 'payout', 'amount' => -20]);
        VendorTransaction::create(['vendor_id' => $v2, 'type' => 'earning', 'amount' => 50]);

        $s = (new ProfitService())->summary($this->range());
        $this->assertEquals(100.00, $s['total_owed_to_vendors']); // 70 - 20 + 50
    }

    public function test_no_orders_no_divide_by_zero(): void
    {
        $s = (new ProfitService())->summary($this->range());
        $this->assertEquals(0.0, $s['paid']['margin']);
        $this->assertEquals(0.0, $s['paid']['platform_profit']);
        $this->assertSame(0, $s['paid']['orders']);
    }
}

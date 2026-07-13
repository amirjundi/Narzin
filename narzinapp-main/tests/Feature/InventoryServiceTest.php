<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\InventoryService;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\Vendor\Models\Vendor;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private static int $seq = 0;

    /**
     * Create a product (+vendor +category) and a variant with given stock/cost/price/expiry.
     * Returns the variant id.
     */
    private function makeVariant(array $v): int
    {
        self::$seq++;

        $vendorUser = User::factory()->create();

        $vendor = Vendor::create([
            'store_name_in_arabic' => 'متجر ' . self::$seq,
            'store_name_in_german' => 'Geschaeft ' . self::$seq,
            'user_id' => $vendorUser->id,
            'status' => 'Active',
        ]);

        $categoryId = DB::table('categories')->insertGetId([
            'name_arabic' => 'فئة ' . self::$seq,
            'name_german' => 'Kategorie ' . self::$seq,
            'slug_arabic' => 'cat-ar-' . self::$seq . '-' . uniqid(),
            'slug_german' => 'cat-de-' . self::$seq . '-' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'name_arabic' => 'منتج ' . self::$seq,
            'name_german' => 'Produkt ' . self::$seq,
            'slug_arabic' => 'prod-ar-' . self::$seq . '-' . uniqid(),
            'slug_german' => 'prod-de-' . self::$seq . '-' . uniqid(),
            'category_id' => $categoryId,
            'vendor_id' => $vendor->id,
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'price' => $v['price'],
            'cost' => $v['cost'],
            'stock' => $v['stock'],
            'expiry_date' => $v['expiry_date'] ?? null,
            'sku' => $v['sku'] ?? ('SKU-' . self::$seq . '-' . uniqid()),
            'is_active' => $v['is_active'] ?? true,
            'is_out_of_stock' => ($v['stock'] ?? 0) <= 0,
        ]);

        return $variant->id;
    }

    /**
     * Seed an Order (created_at = $at) + OrderItem(product_variant_id = $variantId).
     */
    private function sellVariantInWindow(int $variantId, Carbon $at): void
    {
        $user = User::factory()->create();

        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id,
            'address' => '123 Test Street',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $addressId,
            'total_amount' => 50.00,
            'order_number' => 'T-' . uniqid(),
            'order_status' => 'confirmed',
        ]);

        $order->created_at = $at;
        $order->save();

        $variant = ProductVariant::findOrFail($variantId);
        $product = Product::findOrFail($variant->product_id);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variantId,
            'vendor_id' => $product->vendor_id,
            'quantity' => 1,
            'unit_price' => 1,
            'subtotal' => 1,
            'final_price' => 1,
        ]);
    }

    public function test_valuation_totals_and_margin(): void
    {
        // Variant A: stock 10, cost 2.00, price 5.00 (active)
        // Variant B: stock 4, cost 3.00, price 7.00 (active)
        // Variant C: stock 100, cost 1.00, price 2.00 (INACTIVE — must be excluded)
        $this->makeVariant(['stock' => 10, 'cost' => 2, 'price' => 5, 'is_active' => 1]);
        $this->makeVariant(['stock' => 4, 'cost' => 3, 'price' => 7, 'is_active' => 1]);
        $this->makeVariant(['stock' => 100, 'cost' => 1, 'price' => 2, 'is_active' => 0]);

        $val = (new InventoryService())->valuation();

        $this->assertSame(14, $val['total_units']);                 // 10+4, inactive excluded
        $this->assertEqualsWithDelta(32.0, $val['value_at_cost'], 0.01);   // 10*2 + 4*3
        $this->assertEqualsWithDelta(78.0, $val['value_at_retail'], 0.01); // 10*5 + 4*7
        $this->assertEqualsWithDelta(46.0, $val['potential_margin'], 0.01);
    }

    public function test_reorder_worklist_respects_threshold_and_flags_out(): void
    {
        config(['telemetry.low_stock_threshold' => 5]);
        $this->makeVariant(['stock' => 0, 'cost' => 1, 'price' => 2, 'is_active' => 1, 'sku' => 'OUT-1']);
        $this->makeVariant(['stock' => 3, 'cost' => 1, 'price' => 2, 'is_active' => 1, 'sku' => 'LOW-1']);
        $this->makeVariant(['stock' => 50, 'cost' => 1, 'price' => 2, 'is_active' => 1, 'sku' => 'OK-1']);

        $list = (new InventoryService())->reorderWorklist();
        $skus = $list->pluck('sku')->all();

        $this->assertContains('OUT-1', $skus);
        $this->assertContains('LOW-1', $skus);
        $this->assertNotContains('OK-1', $skus);
        $this->assertSame('OUT-1', $list->first()['sku']); // stock asc → 0 first
        $this->assertTrue((bool) $list->firstWhere('sku', 'OUT-1')['is_out']);
        $this->assertFalse((bool) $list->firstWhere('sku', 'LOW-1')['is_out']);
    }

    public function test_dead_stock_excludes_variants_sold_in_window(): void
    {
        $range = new DateRange(Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31'));

        $soldId = $this->makeVariant(['stock' => 10, 'cost' => 2, 'price' => 5, 'is_active' => 1, 'sku' => 'SOLD']);
        $deadId = $this->makeVariant(['stock' => 20, 'cost' => 2, 'price' => 5, 'is_active' => 1, 'sku' => 'DEAD']);
        // Create an order in-window with an order_item for $soldId (mirror Phase 8 order seeding).
        $this->sellVariantInWindow($soldId, Carbon::parse('2026-07-10'));

        $dead = (new InventoryService())->deadStock($range);
        $skus = $dead->pluck('sku')->all();

        $this->assertContains('DEAD', $skus);
        $this->assertNotContains('SOLD', $skus);
        $this->assertEqualsWithDelta(40.0, $dead->firstWhere('sku', 'DEAD')['value_at_cost'], 0.01); // 20*2
    }

    public function test_reorder_worklist_null_vendor_shows_none(): void
    {
        config(['telemetry.low_stock_threshold' => 5]);

        // A product with NO vendor (vendor_id is nullable) — vendor_name must
        // fall back to the '(none)' sentinel, not null.
        $categoryId = DB::table('categories')->insertGetId([
            'name_arabic' => 'nv-ar', 'name_german' => 'nv-de',
            'slug_arabic' => 'nv-ar-' . uniqid(), 'slug_german' => 'nv-de-' . uniqid(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $product = Product::create([
            'name_arabic' => 'no-vendor-ar', 'name_german' => 'no-vendor-de',
            'slug_arabic' => 'nv-p-ar-' . uniqid(), 'slug_german' => 'nv-p-de-' . uniqid(),
            'category_id' => $categoryId, 'vendor_id' => null, 'is_active' => true,
        ]);
        ProductVariant::create([
            'product_id' => $product->id, 'price' => 2, 'cost' => 1, 'stock' => 1,
            'sku' => 'NOVENDOR-1', 'is_active' => true, 'is_out_of_stock' => false,
        ]);

        $row = (new InventoryService())->reorderWorklist()->firstWhere('sku', 'NOVENDOR-1');

        $this->assertNotNull($row);
        $this->assertSame('(none)', $row['vendor_name']);
    }

    public function test_expiring_stock_window_and_null_expiry(): void
    {
        config(['telemetry.expiry_days_ahead' => 30]);
        $this->makeVariant(['stock' => 5, 'cost' => 2, 'price' => 5, 'is_active' => 1, 'sku' => 'SOON', 'expiry_date' => Carbon::now()->addDays(10)->toDateString()]);
        $this->makeVariant(['stock' => 5, 'cost' => 2, 'price' => 5, 'is_active' => 1, 'sku' => 'LATER', 'expiry_date' => Carbon::now()->addDays(90)->toDateString()]);
        $this->makeVariant(['stock' => 5, 'cost' => 2, 'price' => 5, 'is_active' => 1, 'sku' => 'NOEXP', 'expiry_date' => null]);

        $exp = (new InventoryService())->expiringStock();
        $skus = $exp->pluck('sku')->all();

        $this->assertContains('SOON', $skus);
        $this->assertNotContains('LATER', $skus);
        $this->assertNotContains('NOEXP', $skus);
    }
}

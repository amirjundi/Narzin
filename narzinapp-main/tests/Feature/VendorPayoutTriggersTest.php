<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\Vendor\Models\Vendor;
use Modules\Vendor\Services\VendorLedgerService;
use Tests\TestCase;

class VendorPayoutTriggersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a minimal order item for the given vendor, earning amount, collection status, and order status.
     */
    private function makeItem(Vendor $vendor, float $earning, string $collection, string $orderStatus): OrderItem
    {
        $cust = User::create(['name' => 'C', 'email' => 'c' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $cust->id, 'address' => '1 Test St', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'name_arabic' => 'تصنيف', 'name_german' => 'Kat',
            'slug_arabic' => 'cat-ar-' . uniqid(), 'slug_german' => 'cat-de-' . uniqid(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $productId = DB::table('products')->insertGetId([
            'name_arabic' => 'منتج', 'name_german' => 'Prod',
            'slug_arabic' => 'prod-ar-' . uniqid(), 'slug_german' => 'prod-de-' . uniqid(),
            'category_id' => $categoryId, 'vendor_id' => $vendor->id,
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $variantId = DB::table('product_variants')->insertGetId([
            'product_id' => $productId, 'price' => 100, 'stock' => 10,
            'sku' => 'SKU-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $order = Order::create([
            'user_id' => $cust->id, 'address_id' => $addressId,
            'order_number' => 'O' . uniqid(), 'payment_id' => 'P' . uniqid(),
            'total_amount' => 100, 'final_price' => 100,
            'payment_status' => 'processing', 'order_status' => $orderStatus,
        ]);
        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'vendor_id' => $vendor->id,
            'quantity' => 1, 'unit_price' => 100, 'subtotal' => 100, 'final_price' => 100,
            'vendor_earning' => $earning,
            'collection_status' => $collection,
        ]);
    }

    public function test_pending_earnings_excludes_unavailable_and_cancelled(): void
    {
        $u = User::create(['name' => 'V', 'email' => 'v' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        $vendor = Vendor::create(['user_id' => $u->id, 'store_name_in_arabic' => 'م', 'store_name_in_german' => 'L', 'status' => 'Active']);

        $this->makeItem($vendor, 30.0, 'pending', 'confirmed');     // should count
        $this->makeItem($vendor, 50.0, 'unavailable', 'confirmed'); // excluded — unavailable
        $this->makeItem($vendor, 70.0, 'pending', 'cancelled');     // excluded — cancelled order

        $svc = new VendorLedgerService();
        $this->assertSame(30.0, $svc->pendingEarnings($vendor->id));
    }

    public function test_collected_item_credits_then_cancel_reverses(): void
    {
        // Vendor user + vendor
        $u = User::create(['name' => 'V', 'email' => 'v' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        $vendor = Vendor::create(['user_id' => $u->id, 'store_name_in_arabic' => 'م', 'store_name_in_german' => 'L', 'status' => 'Active']);

        // Customer + address
        $cust = User::create(['name' => 'C', 'email' => 'c' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $cust->id, 'address' => '1 Test St', 'created_at' => now(), 'updated_at' => now(),
        ]);

        // Product hierarchy needed for order_items FK
        $categoryId = DB::table('categories')->insertGetId([
            'name_arabic' => 'تصنيف', 'name_german' => 'Kat',
            'slug_arabic' => 'cat-ar-' . uniqid(), 'slug_german' => 'cat-de-' . uniqid(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $productId = DB::table('products')->insertGetId([
            'name_arabic' => 'منتج', 'name_german' => 'Prod',
            'slug_arabic' => 'prod-ar-' . uniqid(), 'slug_german' => 'prod-de-' . uniqid(),
            'category_id' => $categoryId, 'vendor_id' => $vendor->id,
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $variantId = DB::table('product_variants')->insertGetId([
            'product_id' => $productId, 'price' => 100, 'stock' => 10,
            'sku' => 'SKU-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Order
        $order = Order::create([
            'user_id' => $cust->id, 'address_id' => $addressId,
            'order_number' => 'O' . uniqid(), 'payment_id' => 'P' . uniqid(),
            'total_amount' => 100, 'final_price' => 100,
            'payment_status' => 'processing', 'order_status' => 'confirmed',
        ]);

        // Order item with snapshot
        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'vendor_id' => $vendor->id,
            'quantity' => 1,
            'unit_price' => 100,
            'subtotal' => 100,
            'final_price' => 100,
            'vendor_earning' => 90,
            'collection_status' => 'pending',
        ]);

        $svc = new VendorLedgerService();
        $this->assertSame(0.0, $svc->payableBalance($vendor->id));

        // simulate collection transition handler
        $item->update(['collection_status' => 'collected']);
        $svc->creditEarning($item);
        $this->assertSame(90.0, $svc->payableBalance($vendor->id));

        // simulate cancel/return reversal
        $svc->reverseEarning($item);
        $this->assertSame(0.0, $svc->payableBalance($vendor->id));
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\UserAddress\Models\UserAddress;
use Modules\Vendor\Models\Vendor;
use Modules\Vendor\Services\VendorLedgerService;
use Tests\TestCase;

class VendorLedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    private function vendor(): Vendor
    {
        $u = User::create(['name' => 'V', 'email' => 'v' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        return Vendor::create([
            'user_id' => $u->id, 'store_name_in_arabic' => 'م', 'store_name_in_german' => 'L', 'status' => 'Active',
        ]);
    }

    private function orderItem(Vendor $vendor, float $earning, string $collection = 'collected'): OrderItem
    {
        $userId = User::create(['name' => 'C', 'email' => 'c' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()])->id;
        $address = UserAddress::create([
            'user_id' => $userId, 'address' => '123 Main St',
        ]);
        $order = Order::create([
            'user_id' => $userId, 'address_id' => $address->id, 'order_number' => 'O' . uniqid(), 'payment_id' => 'P' . uniqid(),
            'total_amount' => 100, 'final_price' => 100, 'payment_status' => 'processing', 'order_status' => 'confirmed',
        ]);
        $category = Category::create([
            'name_arabic' => 'فئة', 'name_german' => 'Kategorie', 'slug_arabic' => 'category', 'slug_german' => 'kategorie',
        ]);
        $product = Product::create([
            'name_arabic' => 'منتج', 'name_german' => 'Produkt', 'slug_arabic' => 'product', 'slug_german' => 'produkt',
            'category_id' => $category->id, 'vendor_id' => $vendor->id, 'is_active' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id, 'price' => 100, 'stock' => 10, 'sku' => 'SKU' . uniqid(), 'is_active' => true,
        ]);
        return OrderItem::create([
            'order_id' => $order->id, 'product_id' => $product->id, 'product_variant_id' => $variant->id, 'vendor_id' => $vendor->id,
            'quantity' => 1, 'unit_price' => 100, 'subtotal' => 100, 'final_price' => 100, 'vendor_earning' => $earning,
            'collection_status' => $collection,
        ]);
    }

    public function test_credit_earning_is_idempotent(): void
    {
        $svc = new VendorLedgerService();
        $vendor = $this->vendor();
        $item = $this->orderItem($vendor, 90.0);

        $svc->creditEarning($item);
        $svc->creditEarning($item); // again — must not double-credit

        $this->assertSame(90.0, $svc->payableBalance($vendor->id));
        $this->assertSame(1, DB::table('vendor_transactions')->where('order_item_id', $item->id)->where('type', 'earning')->count());
    }

    public function test_reverse_earning_flips_balance(): void
    {
        $svc = new VendorLedgerService();
        $vendor = $this->vendor();
        $item = $this->orderItem($vendor, 90.0);

        $svc->creditEarning($item);
        $svc->reverseEarning($item);

        $this->assertSame(0.0, $svc->payableBalance($vendor->id));
    }

    public function test_record_payout_reduces_balance_and_caps_at_payable(): void
    {
        $svc = new VendorLedgerService();
        $vendor = $this->vendor();
        $svc->creditEarning($this->orderItem($vendor, 90.0));

        $payout = $svc->recordPayout($vendor->id, 50.0, 'bank', 'REF1', null, null);
        $this->assertSame(50.0, (float) $payout->amount);
        $this->assertSame(40.0, $svc->payableBalance($vendor->id));

        $this->expectException(\InvalidArgumentException::class);
        $svc->recordPayout($vendor->id, 999.0, 'bank', 'REF2', null, null); // over balance
    }
}

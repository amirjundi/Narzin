<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\UserAddress\Models\UserAddress;
use Modules\Vendor\Models\Vendor;
use Modules\Vendor\Services\VendorLedgerService;
use Tests\TestCase;

class VendorPayoutAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $a = User::create(['name' => 'A', 'email' => 'a' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        UserAdmin::create(['user_id' => $a->id, 'is_active' => 1]);
        return $a;
    }

    private function vendorWithBalance(float $earning): Vendor
    {
        $u = User::create(['name' => 'V', 'email' => 'v' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        $vendor = Vendor::create(['user_id' => $u->id, 'store_name_in_arabic' => 'م', 'store_name_in_german' => 'L', 'status' => 'Active']);
        $cust = User::create(['name' => 'C', 'email' => 'c' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        $address = UserAddress::create(['user_id' => $cust->id, 'address' => '1 Test St']);
        $order = Order::create(['user_id' => $cust->id, 'address_id' => $address->id, 'order_number' => 'O' . uniqid(), 'payment_id' => 'P' . uniqid(), 'total_amount' => 100, 'final_price' => 100, 'payment_status' => 'processing', 'order_status' => 'confirmed']);
        $category = Category::create(['name_arabic' => 'فئة', 'name_german' => 'Kat', 'slug_arabic' => 'cat-ar-' . uniqid(), 'slug_german' => 'cat-de-' . uniqid()]);
        $product = Product::create(['name_arabic' => 'منتج', 'name_german' => 'Prod', 'slug_arabic' => 'prod-ar-' . uniqid(), 'slug_german' => 'prod-de-' . uniqid(), 'category_id' => $category->id, 'vendor_id' => $vendor->id, 'is_active' => true]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'price' => 100, 'stock' => 10, 'sku' => 'SKU' . uniqid(), 'is_active' => true]);
        $item = OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_variant_id' => $variant->id, 'vendor_id' => $vendor->id, 'quantity' => 1, 'unit_price' => 100, 'subtotal' => 100, 'final_price' => 100, 'vendor_earning' => $earning, 'collection_status' => 'collected']);
        (new VendorLedgerService())->creditEarning($item);
        return $vendor;
    }

    public function test_admin_can_record_payout_within_balance(): void
    {
        $vendor = $this->vendorWithBalance(90.0);
        $this->actingAs($this->admin())
            ->post(route('vendor-payouts.payout', $vendor->id), ['amount' => 50, 'method' => 'bank', 'reference' => 'R1'])
            ->assertRedirect();
        $this->assertSame(40.0, (new VendorLedgerService())->payableBalance($vendor->id));
    }

    public function test_payout_over_balance_is_rejected(): void
    {
        $vendor = $this->vendorWithBalance(90.0);
        $this->actingAs($this->admin())
            ->post(route('vendor-payouts.payout', $vendor->id), ['amount' => 999, 'method' => 'bank'])
            ->assertSessionHasErrors();
        $this->assertSame(90.0, (new VendorLedgerService())->payableBalance($vendor->id));
    }
}

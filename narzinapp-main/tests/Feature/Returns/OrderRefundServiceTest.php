<?php

namespace Tests\Feature\Returns;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\Checkout\Services\OrderRefundService;
use Tests\TestCase;

class OrderRefundServiceTest extends TestCase
{
    use RefreshDatabase;

    private function paidOrderWithItem(User $user, int $stock = 5, int $qty = 2): array
    {
        $addressId = DB::table('user_address')->insertGetId(['user_id' => $user->id, 'address' => '1 St', 'created_at' => now(), 'updated_at' => now()]);
        $vendorId = DB::table('vendors')->insertGetId(['store_name_in_arabic' => 'م', 'store_name_in_german' => 'L', 'user_id' => User::factory()->create()->id, 'created_at' => now(), 'updated_at' => now()]);
        $categoryId = DB::table('categories')->insertGetId(['name_arabic' => 'ف', 'name_german' => 'K', 'slug_arabic' => 'c-' . uniqid(), 'slug_german' => 'c-' . uniqid(), 'created_at' => now(), 'updated_at' => now()]);
        $productId = DB::table('products')->insertGetId(['name_arabic' => 'م', 'name_german' => 'P', 'slug_arabic' => 'p-' . uniqid(), 'slug_german' => 'p-' . uniqid(), 'category_id' => $categoryId, 'vendor_id' => $vendorId, 'is_active' => true, 'weight' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $variantId = DB::table('product_variants')->insertGetId(['product_id' => $productId, 'price' => 50, 'stock' => $stock, 'sku' => 'SKU-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false, 'created_at' => now(), 'updated_at' => now()]);

        $order = Order::create([
            'user_id' => $user->id, 'address_id' => $addressId, 'order_number' => 'T-' . uniqid(),
            'order_status' => 'pending', 'payment_status' => 'completed', 'total_amount' => 100, 'final_price' => 100,
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $productId, 'product_variant_id' => $variantId,
            'quantity' => $qty, 'vendor_id' => $vendorId, 'unit_price' => 50, 'subtotal' => 100, 'final_price' => 100,
            'vendor_earning' => 40,
        ]);

        return ['order' => $order, 'variant_id' => $variantId, 'stock' => $stock, 'qty' => $qty];
    }

    public function test_refund_credits_wallet_refills_stock_sets_refunded(): void
    {
        $user = User::factory()->create();
        $f = $this->paidOrderWithItem($user);

        $amount = (new OrderRefundService())->refundWholeOrder($f['order'], 'customer return', null);

        $this->assertEquals(100.00, $amount);
        $this->assertEquals(100, DB::table('user_wallet')->where('user_id', $user->id)->value('balance'));
        $this->assertEquals($f['stock'] + $f['qty'], DB::table('product_variants')->where('id', $f['variant_id'])->value('stock'));
        $this->assertDatabaseHas('orders', ['id' => $f['order']->id, 'payment_status' => 'refunded', 'order_status' => 'cancelled']);
    }

    public function test_refund_is_idempotent_on_already_refunded(): void
    {
        $user = User::factory()->create();
        $f = $this->paidOrderWithItem($user);
        $svc = new OrderRefundService();
        $svc->refundWholeOrder($f['order'], 'r', null);

        $second = $svc->refundWholeOrder($f['order']->fresh(), 'r again', null);

        $this->assertEquals(0.0, $second); // no-op
        $this->assertEquals(100, DB::table('user_wallet')->where('user_id', $user->id)->value('balance')); // not double-credited
    }
}

<?php

namespace Tests\Feature\Returns;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Models\UserAdmin;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\Checkout\Models\OrderReturn;
use Tests\TestCase;

class AdminReturnApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $u = User::create(['name' => 'A', 'email' => 'a' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        UserAdmin::create(['user_id' => $u->id, 'is_active' => 1]);
        return $u;
    }

    private function returnFor(string $status = 'requested', array $attrs = []): OrderReturn
    {
        $user = User::factory()->create();
        $addressId = DB::table('user_address')->insertGetId(['user_id' => $user->id, 'address' => '1 St', 'created_at' => now(), 'updated_at' => now()]);
        $vendorId = DB::table('vendors')->insertGetId(['store_name_in_arabic' => 'م', 'store_name_in_german' => 'L', 'user_id' => User::factory()->create()->id, 'created_at' => now(), 'updated_at' => now()]);
        $categoryId = DB::table('categories')->insertGetId(['name_arabic' => 'ف', 'name_german' => 'K', 'slug_arabic' => 'c-' . uniqid(), 'slug_german' => 'c-' . uniqid(), 'created_at' => now(), 'updated_at' => now()]);
        $productId = DB::table('products')->insertGetId(['name_arabic' => 'م', 'name_german' => 'P', 'slug_arabic' => 'p-' . uniqid(), 'slug_german' => 'p-' . uniqid(), 'category_id' => $categoryId, 'vendor_id' => $vendorId, 'is_active' => true, 'weight' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $variantId = DB::table('product_variants')->insertGetId(['product_id' => $productId, 'price' => 50, 'stock' => 5, 'sku' => 'SKU-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false, 'created_at' => now(), 'updated_at' => now()]);
        $order = Order::create(['user_id' => $user->id, 'address_id' => $addressId, 'order_number' => 'T-' . uniqid(), 'order_status' => 'pending', 'payment_status' => 'completed', 'total_amount' => 100, 'final_price' => 100]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $productId, 'product_variant_id' => $variantId, 'quantity' => 2, 'vendor_id' => $vendorId, 'unit_price' => 50, 'subtotal' => 100, 'final_price' => 100, 'vendor_earning' => 40]);
        return OrderReturn::create(array_merge(['order_id' => $order->id, 'user_id' => $user->id, 'reason' => 'damaged', 'status' => $status, 'requested_at' => now()], $attrs));
    }

    public function test_approve_moves_requested_to_approved(): void
    {
        $r = $this->returnFor('requested');
        $this->actingAs($this->admin())->post(route('returns.approve', $r->id))->assertRedirect();
        $this->assertDatabaseHas('order_returns', ['id' => $r->id, 'status' => 'approved']);
    }

    public function test_approve_does_not_wipe_customer_note(): void
    {
        $r = $this->returnFor('requested', ['customer_note' => 'Box arrived crushed']);
        $this->actingAs($this->admin())->post(route('returns.approve', $r->id))->assertRedirect();

        $r->refresh();
        $this->assertSame('approved', $r->status);
        $this->assertSame('Box arrived crushed', $r->customer_note);
    }

    public function test_reject_moves_requested_to_rejected(): void
    {
        $r = $this->returnFor('requested');
        $this->actingAs($this->admin())->post(route('returns.reject', $r->id), ['admin_note' => 'no'])->assertRedirect();
        $this->assertDatabaseHas('order_returns', ['id' => $r->id, 'status' => 'rejected']);
    }

    public function test_refund_from_approved_executes_refund(): void
    {
        $r = $this->returnFor('approved');
        $this->actingAs($this->admin())->post(route('returns.refund', $r->id))->assertRedirect();

        $r->refresh();
        $this->assertSame('refunded', $r->status);
        $this->assertEquals(100.00, (float) $r->refund_amount);
        $this->assertDatabaseHas('orders', ['id' => $r->order_id, 'payment_status' => 'refunded']);
        $this->assertEquals(100, DB::table('user_wallet')->where('user_id', $r->user_id)->value('balance'));
    }

    public function test_illegal_transition_rejected(): void
    {
        $r = $this->returnFor('rejected'); // terminal
        $this->actingAs($this->admin())->post(route('returns.approve', $r->id))->assertStatus(422);
        $this->assertDatabaseHas('order_returns', ['id' => $r->id, 'status' => 'rejected']);
    }

    public function test_refund_requires_approved(): void
    {
        $r = $this->returnFor('requested'); // not approved yet
        $this->actingAs($this->admin())->post(route('returns.refund', $r->id))->assertStatus(422);
    }
}

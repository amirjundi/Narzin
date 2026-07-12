<?php

namespace Tests\Feature\Returns;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderReturn;
use Tests\TestCase;

class OrderReturnSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_return_row_round_trips(): void
    {
        $user = User::factory()->create();
        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id, 'address' => '1 St', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $order = Order::create([
            'user_id' => $user->id, 'address_id' => $addressId, 'order_number' => 'T-' . uniqid(),
            'order_status' => 'pending', 'payment_status' => 'completed', 'total_amount' => 100, 'final_price' => 100,
        ]);

        $return = OrderReturn::create([
            'order_id' => $order->id, 'order_item_id' => null, 'user_id' => $user->id,
            'reason' => 'damaged', 'status' => 'requested', 'requested_at' => now(),
        ]);

        $this->assertDatabaseHas('order_returns', [
            'id' => $return->id, 'order_id' => $order->id, 'reason' => 'damaged', 'status' => 'requested',
        ]);
        $this->assertSame($order->id, $return->order->id);
        $this->assertSame($user->id, $return->user->id);
    }
}

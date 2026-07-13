<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Order;
use Tests\TestCase;

class CancellationReasonColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancellation_reason_is_mass_assignable_and_nullable(): void
    {
        $user = User::factory()->create();

        // orders.address_id is NOT NULL with an FK to user_address; seed a row
        // to satisfy it (mirrors tests/Feature/Checkout/PlaceOrderTest.php).
        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id,
            'address' => '123 Test Street',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $addressId,
            'total_amount' => 50.00,
            'order_number' => 'T-1',
            'order_status' => 'pending',
            'cancellation_reason' => null,
        ]);

        $this->assertNull($order->fresh()->cancellation_reason);

        $order->update(['cancellation_reason' => 'out_of_stock']);
        $this->assertSame('out_of_stock', $order->fresh()->cancellation_reason);
    }
}

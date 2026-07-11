<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Order;
use Tests\TestCase;

class OrderAttributionColumnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_accepts_attribution_columns(): void
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
            'attributed_session_id' => 'sess-1',
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'july',
            'utm_term' => 'shoes',
            'utm_content' => 'ad1',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id, 'utm_source' => 'google', 'utm_campaign' => 'july',
            'attributed_session_id' => 'sess-1',
        ]);
    }
}

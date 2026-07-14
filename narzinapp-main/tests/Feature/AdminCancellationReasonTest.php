<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Models\UserAdmin;
use Modules\Checkout\Models\Order;
use Tests\TestCase;

class AdminCancellationReasonTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $attributes = []): Order
    {
        $user = User::factory()->create();

        // orders.address_id is NOT NULL with an FK to user_address; seed a row
        // to satisfy it (mirrors tests/Feature/CancellationReasonColumnTest.php).
        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id,
            'address' => '123 Test Street',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return Order::create(array_merge([
            'user_id' => $user->id,
            'address_id' => $addressId,
            'total_amount' => 50.00,
            'order_number' => 'T-' . uniqid(),
            'order_status' => 'confirmed',
        ], $attributes));
    }

    private function actingAsAdmin(): self
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin' . uniqid() . '@t.test',
            'password' => 'secret123', 'email_verified_at' => now(),
        ]);
        UserAdmin::create(['user_id' => $admin->id, 'is_active' => 1]);

        $this->actingAs($admin);

        return $this;
    }

    public function test_cancelling_via_admin_persists_the_reason(): void
    {
        $order = $this->makeOrder(['order_status' => 'confirmed']);

        $this->actingAsAdmin()
            ->patch(route('orders.update.status', $order->id), [
                'order_status' => 'cancelled',
                'cancellation_reason' => 'out_of_stock',
            ])->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->order_status);
        $this->assertSame('out_of_stock', $order->fresh()->cancellation_reason);
    }

    public function test_non_cancel_status_update_ignores_reason(): void
    {
        $order = $this->makeOrder(['order_status' => 'confirmed']);

        $this->actingAsAdmin()
            ->patch(route('orders.update.status', $order->id), [
                'order_status' => 'shipped',
                'cancellation_reason' => 'out_of_stock',
            ])->assertRedirect();

        $this->assertNull($order->fresh()->cancellation_reason);
    }

    public function test_uncancelling_clears_the_stale_reason(): void
    {
        $order = $this->makeOrder(['order_status' => 'cancelled', 'cancellation_reason' => 'out_of_stock']);

        $this->actingAsAdmin()
            ->patch(route('orders.update.status', $order->id), [
                'order_status' => 'shipped',
            ])->assertRedirect();

        $this->assertSame('shipped', $order->fresh()->order_status);
        $this->assertNull($order->fresh()->cancellation_reason);
    }
}

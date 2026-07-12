<?php

namespace Tests\Feature\Returns;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Order;
use Tests\TestCase;

class CustomerReturnApiTest extends TestCase
{
    use RefreshDatabase;

    private function order(User $user, array $attrs = []): Order
    {
        $addressId = DB::table('user_address')->insertGetId(['user_id' => $user->id, 'address' => '1 St', 'created_at' => now(), 'updated_at' => now()]);
        return Order::create(array_merge([
            'user_id' => $user->id, 'address_id' => $addressId, 'order_number' => 'T-' . uniqid(),
            'order_status' => 'pending', 'payment_status' => 'completed', 'total_amount' => 100, 'final_price' => 100,
        ], $attrs));
    }

    public function test_customer_can_request_a_return(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'damaged'])
            ->assertStatus(201);

        $this->assertDatabaseHas('order_returns', [
            'order_id' => $order->id, 'user_id' => $user->id, 'reason' => 'damaged', 'status' => 'requested',
        ]);
    }

    public function test_cannot_return_someone_elses_order(): void
    {
        $owner = User::factory()->create();
        $order = $this->order($owner);
        $attacker = User::factory()->create();

        $this->actingAs($attacker, 'sanctum')
            ->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'damaged'])
            ->assertStatus(403);
        $this->assertDatabaseCount('order_returns', 0);
    }

    public function test_cannot_return_unpaid_order(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, ['payment_status' => 'not_paid']);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'damaged'])
            ->assertStatus(422);
    }

    public function test_cannot_duplicate_active_return(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);
        $this->actingAs($user, 'sanctum')->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'damaged'])->assertStatus(201);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'wrong_item'])
            ->assertStatus(422);
    }

    public function test_duplicate_return_request_leaves_exactly_one_active_return(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'damaged'])
            ->assertStatus(201);

        // A second request for the same order (simulating a racing duplicate submit)
        // must be rejected by the locked-transaction guard, not create a second row.
        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'wrong_item'])
            ->assertStatus(422)
            ->assertJson(['status' => false, 'message' => 'A return already exists for this order']);

        $this->assertDatabaseCount('order_returns', 1);
        $this->assertDatabaseHas('order_returns', [
            'order_id' => $order->id, 'reason' => 'damaged', 'status' => 'requested',
        ]);
    }

    public function test_invalid_reason_rejected(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'nonsense'])
            ->assertStatus(422);
    }

    public function test_list_returns_only_mine(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);
        $this->actingAs($user, 'sanctum')->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'damaged']);

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/returns')
            ->assertOk()->assertJsonPath('data.0.reason', 'damaged');
    }
}

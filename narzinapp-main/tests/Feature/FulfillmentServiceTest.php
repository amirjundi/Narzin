<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\FulfillmentService;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderAudit;
use Tests\TestCase;

class FulfillmentServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Mirrors AdminCancellationReasonTest::makeOrder(); Order::factory() isn't
     * registered in this project, and `created_at` isn't mass-assignable on
     * Order (not in $fillable), so it's set on the instance and saved after
     * create() rather than passed into create()'s attribute array.
     */
    private function makeOrder(array $attrs = []): Order
    {
        $user = User::factory()->create();

        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id,
            'address' => '123 Test Street',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $createdAt = $attrs['created_at'] ?? now();
        unset($attrs['created_at']);

        $order = Order::create(array_merge([
            'user_id' => $user->id,
            'address_id' => $addressId,
            'total_amount' => 50.00,
            'order_number' => 'T-' . uniqid(),
            'order_status' => 'pending',
        ], $attrs));

        $order->created_at = $createdAt;
        $order->save();

        return $order->fresh();
    }

    private function audit(Order $o, string $newStatus, Carbon $at): void
    {
        OrderAudit::create([
            'order_id' => $o->id,
            'action' => 'status_updated_by_admin',
            'new_order_status' => $newStatus,
            'triggered_by' => 'admin',
            'created_at' => $at,
        ]);
    }

    public function test_sla_stage_durations_and_breach(): void
    {
        config(['telemetry.fulfillment_sla_hours' => 48]);
        $placed = Carbon::parse('2026-07-01 00:00:00');

        // Order A: confirmed +2h, shipped +10h (placed_to_ship=10h, within SLA), delivered +34h
        $a = $this->makeOrder(['created_at' => $placed, 'order_status' => 'delivered']);
        $this->audit($a, 'confirmed', $placed->copy()->addHours(2));
        $this->audit($a, 'shipped', $placed->copy()->addHours(10));
        $this->audit($a, 'delivered', $placed->copy()->addHours(34));

        // Order B: shipped +60h from placed → breaches 48h SLA
        $b = $this->makeOrder(['created_at' => $placed, 'order_status' => 'shipped']);
        $this->audit($b, 'confirmed', $placed->copy()->addHours(1));
        $this->audit($b, 'shipped', $placed->copy()->addHours(60));

        $range = new DateRange(Carbon::parse('2026-06-30'), Carbon::parse('2026-07-31'));
        $sla = (new FulfillmentService())->slaSummary($range);

        // placed_to_ship: A=10h, B=60h → count 2, breach 1/2 = 0.5
        $this->assertSame(2, $sla['stages']['placed_to_ship']['count']);
        $this->assertEqualsWithDelta(35.0, $sla['stages']['placed_to_ship']['avg_hours'], 0.01);
        $this->assertEqualsWithDelta(10.0, $sla['stages']['placed_to_ship']['median_hours'], 0.01);
        $this->assertEqualsWithDelta(60.0, $sla['stages']['placed_to_ship']['p90_hours'], 0.01);
        $this->assertSame(0.5, $sla['breach_rate']);

        // ship_to_deliver: only A shipped→delivered = 24h; B not delivered
        $this->assertSame(1, $sla['stages']['ship_to_deliver']['count']);
        $this->assertEqualsWithDelta(24.0, $sla['stages']['ship_to_deliver']['avg_hours'], 0.01);
    }

    public function test_cancellations_by_reason_counts_both_spellings(): void
    {
        $placed = Carbon::parse('2026-07-02 00:00:00');
        $this->makeOrder(['created_at' => $placed, 'order_status' => 'cancelled', 'cancellation_reason' => 'out_of_stock']);
        $this->makeOrder(['created_at' => $placed, 'order_status' => 'canceled', 'cancellation_reason' => 'payment_failed']); // one-L historical
        $this->makeOrder(['created_at' => $placed, 'order_status' => 'cancelled', 'cancellation_reason' => null]); // unspecified
        $this->makeOrder(['created_at' => $placed, 'order_status' => 'delivered']); // not cancelled

        $range = new DateRange(Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31'));
        $c = (new FulfillmentService())->cancellations($range);

        $this->assertSame(3, $c['total_cancelled']);      // both spellings counted
        $this->assertSame(4, $c['total_orders']);
        $this->assertEqualsWithDelta(0.75, $c['cancellation_rate'], 0.001);
        $reasons = $c['by_reason']->pluck('count', 'reason');
        $this->assertSame(1, $reasons['out_of_stock']);
        $this->assertSame(1, $reasons['payment_failed']);
        $this->assertSame(1, $reasons['(unspecified)']);
    }
}

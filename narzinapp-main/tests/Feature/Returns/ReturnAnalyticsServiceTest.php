<?php

namespace Tests\Feature\Returns;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\ReturnAnalyticsService;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderReturn;
use Tests\TestCase;

class ReturnAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private function range(): DateRange
    {
        return new DateRange(now()->subDays(30)->startOfDay(), now()->endOfDay());
    }

    private function order(): Order
    {
        $user = User::factory()->create();
        $addressId = DB::table('user_address')->insertGetId(['user_id' => $user->id, 'address' => '1 St', 'created_at' => now(), 'updated_at' => now()]);
        return Order::create(['user_id' => $user->id, 'address_id' => $addressId, 'order_number' => 'T-' . uniqid(), 'order_status' => 'pending', 'payment_status' => 'completed', 'total_amount' => 100, 'final_price' => 100]);
    }

    private function ret(Order $o, string $status, string $reason, ?float $refund = null): void
    {
        OrderReturn::create(['order_id' => $o->id, 'user_id' => $o->user_id, 'reason' => $reason, 'status' => $status, 'refund_amount' => $refund, 'requested_at' => now()]);
    }

    public function test_summary_counts_and_rate(): void
    {
        $o1 = $this->order();
        $o2 = $this->order();
        $o3 = $this->order(); // no return
        $this->ret($o1, 'refunded', 'damaged', 100);
        $this->ret($o2, 'requested', 'wrong_item');

        $s = (new ReturnAnalyticsService())->summary($this->range());
        $this->assertSame(2, $s['requested'] + $s['refunded']); // 2 returns total across statuses
        $this->assertSame(1, $s['refunded']);
        $this->assertEquals(100.00, $s['total_refunded']);
        $this->assertEquals(round(2 / 3, 4), $s['return_rate']); // 2 returns / 3 orders
    }

    public function test_by_reason(): void
    {
        $o1 = $this->order(); $o2 = $this->order();
        $this->ret($o1, 'requested', 'damaged');
        $this->ret($o2, 'requested', 'damaged');

        $rows = (new ReturnAnalyticsService())->byReason($this->range());
        $this->assertSame('damaged', $rows->first()['reason']);
        $this->assertSame(2, $rows->first()['count']);
    }

    public function test_no_orders_no_divide_by_zero(): void
    {
        $s = (new ReturnAnalyticsService())->summary($this->range());
        $this->assertEquals(0.0, $s['return_rate']);
    }
}

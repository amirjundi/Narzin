<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\PaymentAnalyticsService;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\PaymentAttempt;
use Tests\TestCase;

class PaymentAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private function range(): DateRange
    {
        return new DateRange(now()->subDays(30)->startOfDay(), now()->endOfDay());
    }

    private function order(array $attrs): void
    {
        $user = User::factory()->create();
        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id, 'address' => '1 St', 'created_at' => now(), 'updated_at' => now(),
        ]);
        Order::create(array_merge([
            'user_id' => $user->id, 'address_id' => $addressId,
            'order_number' => 'T-' . uniqid(), 'order_status' => 'pending',
            'total_amount' => 100, 'price_after_discount' => 100,
        ], $attrs));
    }

    public function test_order_payment_summary_success_rate(): void
    {
        $this->order(['payment_status' => 'completed']);
        $this->order(['payment_status' => 'completed']);
        $this->order(['payment_status' => 'failed']);
        $this->order(['payment_status' => 'not_paid']); // pending, excluded from rate

        $s = (new PaymentAnalyticsService())->orderPaymentSummary($this->range());
        $this->assertSame(2, $s['completed']);
        $this->assertSame(1, $s['failed']);
        $this->assertEquals(round(2 / 3, 4), $s['success_rate']); // 2 completed / 3 resolved
    }

    public function test_method_mix_wallet_vs_gateway(): void
    {
        $this->order(['payment_status' => 'completed', 'wallet_usage' => 30]);
        $this->order(['payment_status' => 'completed', 'wallet_usage' => 0]);

        $s = (new PaymentAnalyticsService())->methodMix($this->range());
        $this->assertSame(1, $s['wallet_involved']);
        $this->assertSame(1, $s['gateway_only']);
    }

    public function test_attempt_summary_and_failure_reasons(): void
    {
        PaymentAttempt::create(['gateway' => 'nass', 'status' => 'success', 'response_code' => '00', 'occurred_at' => now()]);
        PaymentAttempt::create(['gateway' => 'nass', 'status' => 'failed', 'response_code' => '51', 'occurred_at' => now()]);
        PaymentAttempt::create(['gateway' => 'nass', 'status' => 'failed', 'response_code' => '51', 'occurred_at' => now()]);

        $svc = new PaymentAnalyticsService();
        $a = $svc->attemptSummary($this->range());
        $this->assertSame(3, $a['total']);
        $this->assertSame(1, $a['success']);
        $this->assertSame(2, $a['failed']);
        $this->assertEquals(round(1 / 3, 4), $a['gateway_success_rate']);

        $reasons = $svc->failureReasons($this->range());
        $this->assertSame('51', $reasons->first()['response_code']);
        $this->assertSame(2, $reasons->first()['count']);
    }

    public function test_no_data_no_divide_by_zero(): void
    {
        $s = (new PaymentAnalyticsService())->orderPaymentSummary($this->range());
        $this->assertEquals(0.0, $s['success_rate']);
        $a = (new PaymentAnalyticsService())->attemptSummary($this->range());
        $this->assertEquals(0.0, $a['gateway_success_rate']);
    }
}

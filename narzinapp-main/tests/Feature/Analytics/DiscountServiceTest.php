<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\DiscountService;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Coupon;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\Promotion;
use Tests\TestCase;

class DiscountServiceTest extends TestCase
{
    use RefreshDatabase;

    private function range(): DateRange
    {
        return new DateRange(now()->subDays(30)->startOfDay(), now()->endOfDay());
    }

    // orders.address_id is a NOT NULL FK; seed a user_address (mirrors OrderAttributionColumnsTest).
    private function order(array $attrs): Order
    {
        $user = User::factory()->create();
        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id, 'address' => '1 St', 'created_at' => now(), 'updated_at' => now(),
        ]);
        return Order::create(array_merge([
            'user_id' => $user->id,
            'address_id' => $addressId,
            'order_number' => 'T-' . uniqid(),
            'order_status' => 'completed',
            'total_amount' => 100.00,
            'price_after_discount' => 100.00,
        ], $attrs));
    }

    private function coupon(string $code): Coupon
    {
        return Coupon::create([
            'code' => $code, 'discount_amount' => 10, 'discount_type' => 'fixed', 'used' => 0, 'is_active' => true,
        ]);
    }

    private function promotion(string $name): Promotion
    {
        return Promotion::create(['name' => $name, 'type' => 'fixed', 'value' => 10, 'is_active' => true]);
    }

    public function test_by_coupon_aggregates_redemptions_discount_placed_value(): void
    {
        $c = $this->coupon('SAVE10');
        // two redemptions: total 100 each, discounted to 90 → discount 10 each
        $this->order(['coupon_id' => $c->id, 'total_amount' => 100, 'price_after_discount' => 90]);
        $this->order(['coupon_id' => $c->id, 'total_amount' => 100, 'price_after_discount' => 90]);
        // an order with no coupon must be excluded
        $this->order(['total_amount' => 500, 'price_after_discount' => 500]);

        $rows = (new DiscountService())->byCoupon($this->range());
        $row = $rows->firstWhere('coupon_id', $c->id);

        $this->assertSame('SAVE10', $row['code']);
        $this->assertSame(2, $row['redemptions']);
        $this->assertEquals(20.00, $row['discount_given']);   // 10 + 10
        $this->assertEquals(200.00, $row['placed_value']);    // 100 + 100
        $this->assertEquals(100.00, $row['aov']);             // 200 / 2
    }

    public function test_by_promotion_aggregates(): void
    {
        $p = $this->promotion('Summer');
        $this->order(['promotion_id' => $p->id, 'total_amount' => 200, 'price_after_discount' => 170]);

        $rows = (new DiscountService())->byPromotion($this->range());
        $row = $rows->firstWhere('promotion_id', $p->id);

        $this->assertSame('Summer', $row['name']);
        $this->assertSame(1, $row['redemptions']);
        $this->assertEquals(30.00, $row['discount_given']);
    }

    public function test_deleted_promotion_labelled(): void
    {
        // orders.promotion_id has NO foreign key (unlike coupon_id, which has
        // onDelete('set null') — so a dangling coupon_id is impossible and the
        // coupon '(deleted)' path is unreachable). A dangling promotion_id CAN
        // exist, so the '(deleted)' label is testable here.
        $this->order(['promotion_id' => 9999, 'total_amount' => 100, 'price_after_discount' => 95]);

        $rows = (new DiscountService())->byPromotion($this->range());
        $row = $rows->firstWhere('promotion_id', 9999);
        $this->assertSame('(deleted)', $row['name']);
        $this->assertEquals(5.00, $row['discount_given']);
    }

    public function test_summary_penetration(): void
    {
        $c = $this->coupon('X');
        $this->order(['coupon_id' => $c->id, 'total_amount' => 100, 'price_after_discount' => 90]);
        $this->order(['total_amount' => 100, 'price_after_discount' => 100]); // no discount

        $s = (new DiscountService())->summary($this->range());
        $this->assertSame(1, $s['discounted_orders']);
        $this->assertSame(2, $s['total_orders']);
        $this->assertEquals(0.5, $s['discount_rate']);
        $this->assertEquals(10.00, $s['total_discount']);
    }

    public function test_summary_no_orders_no_divide_by_zero(): void
    {
        $s = (new DiscountService())->summary($this->range());
        $this->assertSame(0, $s['total_orders']);
        $this->assertEquals(0.0, $s['discount_rate']);
    }
}

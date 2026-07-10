<?php

namespace Tests\Feature\Analytics;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Services\AbandonedCartService;
use Modules\Admin\Support\DateRange;
use Modules\Telemetry\Models\CartEvent;
use Modules\Telemetry\Models\CheckoutEvent;
use Tests\TestCase;

class AbandonedCartServiceTest extends TestCase
{
    use RefreshDatabase;

    private function range(): DateRange
    {
        return new DateRange(now()->subDays(30)->startOfDay(), now()->endOfDay());
    }

    public function test_session_with_old_add_and_no_order_is_abandoned(): void
    {
        CartEvent::create(['session_id' => 'a1', 'product_id' => 1, 'action' => 'add', 'quantity' => 2, 'unit_price' => 10.00, 'occurred_at' => now()->subHours(48)]);

        $rows = (new AbandonedCartService())->abandoned($this->range(), 24);

        $this->assertCount(1, $rows);
        $this->assertSame('a1', $rows->first()['session_id']);
        $this->assertEquals(20.00, $rows->first()['cart_value']);   // 2 x 10.00
        $this->assertSame(2, $rows->first()['item_count']);
    }

    public function test_session_that_placed_is_excluded(): void
    {
        CartEvent::create(['session_id' => 'a2', 'product_id' => 1, 'action' => 'add', 'quantity' => 1, 'unit_price' => 10.00, 'occurred_at' => now()->subHours(48)]);
        CheckoutEvent::create(['session_id' => 'a2', 'step' => 'placed', 'order_id' => 1, 'occurred_at' => now()->subHours(47)]);

        $rows = (new AbandonedCartService())->abandoned($this->range(), 24);
        $this->assertCount(0, $rows);
    }

    public function test_recent_add_within_window_is_excluded(): void
    {
        CartEvent::create(['session_id' => 'a3', 'product_id' => 1, 'action' => 'add', 'quantity' => 1, 'unit_price' => 10.00, 'occurred_at' => now()->subHours(2)]);

        $rows = (new AbandonedCartService())->abandoned($this->range(), 24);
        $this->assertCount(0, $rows);
    }

    public function test_cart_value_reflects_remove_and_update(): void
    {
        // add product 1 (qty 2), add product 2 (qty 1), then remove product 1
        CartEvent::create(['session_id' => 'a4', 'product_id' => 1, 'action' => 'add', 'quantity' => 2, 'unit_price' => 10.00, 'occurred_at' => now()->subHours(50)]);
        CartEvent::create(['session_id' => 'a4', 'product_id' => 2, 'action' => 'add', 'quantity' => 1, 'unit_price' => 30.00, 'occurred_at' => now()->subHours(49)]);
        CartEvent::create(['session_id' => 'a4', 'product_id' => 1, 'action' => 'remove', 'quantity' => 0, 'unit_price' => 10.00, 'occurred_at' => now()->subHours(48)]);

        $rows = (new AbandonedCartService())->abandoned($this->range(), 24);
        $this->assertCount(1, $rows);
        $this->assertEquals(30.00, $rows->first()['cart_value']);   // only product 2 remains
        $this->assertSame(1, $rows->first()['item_count']);
    }

    public function test_window_is_configurable(): void
    {
        CartEvent::create(['session_id' => 'a5', 'product_id' => 1, 'action' => 'add', 'quantity' => 1, 'unit_price' => 10.00, 'occurred_at' => now()->subHours(5)]);

        // 3h window: a 5h-old cart is abandoned; default 24h window: it is not.
        $this->assertCount(1, (new AbandonedCartService())->abandoned($this->range(), 3));
        $this->assertCount(0, (new AbandonedCartService())->abandoned($this->range(), 24));
    }
}

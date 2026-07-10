<?php

namespace Tests\Feature\Analytics;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Services\FunnelService;
use Modules\Admin\Support\DateRange;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Modules\Telemetry\Models\CartEvent;
use Modules\Telemetry\Models\CheckoutEvent;
use Modules\Telemetry\Models\UserProductView;
use Tests\TestCase;

class FunnelServiceTest extends TestCase
{
    use RefreshDatabase;

    private function range(): DateRange
    {
        return new DateRange(now()->subDays(7)->startOfDay(), now()->endOfDay());
    }

    // user_product_views has a FK on product_id (enforced in the sqlite test
    // DB), so product views need a real product row.
    private function product(): Product
    {
        $cat = Category::create([
            'name_arabic' => 'ف', 'name_german' => 'K',
            'slug_arabic' => 'c-' . uniqid(), 'slug_german' => 'c-' . uniqid(),
        ]);
        return Product::create([
            'name_arabic' => 'م', 'name_german' => 'P',
            'slug_arabic' => 'p-' . uniqid(), 'slug_german' => 'p-' . uniqid(),
            'category_id' => $cat->id, 'is_active' => true,
        ]);
    }

    private function stage(array $funnel, string $key): array
    {
        foreach ($funnel['stages'] as $s) {
            if ($s['key'] === $key) return $s;
        }
        $this->fail("stage {$key} not found");
    }

    public function test_counts_distinct_actors_per_stage(): void
    {
        $p = $this->product();
        // product_view: two distinct sessions
        UserProductView::create(['product_id' => $p->id, 'session_id' => 's1', 'dwell_time_seconds' => 3]);
        UserProductView::create(['product_id' => $p->id, 'session_id' => 's2', 'dwell_time_seconds' => 3]);
        // cart_add: one session, added twice -> counts once
        CartEvent::create(['session_id' => 's1', 'product_id' => 1, 'action' => 'add', 'quantity' => 1, 'occurred_at' => now()]);
        CartEvent::create(['session_id' => 's1', 'product_id' => 2, 'action' => 'add', 'quantity' => 1, 'occurred_at' => now()]);
        // checkout events carry null session_id but a user_id (real current shape)
        CheckoutEvent::create(['session_id' => null, 'user_id' => 5, 'step' => 'checkout_start', 'occurred_at' => now()]);
        CheckoutEvent::create(['session_id' => null, 'user_id' => 5, 'step' => 'placed', 'order_id' => 99, 'occurred_at' => now()]);

        $f = (new FunnelService())->funnel($this->range());

        $this->assertSame(2, $this->stage($f, 'product_view')['count']);
        $this->assertSame(1, $this->stage($f, 'cart_add')['count']);           // s1 counted once despite 2 adds
        $this->assertSame(1, $this->stage($f, 'checkout_start')['count']);      // via user:5 fallback
        $this->assertSame(1, $this->stage($f, 'placed')['count']);             // via user:5 fallback
    }

    public function test_excludes_events_outside_range(): void
    {
        UserProductView::create(['product_id' => $this->product()->id, 'session_id' => 'old', 'dwell_time_seconds' => 1]);
        // shift it far into the past
        UserProductView::where('session_id', 'old')->update(['created_at' => now()->subDays(60)]);

        $f = (new FunnelService())->funnel($this->range());
        $this->assertSame(0, $this->stage($f, 'product_view')['count']);
    }
}

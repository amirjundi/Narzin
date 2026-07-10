# Analytics Phase 2 — Funnel + Abandoned Cart Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a conversion-funnel + abandoned-cart admin report over the Phase 1 capture tables, plus a reusable `DateRange` date-range foundation.

**Architecture:** Two read-only query services (`FunnelService`, `AbandonedCartService`) in the Admin module read the Phase 1 Telemetry tables. A `DateRange` value object carries the reporting window. A new `funnelStatistics` controller action renders a new `admin::statistics.funnel` Blade page. No new tables, no client changes.

**Tech Stack:** Laravel 11, nwidart/laravel-modules, PHPUnit 11, SQLite test DB with `RefreshDatabase`. Admin web routes are behind `admin.auth` middleware; tests authenticate with `actingAs($user)` where `$user` has a `Modules\Admin\Models\UserAdmin` row.

## Global Constraints

- Money values are `decimal(12,2)`; cart value derives from `cart_events.unit_price × quantity`. (from spec)
- `DateRange` defaults to `[now()->subDays(30)->startOfDay(), now()->endOfDay()]`; invalid/absent input or `from > to` → default. (from spec)
- Funnel stage counts = distinct **actor key** = `session_id` when present, else `user:{user_id}`, else excluded. **Compute the actor key in PHP, not SQL** — SQLite (<3.44), MySQL, and Postgres disagree on string concatenation (`CONCAT` vs `||`), so a SQL expression is not portable across prod and the test DB. (refines spec's SQL suggestion)
- Abandoned-cart window hours = `$windowHours ?? config('telemetry.abandoned_cart_hours', 24)`. (from spec)
- No data fabrication: counts are whatever the tables hold; client-dependent stages read 0 until their hooks land, and the page says so. (from spec)
- Reporting is server-rendered Blade only (no JSON API, no CSV this phase). (from spec)
- Phase 1 models already exist: `Modules\Telemetry\Models\{VisitSession, CartEvent, CheckoutEvent, SearchLog}` and `Modules\Telemetry\Models\UserProductView`. Reuse them; do not create new tables.

---

### Task 1: DateRange value object

**Files:**
- Create: `Modules/Admin/app/Support/DateRange.php`
- Test: `tests/Feature/Analytics/DateRangeTest.php`

**Interfaces:**
- Produces `Modules\Admin\Support\DateRange` with public readonly `Carbon $from`, `Carbon $to`, and static `fromRequest(Request $r, int $defaultDays = 30): self`. Tasks 2–4 consume it.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Analytics/DateRangeTest.php`:

```php
<?php

namespace Tests\Feature\Analytics;

use Illuminate\Http\Request;
use Modules\Admin\Support\DateRange;
use Tests\TestCase;

class DateRangeTest extends TestCase
{
    public function test_defaults_to_last_30_days_when_no_params(): void
    {
        $r = DateRange::fromRequest(new Request());
        $this->assertSame(now()->subDays(30)->startOfDay()->toDateString(), $r->from->toDateString());
        $this->assertSame(now()->endOfDay()->toDateString(), $r->to->toDateString());
    }

    public function test_parses_valid_from_and_to(): void
    {
        $r = DateRange::fromRequest(new Request(['from' => '2026-01-01', 'to' => '2026-01-31']));
        $this->assertSame('2026-01-01', $r->from->toDateString());
        $this->assertSame('2026-01-31', $r->to->toDateString());
        $this->assertSame('00:00:00', $r->from->format('H:i:s'));
        $this->assertSame('23:59:59', $r->to->format('H:i:s'));
    }

    public function test_invalid_input_falls_back_to_default(): void
    {
        $r = DateRange::fromRequest(new Request(['from' => 'not-a-date', 'to' => '2026-01-31']));
        $this->assertSame(now()->subDays(30)->startOfDay()->toDateString(), $r->from->toDateString());
    }

    public function test_from_after_to_falls_back_to_default(): void
    {
        $r = DateRange::fromRequest(new Request(['from' => '2026-02-01', 'to' => '2026-01-01']));
        $this->assertSame(now()->subDays(30)->startOfDay()->toDateString(), $r->from->toDateString());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DateRangeTest`
Expected: FAIL — `Class "Modules\Admin\Support\DateRange" not found`.

- [ ] **Step 3: Write the value object**

Create `Modules/Admin/app/Support/DateRange.php`:

```php
<?php

namespace Modules\Admin\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Immutable reporting window. Reads optional Y-m-d `from`/`to` query params,
 * defaulting to the last N days. `to` is inclusive (end of day).
 */
class DateRange
{
    public function __construct(
        public readonly Carbon $from,
        public readonly Carbon $to,
    ) {}

    public static function fromRequest(Request $request, int $defaultDays = 30): self
    {
        $default = new self(
            Carbon::now()->subDays($defaultDays)->startOfDay(),
            Carbon::now()->endOfDay(),
        );

        $fromRaw = $request->query('from');
        $toRaw = $request->query('to');
        if (!is_string($fromRaw) || !is_string($toRaw)) {
            return $default;
        }

        try {
            $from = Carbon::createFromFormat('Y-m-d', $fromRaw)->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', $toRaw)->endOfDay();
        } catch (\Throwable $e) {
            return $default;
        }

        // Reject overflow dates that Carbon silently rolls over (e.g. 2026-13-40).
        if ($from->format('Y-m-d') !== $fromRaw || $to->format('Y-m-d') !== $toRaw) {
            return $default;
        }
        if ($from->greaterThan($to)) {
            return $default;
        }

        return new self($from, $to);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=DateRangeTest`
Expected: PASS (4 tests).

- [ ] **Step 5: Commit**

```bash
git add Modules/Admin/app/Support/DateRange.php tests/Feature/Analytics/DateRangeTest.php
git commit -m "feat(analytics): DateRange value object for report windows"
```

---

### Task 2: FunnelService

**Files:**
- Create: `Modules/Admin/app/Services/FunnelService.php`
- Test: `tests/Feature/Analytics/FunnelServiceTest.php`

**Interfaces:**
- Consumes: `DateRange` (Task 1); Phase 1 models.
- Produces `Modules\Admin\Services\FunnelService` with `funnel(DateRange $range): array` returning:
  `['stages' => [ ['key','label','count','conversion_from_prev'], ... ], 'overall_conversion' => float]`.
  Stage order: sessions, product_view, cart_add, checkout_start, placed. Task 4 consumes it.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Analytics/FunnelServiceTest.php`:

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=FunnelServiceTest`
Expected: FAIL — `Class "Modules\Admin\Services\FunnelService" not found`.

- [ ] **Step 3: Write the service**

Create `Modules/Admin/app/Services/FunnelService.php`:

```php
<?php

namespace Modules\Admin\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Support\DateRange;
use Modules\Telemetry\Models\CartEvent;
use Modules\Telemetry\Models\CheckoutEvent;
use Modules\Telemetry\Models\UserProductView;
use Modules\Telemetry\Models\VisitSession;

/**
 * Read-only conversion funnel over the Phase 1 capture tables. Each stage is a
 * count of distinct "actors" (session_id, else user:{user_id}) in the window.
 */
class FunnelService
{
    public function funnel(DateRange $range): array
    {
        $counts = [
            'sessions'       => $this->distinctActors(VisitSession::query()->whereBetween('first_seen_at', [$range->from, $range->to])),
            'product_view'   => $this->distinctActors(UserProductView::query()->whereBetween('created_at', [$range->from, $range->to])),
            'cart_add'       => $this->distinctActors(CartEvent::query()->where('action', 'add')->whereBetween('occurred_at', [$range->from, $range->to])),
            'checkout_start' => $this->distinctActors(CheckoutEvent::query()->where('step', 'checkout_start')->whereBetween('occurred_at', [$range->from, $range->to])),
            'placed'         => $this->distinctActors(CheckoutEvent::query()->where('step', 'placed')->whereBetween('occurred_at', [$range->from, $range->to])),
        ];

        $labels = [
            'sessions' => 'Sessions',
            'product_view' => 'Product View',
            'cart_add' => 'Add to Cart',
            'checkout_start' => 'Checkout Started',
            'placed' => 'Order Placed',
        ];

        $stages = [];
        $prev = null;
        foreach ($counts as $key => $count) {
            $stages[] = [
                'key' => $key,
                'label' => $labels[$key],
                'count' => $count,
                'conversion_from_prev' => $prev === null ? null : ($prev > 0 ? round($count / $prev, 4) : 0.0),
            ];
            $prev = $count;
        }

        $overall = $counts['sessions'] > 0 ? round($counts['placed'] / $counts['sessions'], 4) : 0.0;

        return ['stages' => $stages, 'overall_conversion' => $overall];
    }

    /**
     * Distinct actor count. Actor = session_id, else "user:{user_id}", else
     * excluded. Computed in PHP (not SQL) so it is portable across MySQL,
     * Postgres, and the SQLite test DB, which disagree on string concatenation.
     * ponytail: pulls distinct (session_id,user_id) pairs — bounded by actor
     * count; revisit with a keyed rollup table if the event tables get huge.
     */
    private function distinctActors(Builder $query): int
    {
        return $query->distinct()
            ->get(['session_id', 'user_id'])
            ->map(fn ($r) => $r->session_id ?? ($r->user_id !== null ? 'user:' . $r->user_id : null))
            ->filter()
            ->unique()
            ->count();
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=FunnelServiceTest`
Expected: PASS (2 tests).

- [ ] **Step 5: Commit**

```bash
git add Modules/Admin/app/Services/FunnelService.php tests/Feature/Analytics/FunnelServiceTest.php
git commit -m "feat(analytics): FunnelService — distinct-actor stage counts + conversions"
```

---

### Task 3: AbandonedCartService

**Files:**
- Create: `Modules/Admin/app/Services/AbandonedCartService.php`
- Modify: `Modules/Telemetry/config/config.php`
- Test: `tests/Feature/Analytics/AbandonedCartServiceTest.php`

**Interfaces:**
- Consumes: `DateRange` (Task 1); Phase 1 models; `App\Models\User`.
- Produces `Modules\Admin\Services\AbandonedCartService` with
  `abandoned(DateRange $range, ?int $windowHours = null): \Illuminate\Support\Collection` of arrays:
  `['session_id','user_id','user_name','user_email','cart_value','item_count','last_activity_at','age_hours']`. Task 4 consumes it.

- [ ] **Step 1: Add the config key**

In `Modules/Telemetry/config/config.php`, add the window default. The file becomes:

```php
<?php

return [
    'name' => 'Telemetry',
    'abandoned_cart_hours' => env('ABANDONED_CART_HOURS', 24),
];
```

- [ ] **Step 2: Write the failing test**

Create `tests/Feature/Analytics/AbandonedCartServiceTest.php`:

```php
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
```

- [ ] **Step 3: Run test to verify it fails**

Run: `php artisan test --filter=AbandonedCartServiceTest`
Expected: FAIL — `Class "Modules\Admin\Services\AbandonedCartService" not found`.

- [ ] **Step 4: Write the service**

Create `Modules/Admin/app/Services/AbandonedCartService.php`:

```php
<?php

namespace Modules\Admin\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Admin\Support\DateRange;
use Modules\Telemetry\Models\CartEvent;
use Modules\Telemetry\Models\CheckoutEvent;

/**
 * Read-only abandoned-cart report. A session is abandoned when, within the
 * range, it added to cart, never placed an order, and its last cart activity is
 * older than the window. Cart value = net cart state (last-write-wins per line,
 * removes drop the line).
 */
class AbandonedCartService
{
    public function abandoned(DateRange $range, ?int $windowHours = null): Collection
    {
        $windowHours = $windowHours ?? (int) config('telemetry.abandoned_cart_hours', 24);
        $cutoff = now()->subHours($windowHours);

        // Candidate sessions: added to cart within the range.
        $candidates = CartEvent::query()
            ->where('action', 'add')
            ->whereBetween('occurred_at', [$range->from, $range->to])
            ->whereNotNull('session_id')
            ->distinct()
            ->pluck('session_id')
            ->all();

        if (empty($candidates)) {
            return collect();
        }

        // Exclude any session (or its known user) that placed an order.
        $placedSessions = CheckoutEvent::query()
            ->where('step', 'placed')
            ->whereIn('session_id', $candidates)
            ->pluck('session_id')
            ->filter()
            ->all();
        $placedUsers = CheckoutEvent::query()
            ->where('step', 'placed')
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique()
            ->all();

        $rows = collect();

        foreach ($candidates as $sessionId) {
            if (in_array($sessionId, $placedSessions, true)) {
                continue;
            }

            $events = CartEvent::query()
                ->where('session_id', $sessionId)
                ->orderBy('occurred_at')
                ->get(['product_id', 'variant_id', 'action', 'quantity', 'unit_price', 'user_id', 'occurred_at']);

            $userId = $events->pluck('user_id')->filter()->first();
            if ($userId !== null && in_array($userId, $placedUsers, true)) {
                continue;
            }

            $lastActivity = $events->max('occurred_at');
            if ($lastActivity === null || $lastActivity->greaterThan($cutoff)) {
                continue; // still active within the window
            }

            [$value, $items] = $this->netCart($events);
            if ($value <= 0 || $items <= 0) {
                continue; // fully removed cart isn't abandoned
            }

            $user = $userId !== null ? User::find($userId) : null;

            $rows->push([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'user_name' => $user?->name,
                'user_email' => $user?->email,
                'cart_value' => round($value, 2),
                'item_count' => $items,
                'last_activity_at' => $lastActivity,
                'age_hours' => (int) $lastActivity->diffInHours(now()),
            ]);
        }

        return $rows->sortByDesc('cart_value')->values();
    }

    /**
     * Net cart state from a session's ordered cart events. Last-write-wins on
     * quantity per (product, variant); a 'remove' drops the line.
     * Returns [value, itemUnits].
     */
    private function netCart(Collection $events): array
    {
        $state = [];
        foreach ($events as $e) {
            $key = $e->product_id . ':' . ($e->variant_id ?? '0');
            if ($e->action === 'remove') {
                unset($state[$key]);
                continue;
            }
            $state[$key] = ['qty' => (int) $e->quantity, 'price' => (float) ($e->unit_price ?? 0)];
        }

        $value = 0.0;
        $items = 0;
        foreach ($state as $line) {
            if ($line['qty'] > 0) {
                $value += $line['qty'] * $line['price'];
                $items += $line['qty'];
            }
        }
        return [$value, $items];
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=AbandonedCartServiceTest`
Expected: PASS (5 tests).

- [ ] **Step 6: Commit**

```bash
git add Modules/Admin/app/Services/AbandonedCartService.php Modules/Telemetry/config/config.php tests/Feature/Analytics/AbandonedCartServiceTest.php
git commit -m "feat(analytics): AbandonedCartService — net-cart valuation + config window"
```

---

### Task 4: Funnel page (controller + route + Blade + nav)

**Files:**
- Modify: `Modules/Admin/app/Http/Controllers/StatisticsController.php` (add `funnelStatistics`)
- Modify: `Modules/Admin/routes/web.php` (add `statistics/funnel` route, in the `admin.auth` group next to the other `statistics/*` routes, ~line 131)
- Create: `Modules/Admin/resources/views/statistics/funnel.blade.php`
- Test: `tests/Feature/Analytics/FunnelPageTest.php`

**Interfaces:**
- Consumes: `FunnelService`, `AbandonedCartService`, `DateRange`.
- Produces: `GET statistics/funnel` (route name `statistics.funnel`) rendering `admin::statistics.funnel`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Analytics/FunnelPageTest.php`:

```php
<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Tests\TestCase;

class FunnelPageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::create([
            'name' => 'Admin', 'email' => 'admin' . uniqid() . '@t.test',
            'password' => 'x', 'email_verified_at' => now(),
        ]);
        UserAdmin::create(['user_id' => $user->id, 'is_active' => 1]);
        return $user;
    }

    public function test_admin_sees_funnel_page_with_stage_labels(): void
    {
        // The page renders all five stages regardless of data, so no seeding is
        // needed to assert the stage labels are present.
        $this->actingAs($this->admin())
            ->get(route('statistics.funnel'))
            ->assertOk()
            ->assertSee('Product View')
            ->assertSee('Order Placed');
    }

    public function test_abandoned_cart_empty_state_is_shown_when_none(): void
    {
        $this->actingAs($this->admin())
            ->get(route('statistics.funnel'))
            ->assertOk()
            ->assertSee('No abandoned carts');
    }

    public function test_guest_cannot_reach_funnel_page(): void
    {
        $this->get(route('statistics.funnel'))->assertRedirect();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=FunnelPageTest`
Expected: FAIL — route `statistics.funnel` not defined.

- [ ] **Step 3: Add the route**

In `Modules/Admin/routes/web.php`, immediately after the existing
`Route::get('statistics/orders', ...)->name('statistics.orders');` line (~line 131, inside the `admin.auth` group), add:

```php
    Route::get('statistics/funnel', [StatisticsController::class, 'funnelStatistics'])->name('statistics.funnel');
```

- [ ] **Step 4: Add the controller action**

In `Modules/Admin/app/Http/Controllers/StatisticsController.php`, add these imports after the existing `use` block (top of file):

```php
use Modules\Admin\Services\FunnelService;
use Modules\Admin\Services\AbandonedCartService;
use Modules\Admin\Support\DateRange;
```

Then add this method inside the class (e.g. after `productStatistics`):

```php
    public function funnelStatistics(Request $request)
    {
        $range = DateRange::fromRequest($request);
        $funnel = (new FunnelService())->funnel($range);
        $abandoned = (new AbandonedCartService())->abandoned($range);

        return view('admin::statistics.funnel', [
            'funnel' => $funnel,
            'abandoned' => $abandoned,
            'from' => $range->from->toDateString(),
            'to' => $range->to->toDateString(),
        ]);
    }
```

- [ ] **Step 5: Create the Blade view**

Create `Modules/Admin/resources/views/statistics/funnel.blade.php`. Keep it self-contained (no dependency on data other than what the controller passes), matching the `<x-admin-layout>` wrapper the other statistics pages use:

```blade
<x-admin-layout>
    <div class="space-y-8 px-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-1">Conversion Funnel</h1>
            <p class="text-sm text-gray-500">
                Some stages (Sessions, Add to Cart) populate once the mobile/web
                cart &amp; session tracking hooks are live. Server-captured stages
                (Product View, Checkout, Order Placed) are live now.
            </p>

            <form method="GET" class="mt-4 flex flex-wrap items-end gap-3">
                <label class="text-sm">From
                    <input type="date" name="from" value="{{ $from }}" class="block border rounded px-2 py-1" />
                </label>
                <label class="text-sm">To
                    <input type="date" name="to" value="{{ $to }}" class="block border rounded px-2 py-1" />
                </label>
                <button type="submit" class="bg-gray-800 text-white rounded px-4 py-1.5 text-sm">Apply</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Funnel</h2>
                <span class="text-sm text-gray-600">
                    Overall conversion: {{ number_format(($funnel['overall_conversion'] ?? 0) * 100, 2) }}%
                </span>
            </div>

            @php $maxCount = max(1, collect($funnel['stages'])->max('count')); @endphp
            <div class="space-y-3">
                @foreach ($funnel['stages'] as $stage)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium">{{ $stage['label'] }}</span>
                            <span class="text-gray-600">
                                {{ number_format($stage['count']) }}
                                @if (!is_null($stage['conversion_from_prev']))
                                    <span class="text-gray-400">({{ number_format($stage['conversion_from_prev'] * 100, 1) }}%)</span>
                                @endif
                            </span>
                        </div>
                        <div class="w-full bg-gray-100 rounded h-3">
                            <div class="bg-indigo-500 h-3 rounded" style="width: {{ ($stage['count'] / $maxCount) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Abandoned Carts</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4">Customer</th>
                            <th class="py-2 pr-4">Session</th>
                            <th class="py-2 pr-4">Cart value</th>
                            <th class="py-2 pr-4">Items</th>
                            <th class="py-2 pr-4">Age (h)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($abandoned as $row)
                            <tr class="border-b">
                                <td class="py-2 pr-4">{{ $row['user_name'] ?? 'Guest' }}</td>
                                <td class="py-2 pr-4 font-mono text-xs">{{ \Illuminate\Support\Str::limit($row['session_id'], 12) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['cart_value'], 2) }}</td>
                                <td class="py-2 pr-4">{{ $row['item_count'] }}</td>
                                <td class="py-2 pr-4">{{ $row['age_hours'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-gray-400">
                                    No abandoned carts yet — cart tracking (/track/cart) is not wired into the apps yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
```

- [ ] **Step 6: Add a nav link (best-effort)**

Search for where the existing statistics links live in the admin layout/sidebar:

Run: `grep -rn "statistics.orders\|statistics/orders" Modules/Admin/resources/views/`

- If a sidebar/nav partial references the other statistics routes, add a sibling link: `<a href="{{ route('statistics.funnel') }}">Funnel</a>` matching the surrounding markup.
- If no nav references the statistics routes (they may be linked elsewhere), skip the nav edit and note it in your report — the route + page are the deliverable; do not invent a nav structure.

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --filter=FunnelPageTest`
Expected: PASS (3 tests). If the `<x-admin-layout>` component requires view data the test user doesn't provide and the page 500s, report it — do not stub the layout; the fix belongs with whoever owns the layout.

- [ ] **Step 8: Run the full analytics suite + commit**

Run: `php artisan test --filter=Analytics`
Expected: PASS (Tasks 1–4 tests green).

```bash
git add Modules/Admin/app/Http/Controllers/StatisticsController.php Modules/Admin/routes/web.php Modules/Admin/resources/views/statistics/funnel.blade.php tests/Feature/Analytics/FunnelPageTest.php
git commit -m "feat(analytics): admin Funnel page — funnel chart + abandoned carts"
```

---

## Definition of done (Phase 2)

- `php artisan test --filter=Analytics` is green (DateRange, FunnelService, AbandonedCartService, FunnelPage).
- `GET /admin` funnel page renders for an admin with a date-range filter, the five funnel stages, overall conversion, and an abandoned-cart table with an honest empty-state.
- Funnel counts distinct actors (server-captured stages show real numbers now; cart/session stages read 0 until their client hooks land).
- No new tables; no client changes; no CSV (Phase 10).

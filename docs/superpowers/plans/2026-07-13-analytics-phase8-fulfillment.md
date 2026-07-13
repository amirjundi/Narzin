# Analytics Phase 8 — Fulfillment SLA + Cancellation Reasons Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Report fulfillment SLA timing (from existing OrderAudit transitions) and capture + report structured cancellation reasons, as an admin analytics page.

**Architecture:** Read-only `FulfillmentService` computes stage durations by folding `order_audits` rows in PHP (portable, no DB median). One new nullable column `orders.cancellation_reason` captured at the 4 code paths that cancel orders. New Blade `statistics/fulfillment` page + sidebar link. No new table.

**Tech Stack:** Laravel 11 (nwidart modules), `Modules\Admin\Services`, `DateRange` value object, Blade `admin::statistics.*`, PHPUnit.

## Global Constraints

- Portable SQL only: `COALESCE`, `whereIn`, `groupBy`; **medians/p90 computed in PHP**, never a DB median function. No `CONCAT`/string-concat in SQL.
- Money/time folding done with ONE audit query per report call (`whereIn order_id`), never per-order queries (no N+1).
- `cancellation_reason` is a plain nullable string (app-enforced values), mirroring how `order_status` is a plain string — no DB enum.
- Admin cancellation reason values EXACTLY: `out_of_stock`, `customer_request`, `fraud_suspected`, `pricing_error`, `other`. System auto values EXACTLY: `return_refund`, `customer_request`, `payment_failed`.
- The report counts BOTH cancellation spellings `['cancelled','canceled']`; the payment-webhook write is normalized to `'cancelled'` (two L).
- SLA threshold from `config('telemetry.fulfillment_sla_hours', 48)`.
- Analytics services live in `Modules/Admin/app/Services`; sidebar link goes in the app-level `resources/views/components/admin/sidebar.blade.php` (NOT module views).
- Stage durations are in **hours** (float, rounded to 2).

---

### Task 1: Migration + Order model + telemetry config

**Files:**
- Create: `narzinapp-main/Modules/Checkout/database/migrations/2026_07_13_000000_add_cancellation_reason_to_orders.php`
- Modify: `narzinapp-main/Modules/Checkout/app/Models/Order.php` (add to `$fillable`)
- Modify: `narzinapp-main/Modules/Telemetry/config/config.php` (add `fulfillment_sla_hours`)
- Test: `narzinapp-main/tests/Feature/CancellationReasonColumnTest.php`

**Interfaces:**
- Produces: `orders.cancellation_reason` (nullable string, indexed); config key `telemetry.fulfillment_sla_hours` (int, default 48).

- [ ] **Step 1: Write the migration**

Look at an existing Checkout migration (e.g. the attribution one that added `utm_*`) for the exact `Schema::table('orders', ...)` style and `down()`. Then:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Why-cancelled, captured at the 4 paths that set order_status=cancelled.
            // Nullable: historical cancellations stay unlabeled; report tolerates it.
            $table->string('cancellation_reason')->nullable()->after('notes')->index();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['cancellation_reason']);
            $table->dropColumn('cancellation_reason');
        });
    }
};
```

- [ ] **Step 2: Add to Order `$fillable`**

In `Order.php`, add `'cancellation_reason',` to the `$fillable` array (near `'notes'`).

- [ ] **Step 3: Add the config key**

In `Modules/Telemetry/config/config.php`, alongside `abandoned_cart_hours`:

```php
'fulfillment_sla_hours' => env('FULFILLMENT_SLA_HOURS', 48),
```

- [ ] **Step 4: Write the test**

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Checkout\Models\Order;
use Tests\TestCase;

class CancellationReasonColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancellation_reason_is_mass_assignable_and_nullable(): void
    {
        $order = Order::factory()->create(['cancellation_reason' => null]);
        $this->assertNull($order->fresh()->cancellation_reason);

        $order->update(['cancellation_reason' => 'out_of_stock']);
        $this->assertSame('out_of_stock', $order->fresh()->cancellation_reason);
    }
}
```

If `Order::factory()` requires fields that have no defaults, construct the order the way other Checkout tests do (check an existing Checkout/Order test for the minimal factory/create call and mirror it). The assertion that matters: the column round-trips and defaults null.

- [ ] **Step 5: Run migration + test**

Run: `php artisan migrate` then `php artisan test --filter=CancellationReasonColumnTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add narzinapp-main/Modules/Checkout narzinapp-main/Modules/Telemetry narzinapp-main/tests/Feature/CancellationReasonColumnTest.php
git commit -m "feat(analytics): add orders.cancellation_reason + fulfillment SLA config"
```

---

### Task 2: Capture cancellation reasons at the 4 cancel sites

**Files:**
- Modify: `narzinapp-main/Modules/Admin/app/Http/Controllers/OrderController.php` (`updateStatus`)
- Modify: `narzinapp-main/Modules/Admin/resources/views/orders/show.blade.php` (reason `<select>`)
- Modify: `narzinapp-main/Modules/Checkout/app/Services/OrderRefundService.php` (`return_refund`)
- Modify: `narzinapp-main/Modules/Checkout/app/Http/Controllers/V1/Api/CheckoutController.php` (customer-cancel + payment-fail)
- Test: `narzinapp-main/tests/Feature/AdminCancellationReasonTest.php`

**Interfaces:**
- Consumes: `orders.cancellation_reason` from Task 1.
- Produces: cancelled orders now carry a `cancellation_reason` string.

- [ ] **Step 1: Write the failing feature test**

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Checkout\Models\Order;
use Tests\TestCase;

class AdminCancellationReasonTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancelling_via_admin_persists_the_reason(): void
    {
        $order = $this->makeOrder(['order_status' => 'confirmed']);

        $this->actingAsAdmin()
            ->post(route('admin.orders.updateStatus', $order->id), [
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
            ->post(route('admin.orders.updateStatus', $order->id), [
                'order_status' => 'shipped',
                'cancellation_reason' => 'out_of_stock',
            ])->assertRedirect();

        $this->assertNull($order->fresh()->cancellation_reason);
    }
}
```

`makeOrder()` and `actingAsAdmin()`: reuse whatever the existing Admin order/controller feature tests use (find one in `tests/` that hits an `admin.orders.*` route and mirror its setup — the admin auth guard/middleware and order factory helper). The exact route name for the status-update endpoint must match `Modules/Admin/routes/web.php` — verify it (it may be `admin.orders.updateStatus` or similar) and use the real name in `route(...)`.

- [ ] **Step 2: Run it — verify it fails**

Run: `php artisan test --filter=AdminCancellationReasonTest`
Expected: FAIL (reason not persisted yet).

- [ ] **Step 3: Wire admin `updateStatus`**

In `OrderController::updateStatus`, extend validation and persist the reason only when cancelling:

```php
$request->validate([
    'order_status' => 'required|in:pending_payment,confirmed,processing,shipped,delivered,cancelled',
    'notes' => 'nullable|string|max:500',
    'cancellation_reason' => 'nullable|in:out_of_stock,customer_request,fraud_suspected,pricing_error,other',
]);
```

In the `$order->update([...])` call, conditionally include the reason (only when the new status is `cancelled`, else leave the column untouched):

```php
$updates = [
    'order_status' => $request->order_status,
    'notes' => $request->notes ? ($order->notes . ' | Admin: ' . $request->notes) : $order->notes,
];
if ($request->order_status === 'cancelled') {
    $updates['cancellation_reason'] = $request->cancellation_reason;
}
$order->update($updates);
```

Also add `'cancellation_reason' => $request->cancellation_reason` into the `OrderAudit::create([... 'data' => ...])` — the audit `data` json is the traceability record. If the audit `create` here does not currently set `data`, add `'data' => ['cancellation_reason' => $request->cancellation_reason]` when cancelling; otherwise merge into the existing `data`.

- [ ] **Step 4: Add the reason `<select>` to the blade form**

In `Modules/Admin/resources/views/orders/show.blade.php`, inside the status-update `<form>` (right after the `order_status` `<select>`, around line 100), add:

```blade
<select name="cancellation_reason" class="flex-1 min-w-[150px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
    <option value="">Cancellation reason (if cancelling)</option>
    <option value="out_of_stock" {{ $order->cancellation_reason === 'out_of_stock' ? 'selected' : '' }}>Out of stock</option>
    <option value="customer_request" {{ $order->cancellation_reason === 'customer_request' ? 'selected' : '' }}>Customer request</option>
    <option value="fraud_suspected" {{ $order->cancellation_reason === 'fraud_suspected' ? 'selected' : '' }}>Fraud suspected</option>
    <option value="pricing_error" {{ $order->cancellation_reason === 'pricing_error' ? 'selected' : '' }}>Pricing error</option>
    <option value="other" {{ $order->cancellation_reason === 'other' ? 'selected' : '' }}>Other</option>
</select>
```

Match the surrounding markup/indentation of the existing `order_status` select.

- [ ] **Step 5: Wire the 3 system paths**

**a. `OrderRefundService::refundWholeOrder`** — in the `$order->update([...])` that sets `'order_status' => 'cancelled'`, add `'cancellation_reason' => 'return_refund',`.

**b. `CheckoutController` customer self-cancel path** — the block (around line 993) that sets `$order->order_status = 'cancelled'` on user cancel: also set `$order->cancellation_reason = 'customer_request';` before the `save()`/`update()`. (If it uses `$order->order_status = ...; $order->save();`, add the property assignment alongside.)

**c. `CheckoutController` payment-webhook-failure path** — the `$order->update([...])` (around line 855) that sets `'order_status' => 'canceled'`: change `'canceled'` to `'cancelled'` (two L, the spelling fix) and add `'cancellation_reason' => 'payment_failed',`.

- [ ] **Step 6: Run the test — verify it passes**

Run: `php artisan test --filter=AdminCancellationReasonTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add narzinapp-main/Modules/Admin narzinapp-main/Modules/Checkout narzinapp-main/tests/Feature/AdminCancellationReasonTest.php
git commit -m "feat(analytics): capture cancellation_reason at admin + system cancel paths"
```

---

### Task 3: FulfillmentService

**Files:**
- Create: `narzinapp-main/Modules/Admin/app/Services/FulfillmentService.php`
- Test: `narzinapp-main/tests/Unit/FulfillmentServiceTest.php` (or `tests/Feature/` if it needs the DB — it does; use `Feature` or a `RefreshDatabase` unit test consistent with how `ReturnAnalyticsService` is tested)

**Interfaces:**
- Consumes: `Modules\Admin\Support\DateRange`; `order_audits` rows (`order_id, new_order_status, created_at`); `orders` (`id, created_at, order_status, cancellation_reason`).
- Produces:
  - `slaSummary(DateRange $range): array` → `['stages' => ['confirm_to_ship'=>['count','avg_hours','median_hours','p90_hours'], 'ship_to_deliver'=>[...], 'placed_to_ship'=>[...]], 'breach_rate'=>float, 'sla_hours'=>int]`
  - `cancellations(DateRange $range): array` → `['by_reason'=>Collection<['reason','count']>, 'total_cancelled'=>int, 'total_orders'=>int, 'cancellation_rate'=>float]`

- [ ] **Step 1: Write the failing test**

Look at how `ReturnAnalyticsService` is tested (find its test file) and mirror the DB-seeding style (factories or raw `Order::create` + `OrderAudit::create`). Then:

```php
<?php

namespace Tests\Feature; // match ReturnAnalyticsService test's namespace/location

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Services\FulfillmentService;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderAudit;
use Carbon\Carbon;
use Tests\TestCase;

class FulfillmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $attrs = []): Order
    {
        // Mirror ReturnAnalyticsService test / existing Order test seeding.
        return Order::factory()->create($attrs);
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
```

If `Order::factory()` can't set `created_at`/`order_status` directly, set them after create or via the factory's `->state`. The behavior under test is the folding math, not the factory.

- [ ] **Step 2: Run it — verify it fails**

Run: `php artisan test --filter=FulfillmentServiceTest`
Expected: FAIL (class not found).

- [ ] **Step 3: Implement the service**

```php
<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Collection;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderAudit;

/** Read-only fulfillment SLA (from order_audits) + cancellation breakdown. */
class FulfillmentService
{
    public function slaSummary(DateRange $range): array
    {
        // Orders placed in the window, with their placed timestamp.
        $orders = Order::query()
            ->whereBetween('created_at', [$range->from, $range->to])
            ->get(['id', 'created_at'])
            ->keyBy('id');

        if ($orders->isEmpty()) {
            return $this->emptySla();
        }

        // ONE audit query for all these orders; earliest row per (order,new_status).
        $audits = OrderAudit::query()
            ->whereIn('order_id', $orders->keys())
            ->whereIn('new_order_status', ['confirmed', 'shipped', 'delivered'])
            ->orderBy('created_at')
            ->get(['order_id', 'new_order_status', 'created_at']);

        // stamps[order_id][status] = first timestamp seen (Carbon).
        $stamps = [];
        foreach ($audits as $a) {
            $stamps[$a->order_id][$a->new_order_status] ??= $a->created_at;
        }

        $confirmToShip = [];
        $shipToDeliver = [];
        $placedToShip = [];

        foreach ($orders as $id => $order) {
            $s = $stamps[$id] ?? [];
            $confirmed = $s['confirmed'] ?? $order->created_at;
            $shipped = $s['shipped'] ?? null;
            $delivered = $s['delivered'] ?? null;

            if ($shipped) {
                $confirmToShip[] = $this->hours($confirmed, $shipped);
                $placedToShip[] = $this->hours($order->created_at, $shipped);
            }
            if ($shipped && $delivered) {
                $shipToDeliver[] = $this->hours($shipped, $delivered);
            }
        }

        $slaHours = (int) config('telemetry.fulfillment_sla_hours', 48);
        $breaches = array_filter($placedToShip, fn ($h) => $h > $slaHours);
        $breachRate = count($placedToShip) > 0
            ? round(count($breaches) / count($placedToShip), 4)
            : 0.0;

        return [
            'stages' => [
                'confirm_to_ship' => $this->stageStats($confirmToShip),
                'ship_to_deliver' => $this->stageStats($shipToDeliver),
                'placed_to_ship' => $this->stageStats($placedToShip),
            ],
            'breach_rate' => $breachRate,
            'sla_hours' => $slaHours,
        ];
    }

    public function cancellations(DateRange $range): array
    {
        $totalOrders = Order::query()
            ->whereBetween('created_at', [$range->from, $range->to])
            ->count();

        $byReason = Order::query()
            ->whereBetween('created_at', [$range->from, $range->to])
            ->whereIn('order_status', ['cancelled', 'canceled'])
            ->selectRaw("COALESCE(cancellation_reason, '(unspecified)') as reason, COUNT(*) as c")
            ->groupBy('reason')
            ->orderByDesc('c')
            ->get()
            ->map(fn ($r) => ['reason' => $r->reason, 'count' => (int) $r->c]);

        $totalCancelled = (int) $byReason->sum('count');

        return [
            'by_reason' => $byReason,
            'total_cancelled' => $totalCancelled,
            'total_orders' => $totalOrders,
            'cancellation_rate' => $totalOrders > 0
                ? round($totalCancelled / $totalOrders, 4)
                : 0.0,
        ];
    }

    /** Duration in hours between two datetimes (float, 2dp). */
    private function hours($from, $to): float
    {
        // diffInSeconds is order-independent (abs) on older Carbon; these are always from<=to.
        return round($from->diffInSeconds($to) / 3600, 2);
    }

    /** avg/median/p90 over a list of hour-durations, all computed in PHP. */
    private function stageStats(array $vals): array
    {
        $n = count($vals);
        if ($n === 0) {
            return ['count' => 0, 'avg_hours' => 0.0, 'median_hours' => 0.0, 'p90_hours' => 0.0];
        }
        sort($vals);
        return [
            'count' => $n,
            'avg_hours' => round(array_sum($vals) / $n, 2),
            'median_hours' => $this->percentile($vals, 0.5),
            'p90_hours' => $this->percentile($vals, 0.9),
        ];
    }

    /** Nearest-rank percentile on a pre-sorted array. */
    private function percentile(array $sorted, float $p): float
    {
        $n = count($sorted);
        $idx = (int) ceil($p * $n) - 1;
        $idx = max(0, min($idx, $n - 1));
        return round($sorted[$idx], 2);
    }

    private function emptySla(): array
    {
        $empty = ['count' => 0, 'avg_hours' => 0.0, 'median_hours' => 0.0, 'p90_hours' => 0.0];
        return [
            'stages' => [
                'confirm_to_ship' => $empty,
                'ship_to_deliver' => $empty,
                'placed_to_ship' => $empty,
            ],
            'breach_rate' => 0.0,
            'sla_hours' => (int) config('telemetry.fulfillment_sla_hours', 48),
        ];
    }
}
```

Note on `diffInSeconds`: pass the arguments so the elapsed value is positive (from ≤ to in all our stages). If the installed Carbon returns a signed value, the durations are still correct because we always call `$earlier->diffInSeconds($later)`.

- [ ] **Step 4: Run the test — verify it passes**

Run: `php artisan test --filter=FulfillmentServiceTest`
Expected: PASS. If the p90 assertion is off, verify the nearest-rank formula against the test data (for 2 values, p90 index = ceil(0.9*2)-1 = 1 → the larger value).

- [ ] **Step 5: Commit**

```bash
git add narzinapp-main/Modules/Admin/app/Services/FulfillmentService.php narzinapp-main/tests
git commit -m "feat(analytics): FulfillmentService — SLA timing from audits + cancellation breakdown"
```

---

### Task 4: Admin page — controller, route, blade, sidebar

**Files:**
- Modify: `narzinapp-main/Modules/Admin/app/Http/Controllers/StatisticsController.php` (add `fulfillmentStatistics`)
- Modify: `narzinapp-main/Modules/Admin/routes/web.php` (add route)
- Create: `narzinapp-main/Modules/Admin/resources/views/statistics/fulfillment.blade.php`
- Modify: `narzinapp-main/resources/views/components/admin/sidebar.blade.php` (add link)

**Interfaces:**
- Consumes: `FulfillmentService::slaSummary` + `cancellations` from Task 3; `DateRange::fromRequest`.

- [ ] **Step 1: Add the controller method**

In `StatisticsController` (mirror `funnelStatistics`/`profitStatistics`), add:

```php
public function fulfillmentStatistics(Request $request)
{
    $range = DateRange::fromRequest($request);
    $service = new FulfillmentService();

    return view('admin::statistics.fulfillment', [
        'sla' => $service->slaSummary($range),
        'cancellations' => $service->cancellations($range),
        'from' => $range->from->toDateString(),
        'to' => $range->to->toDateString(),
    ]);
}
```

Add `use Modules\Admin\Services\FulfillmentService;` at the top with the other service imports.

- [ ] **Step 2: Add the route**

In `Modules/Admin/routes/web.php`, next to `statistics/returns`:

```php
Route::get('statistics/fulfillment', [StatisticsController::class, 'fulfillmentStatistics'])->name('statistics.fulfillment');
```

Match the surrounding group/middleware (it must sit inside the same admin-auth group as `statistics.returns`).

- [ ] **Step 3: Create the blade page**

Model it on `Modules/Admin/resources/views/statistics/returns.blade.php` (same layout wrapper, date-range form, card + table markup). Read that file first and reuse its structure. The page shows:
- The date-range filter form (copy from returns.blade.php — same `from`/`to` inputs).
- Three SLA stage cards (`confirm_to_ship`, `ship_to_deliver`, `placed_to_ship`), each showing count, avg / median / p90 hours.
- A "SLA breach rate" figure with the `sla['sla_hours']` threshold and `sla['breach_rate']` (render as a percentage).
- A cancellations table: `cancellation_rate` headline (`total_cancelled` / `total_orders`), then rows from `cancellations['by_reason']` (`reason` → humanized, `count`).
- A short caveat line: "SLA measured over orders placed in the window; orders not yet shipped don't contribute a shipping time. Historical cancellations before this release show as (unspecified)."

Render hours to 1–2 decimals and rates as `{{ round($rate * 100, 1) }}%`. Humanize reason with `ucfirst(str_replace('_',' ',$reason))`.

- [ ] **Step 4: Add the sidebar link**

In `resources/views/components/admin/sidebar.blade.php`, copy the `statistics.returns` (or `statistics.payments`) link block and repoint it to `statistics.fulfillment` with label "Fulfillment" — matching the exact active-state markup pattern of the neighbouring links (`request()->routeIs('statistics.fulfillment')`).

- [ ] **Step 5: Verify the page renders**

Run: `php artisan route:list --name=statistics.fulfillment` (confirm the route is registered).
Then, if a smoke test harness exists for other statistics pages, mirror it; otherwise verify by loading the page manually is out of scope for the subagent — a route-list confirmation + `php artisan view:clear` with no blade compile error is sufficient. To force a compile check:

Run: `php artisan view:cache` (compiles all blades; fails loudly on a syntax error), then `php artisan view:clear`.
Expected: no error mentioning `statistics/fulfillment`.

- [ ] **Step 6: Commit**

```bash
git add narzinapp-main/Modules/Admin narzinapp-main/resources/views/components/admin/sidebar.blade.php
git commit -m "feat(analytics): admin fulfillment SLA + cancellations page"
```

---

## Notes for the final whole-branch review

- Verify the payment-webhook spelling fix (`'canceled'`→`'cancelled'`) didn't break any code that queried the one-L value elsewhere (grep `'canceled'` across the repo).
- Verify no capture write throws inside a path that must not fail (all four piggyback existing writes — confirm none moved a write outside its transaction).
- Confirm `slaSummary` issues exactly two queries (orders + audits), not N+1.
- Confirm medians/p90 are PHP-side (no DB median).

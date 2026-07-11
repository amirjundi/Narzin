# Analytics Phase 4 — Coupon / Promotion Performance Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Report coupon/promotion redemptions, discount given, placed value, and AOV over a date range on a new admin Promotions page — no new table, over existing orders.

**Architecture:** `DiscountService` aggregates `orders` (joined to `coupons`/`promotions`) by coupon and by promotion, plus a penetration summary. A new `promotionStatistics` controller action renders a new `admin::statistics.promotions` Blade page. Reuses the Phase 2 `DateRange`.

**Tech Stack:** Laravel 11, nwidart modules, PHPUnit 11, SQLite test DB with `RefreshDatabase`. Admin routes behind `admin.auth`; tests authenticate with `actingAs($user)` where `$user` has a `Modules\Admin\Models\UserAdmin` row.

## Global Constraints

- No new table, no checkout change — read-only over existing `orders`. (from spec)
- Discount per order = `total_amount − price_after_discount` (exact; `discount_breakdown` is dead — ignore). (from spec)
- "Placed value" = `SUM(orders.total_amount)` — gross, same basis as existing stats + attribution; money rounded to 2. (from spec)
- Each order has at most one of `coupon_id` / `promotion_id` (best-one-wins). (from spec)
- Missing joined name → `'(deleted)'` (coalesce in PHP). NOTE: `orders.coupon_id` has an FK with `onDelete('set null')`, so a dangling coupon_id is impossible — the coupon `(deleted)` path is defensive/unreachable (keep the `?? '(deleted)'` anyway, harmless). `orders.promotion_id` has NO FK, so a dangling promotion_id IS possible — that `(deleted)` path is real and tested. (corrected from FK inspection)
- Range-bound on `orders.created_at`; reuse `Modules\Admin\Support\DateRange`. Rows sorted by `discount_given` desc. (from spec)
- Run commands from `C:\xampp\htdocs\Narzin\narzinapp-main`.

---

### Task 1: DiscountService

**Files:**
- Create: `Modules/Admin/app/Services/DiscountService.php`
- Test: `tests/Feature/Analytics/DiscountServiceTest.php`

**Interfaces:**
- Consumes: `DateRange`, `Modules\Checkout\Models\{Order,Coupon,Promotion}`.
- Produces `Modules\Admin\Services\DiscountService` with:
  - `byCoupon(DateRange): Collection` rows `['code','coupon_id','redemptions','discount_given','placed_value','aov']`
  - `byPromotion(DateRange): Collection` rows `['name','promotion_id','redemptions','discount_given','placed_value','aov']`
  - `summary(DateRange): array` `['discounted_orders','total_orders','discount_rate','total_discount']`
  Task 2 consumes all three.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Analytics/DiscountServiceTest.php`:

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DiscountServiceTest`
Expected: FAIL — `Class "Modules\Admin\Services\DiscountService" not found`.

- [ ] **Step 3: Write the service**

Create `Modules/Admin/app/Services/DiscountService.php`:

```php
<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Collection;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;

/**
 * Read-only coupon/promotion performance over existing orders.
 * Discount per order = total_amount - price_after_discount (exact).
 * No new table; groups on the order's own coupon_id/promotion_id.
 */
class DiscountService
{
    public function byCoupon(DateRange $range): Collection
    {
        return Order::query()
            ->whereBetween('orders.created_at', [$range->from, $range->to])
            ->whereNotNull('coupon_id')
            ->leftJoin('coupons', 'orders.coupon_id', '=', 'coupons.id')
            ->groupBy('orders.coupon_id', 'coupons.code')
            ->selectRaw('orders.coupon_id as coupon_id')
            ->selectRaw('coupons.code as code')
            ->selectRaw('COUNT(*) as redemptions')
            ->selectRaw('SUM(orders.total_amount - orders.price_after_discount) as discount_given')
            ->selectRaw('SUM(orders.total_amount) as placed_value')
            ->get()
            ->map(fn ($r) => $this->row([
                'code' => $r->code ?? '(deleted)',
                'coupon_id' => (int) $r->coupon_id,
            ], $r))
            ->sortByDesc('discount_given')->values();
    }

    public function byPromotion(DateRange $range): Collection
    {
        return Order::query()
            ->whereBetween('orders.created_at', [$range->from, $range->to])
            ->whereNotNull('promotion_id')
            ->leftJoin('promotions', 'orders.promotion_id', '=', 'promotions.id')
            ->groupBy('orders.promotion_id', 'promotions.name')
            ->selectRaw('orders.promotion_id as promotion_id')
            ->selectRaw('promotions.name as name')
            ->selectRaw('COUNT(*) as redemptions')
            ->selectRaw('SUM(orders.total_amount - orders.price_after_discount) as discount_given')
            ->selectRaw('SUM(orders.total_amount) as placed_value')
            ->get()
            ->map(fn ($r) => $this->row([
                'name' => $r->name ?? '(deleted)',
                'promotion_id' => (int) $r->promotion_id,
            ], $r))
            ->sortByDesc('discount_given')->values();
    }

    public function summary(DateRange $range): array
    {
        $orders = Order::query()->whereBetween('created_at', [$range->from, $range->to]);
        $total = (clone $orders)->count();
        $discounted = (clone $orders)
            ->where(fn ($q) => $q->whereNotNull('coupon_id')->orWhereNotNull('promotion_id'))
            ->count();
        $totalDiscount = (clone $orders)
            ->where(fn ($q) => $q->whereNotNull('coupon_id')->orWhereNotNull('promotion_id'))
            ->sum(\DB::raw('total_amount - price_after_discount'));

        return [
            'discounted_orders' => $discounted,
            'total_orders' => $total,
            'discount_rate' => $total > 0 ? round($discounted / $total, 4) : 0.0,
            'total_discount' => round((float) $totalDiscount, 2),
        ];
    }

    private function row(array $keys, $r): array
    {
        $redemptions = (int) $r->redemptions;
        $discount = round((float) $r->discount_given, 2);
        $placed = round((float) $r->placed_value, 2);
        return $keys + [
            'redemptions' => $redemptions,
            'discount_given' => $discount,
            'placed_value' => $placed,
            'aov' => $redemptions > 0 ? round($placed / $redemptions, 2) : 0.0,
        ];
    }
}
```

> Note: `summary()` uses `\DB::raw`. Add `use Illuminate\Support\Facades\DB;` and call `DB::raw(...)`, OR use the fully-qualified `\Illuminate\Support\Facades\DB::raw(...)` inline. Pick one and make it consistent.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=DiscountServiceTest`
Expected: PASS (6 tests). If a NOT NULL orders column beyond `address_id` blocks `Order::create`, add it to the `order()` helper mirroring `tests/Feature/Analytics/OrderAttributionColumnsTest.php`.

- [ ] **Step 5: Commit**

```bash
git add Modules/Admin/app/Services/DiscountService.php tests/Feature/Analytics/DiscountServiceTest.php
git commit -m "feat(analytics): DiscountService — coupon/promotion performance over orders"
```

---

### Task 2: Promotions page (controller + route + Blade)

**Files:**
- Modify: `Modules/Admin/app/Http/Controllers/StatisticsController.php` (add `promotionStatistics` + import)
- Modify: `Modules/Admin/routes/web.php` (add `statistics/promotions` route in the `admin.auth` group, ~line 132)
- Create: `Modules/Admin/resources/views/statistics/promotions.blade.php`
- Test: `tests/Feature/Analytics/PromotionsPageTest.php`

**Interfaces:**
- Consumes: `DiscountService`, `DateRange`.
- Produces: `GET statistics/promotions` (route name `statistics.promotions`) rendering `admin::statistics.promotions`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Analytics/PromotionsPageTest.php`:

```php
<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Tests\TestCase;

class PromotionsPageTest extends TestCase
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

    public function test_admin_sees_promotions_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('statistics.promotions'))
            ->assertOk()
            ->assertSee('Coupons')
            ->assertSee('Promotions');
    }

    public function test_guest_cannot_reach_promotions_page(): void
    {
        $this->get(route('statistics.promotions'))->assertRedirect();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PromotionsPageTest`
Expected: FAIL — route `statistics.promotions` not defined.

- [ ] **Step 3: Add the route**

In `Modules/Admin/routes/web.php`, immediately after the `statistics/funnel` route (~line 132, inside the `admin.auth` group), add:

```php
    Route::get('statistics/promotions', [StatisticsController::class, 'promotionStatistics'])->name('statistics.promotions');
```

- [ ] **Step 4: Add the controller action**

In `Modules/Admin/app/Http/Controllers/StatisticsController.php`, add the import near the other service imports:

```php
use Modules\Admin\Services\DiscountService;
```

Then add the method (e.g. after `funnelStatistics`):

```php
    public function promotionStatistics(Request $request)
    {
        $range = DateRange::fromRequest($request);
        $service = new DiscountService();

        return view('admin::statistics.promotions', [
            'coupons' => $service->byCoupon($range),
            'promotions' => $service->byPromotion($range),
            'summary' => $service->summary($range),
            'from' => $range->from->toDateString(),
            'to' => $range->to->toDateString(),
        ]);
    }
```

- [ ] **Step 5: Create the Blade view**

Create `Modules/Admin/resources/views/statistics/promotions.blade.php`:

```blade
<x-admin-layout>
    <div class="space-y-8 px-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-1">Coupons &amp; Promotions</h1>
            <p class="text-sm text-gray-500">
                “Placed value” is gross placed-order value (incl. unpaid/cancelled,
                same basis as the order stats), not settled revenue.
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
            <h2 class="text-lg font-semibold mb-4">Discount Penetration</h2>
            <div class="flex flex-wrap gap-8 text-sm">
                <div>
                    <div class="text-gray-500">Discounted orders</div>
                    <div class="text-2xl font-bold">{{ number_format($summary['discounted_orders']) }} / {{ number_format($summary['total_orders']) }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Discount rate</div>
                    <div class="text-2xl font-bold">{{ number_format($summary['discount_rate'] * 100, 1) }}%</div>
                </div>
                <div>
                    <div class="text-gray-500">Total discount given</div>
                    <div class="text-2xl font-bold">{{ number_format($summary['total_discount'], 2) }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Coupons</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4">Code</th>
                            <th class="py-2 pr-4">Redemptions</th>
                            <th class="py-2 pr-4">Discount given</th>
                            <th class="py-2 pr-4">Placed value</th>
                            <th class="py-2 pr-4">AOV</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($coupons as $row)
                            <tr class="border-b">
                                <td class="py-2 pr-4">{{ $row['code'] }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['redemptions']) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['discount_given'], 2) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['placed_value'], 2) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['aov'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-center text-gray-400">No coupon redemptions in this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Promotions</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4">Name</th>
                            <th class="py-2 pr-4">Redemptions</th>
                            <th class="py-2 pr-4">Discount given</th>
                            <th class="py-2 pr-4">Placed value</th>
                            <th class="py-2 pr-4">AOV</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($promotions as $row)
                            <tr class="border-b">
                                <td class="py-2 pr-4">{{ $row['name'] }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['redemptions']) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['discount_given'], 2) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['placed_value'], 2) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['aov'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-center text-gray-400">No promotion redemptions in this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=PromotionsPageTest`
Expected: PASS (2 tests). If `<x-admin-layout>` 500s for the test admin, report it — do not stub the layout (the Funnel page test already renders it fine).

- [ ] **Step 7: Run the full analytics suite + commit**

Run: `php artisan test --filter=Analytics`
Expected: PASS (all analytics tests green).

```bash
git add Modules/Admin/app/Http/Controllers/StatisticsController.php Modules/Admin/routes/web.php Modules/Admin/resources/views/statistics/promotions.blade.php tests/Feature/Analytics/PromotionsPageTest.php
git commit -m "feat(analytics): admin Promotions page — coupon + promotion performance"
```

---

## Definition of done (Phase 4)

- `php artisan test --filter=Analytics` green.
- `GET statistics/promotions` renders for an admin: penetration summary + coupon table + promotion table, date-range filter, honest empty-states, `(deleted)` for removed coupons/promotions.
- No new table, no checkout change; discount = `total_amount − price_after_discount`; "Placed value" gross basis consistent with attribution.

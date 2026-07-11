# Analytics Phase 5 — Platform Profit Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Report platform profit (revenue − vendor earnings) for placed and paid orders, plus total owed to vendors, on a new admin Profit page — over stored data, no migration.

**Architecture:** `ProfitService` aggregates `orders` and `order_items` (already carrying `vendor_earning`) over a `DateRange` for two sets (placed = all, paid = `payment_status='completed'`). A new `profitStatistics` controller action renders `admin::statistics.profit`; a sidebar link is added. Reuses `DateRange`.

**Tech Stack:** Laravel 11, nwidart modules, PHPUnit 11, SQLite test DB with `RefreshDatabase`. Admin routes behind `admin.auth`; tests authenticate with `actingAs($user)` where `$user` has a `Modules\Admin\Models\UserAdmin` row.

## Global Constraints

- No migration, no capture change — read-only over stored `orders`/`order_items`/`vendor_transactions`. (from spec)
- revenue = `SUM(COALESCE(orders.price_after_discount, orders.total_amount))`; vendor_earnings = `SUM(COALESCE(order_items.vendor_earning, 0))`; platform_profit = revenue − vendor_earnings; margin = profit/revenue (guard 0). Money rounded 2, margin rounded 4. (from spec)
- **Compute revenue (from orders) and vendor_earnings (from order_items) as SEPARATE queries** — never one orders⋈order_items join summing both, or revenue fan-outs by item count. (correctness)
- placed set = all orders in range; paid set = orders in range with `payment_status = 'completed'`. (from spec — verified value)
- total_owed_to_vendors = `SUM(vendor_transactions.amount)` (all vendors, all-time balance; NOT range-bound). (from spec)
- Run commands from `C:\xampp\htdocs\Narzin\narzinapp-main`.
- Test fixtures: `order_items.{product_id,product_variant_id,vendor_id}` are enforced non-nullable FKs and `order_items.final_price` is NOT NULL; `vendor_transactions.vendor_id` is an enforced FK. The test's `catalog()`/`vendor()` helpers seed real vendor/category/product/variant rows — use them, don't pass literal ids. (corrected after a BLOCKED attempt)

---

### Task 1: ProfitService

**Files:**
- Create: `Modules/Admin/app/Services/ProfitService.php`
- Test: `tests/Feature/Analytics/ProfitServiceTest.php`

**Interfaces:**
- Consumes: `DateRange`, `Modules\Checkout\Models\{Order,OrderItem}`, `Modules\Vendor\Models\VendorTransaction`.
- Produces `Modules\Admin\Services\ProfitService` with `summary(DateRange): array` shaped:
  `['placed'=>['revenue','vendor_earnings','platform_profit','margin','orders'], 'paid'=>[...same...], 'commission_collected'=>float, 'total_owed_to_vendors'=>float]`. Task 2 consumes it.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Analytics/ProfitServiceTest.php`:

```php
<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\ProfitService;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\Vendor\Models\VendorTransaction;
use Tests\TestCase;

class ProfitServiceTest extends TestCase
{
    use RefreshDatabase;

    private function range(): DateRange
    {
        return new DateRange(now()->subDays(30)->startOfDay(), now()->endOfDay());
    }

    // orders.address_id is a NOT NULL FK — seed a user_address (mirrors OrderAttributionColumnsTest).
    private function order(array $attrs = []): Order
    {
        $user = User::factory()->create();
        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id, 'address' => '1 St', 'created_at' => now(), 'updated_at' => now(),
        ]);
        return Order::create(array_merge([
            'user_id' => $user->id,
            'address_id' => $addressId,
            'order_number' => 'T-' . uniqid(),
            'order_status' => 'pending',
            'payment_status' => 'completed',
            'total_amount' => 100.00,
            'price_after_discount' => 100.00,
        ], $attrs));
    }

    private array $catalog = [];

    // order_items.{product_id,product_variant_id,vendor_id} are enforced,
    // non-nullable FKs; order_items.final_price is NOT NULL. Seed one
    // vendor+category+product+variant once and reuse (mirrors PlaceOrderTest).
    private function catalog(): array
    {
        if ($this->catalog) return $this->catalog;
        $vendorId = DB::table('vendors')->insertGetId([
            'store_name_in_arabic' => 'متجر', 'store_name_in_german' => 'Laden',
            'user_id' => User::factory()->create()->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'name_arabic' => 'فئة', 'name_german' => 'Kat',
            'slug_arabic' => 'c-' . uniqid(), 'slug_german' => 'c-' . uniqid(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $productId = DB::table('products')->insertGetId([
            'name_arabic' => 'م', 'name_german' => 'P',
            'slug_arabic' => 'p-' . uniqid(), 'slug_german' => 'p-' . uniqid(),
            'category_id' => $categoryId, 'vendor_id' => $vendorId,
            'is_active' => true, 'weight' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $variantId = DB::table('product_variants')->insertGetId([
            'product_id' => $productId, 'price' => 100, 'stock' => 10,
            'sku' => 'SKU-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return $this->catalog = ['vendor_id' => $vendorId, 'product_id' => $productId, 'variant_id' => $variantId];
    }

    private function vendor(): int
    {
        return DB::table('vendors')->insertGetId([
            'store_name_in_arabic' => 'م', 'store_name_in_german' => 'L',
            'user_id' => User::factory()->create()->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function item(Order $order, float $vendorEarning, float $commission = 0): void
    {
        $c = $this->catalog();
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $c['product_id'],
            'product_variant_id' => $c['variant_id'], 'quantity' => 1,
            'vendor_id' => $c['vendor_id'], 'unit_price' => 100, 'subtotal' => 100,
            'final_price' => 100,
            'vendor_earning' => $vendorEarning, 'vendor_commission_amount' => $commission,
        ]);
    }

    public function test_placed_profit_is_revenue_minus_vendor_earnings(): void
    {
        // paid order: revenue 100, vendor earning 70 → profit 30
        $o1 = $this->order(['price_after_discount' => 100, 'payment_status' => 'completed']);
        $this->item($o1, vendorEarning: 70, commission: 10);
        // unpaid order: revenue 200, vendor earning 150 → in PLACED only
        $o2 = $this->order(['price_after_discount' => 200, 'payment_status' => 'not_paid']);
        $this->item($o2, vendorEarning: 150, commission: 20);

        $s = (new ProfitService())->summary($this->range());

        // placed = both orders: revenue 300, earnings 220, profit 80
        $this->assertEquals(300.00, $s['placed']['revenue']);
        $this->assertEquals(220.00, $s['placed']['vendor_earnings']);
        $this->assertEquals(80.00, $s['placed']['platform_profit']);
        $this->assertSame(2, $s['placed']['orders']);

        // paid = only o1: revenue 100, earnings 70, profit 30
        $this->assertEquals(100.00, $s['paid']['revenue']);
        $this->assertEquals(70.00, $s['paid']['vendor_earnings']);
        $this->assertEquals(30.00, $s['paid']['platform_profit']);
        $this->assertSame(1, $s['paid']['orders']);
        $this->assertEquals(0.3, $s['paid']['margin']); // 30/100
    }

    public function test_null_vendor_earning_coalesces_to_zero(): void
    {
        $o = $this->order(['price_after_discount' => 100, 'payment_status' => 'completed']);
        $this->item($o, vendorEarning: 0); // simulate pre-feature: set null explicitly
        OrderItem::where('order_id', $o->id)->update(['vendor_earning' => null]);

        $s = (new ProfitService())->summary($this->range());
        $this->assertEquals(100.00, $s['paid']['revenue']);
        $this->assertEquals(0.00, $s['paid']['vendor_earnings']); // null → 0
        $this->assertEquals(100.00, $s['paid']['platform_profit']);
    }

    public function test_total_owed_sums_vendor_transactions(): void
    {
        // vendor_transactions.vendor_id is an enforced FK — seed real vendors.
        $v1 = $this->vendor();
        $v2 = $this->vendor();
        VendorTransaction::create(['vendor_id' => $v1, 'type' => 'earning', 'amount' => 70]);
        VendorTransaction::create(['vendor_id' => $v1, 'type' => 'payout', 'amount' => -20]);
        VendorTransaction::create(['vendor_id' => $v2, 'type' => 'earning', 'amount' => 50]);

        $s = (new ProfitService())->summary($this->range());
        $this->assertEquals(100.00, $s['total_owed_to_vendors']); // 70 - 20 + 50
    }

    public function test_no_orders_no_divide_by_zero(): void
    {
        $s = (new ProfitService())->summary($this->range());
        $this->assertEquals(0.0, $s['paid']['margin']);
        $this->assertEquals(0.0, $s['paid']['platform_profit']);
        $this->assertSame(0, $s['paid']['orders']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProfitServiceTest`
Expected: FAIL — `Class "Modules\Admin\Services\ProfitService" not found`.

- [ ] **Step 3: Write the service**

Create `Modules/Admin/app/Services/ProfitService.php`:

```php
<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Facades\DB;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\Vendor\Models\VendorTransaction;

/**
 * Read-only platform profit = product revenue - vendor earnings, over stored
 * order/order_item data. Revenue (from orders) and vendor_earnings (from
 * order_items) are summed in SEPARATE queries so a per-order revenue is never
 * multiplied by item count via a join. No migration; no capture change.
 *
 * Caveat: orders before the vendor-earning system (2026-06-28) have
 * vendor_earning NULL; COALESCE(...,0) treats vendor cost as 0, overstating
 * profit for those orders. Default range (last 30 days) is unaffected.
 */
class ProfitService
{
    public function summary(DateRange $range): array
    {
        return [
            'placed' => $this->computeSet($range, paidOnly: false),
            'paid' => $this->computeSet($range, paidOnly: true),
            'commission_collected' => round((float) $this->itemQuery($range, true)
                ->sum(DB::raw('COALESCE(order_items.vendor_commission_amount, 0)')), 2),
            'total_owed_to_vendors' => round((float) VendorTransaction::sum('amount'), 2),
        ];
    }

    private function computeSet(DateRange $range, bool $paidOnly): array
    {
        $orders = Order::query()->whereBetween('created_at', [$range->from, $range->to]);
        if ($paidOnly) {
            $orders->where('payment_status', 'completed');
        }

        $revenue = round((float) $orders->sum(DB::raw('COALESCE(price_after_discount, total_amount)')), 2);
        $count = (clone $orders)->count();
        $vendorEarnings = round((float) $this->itemQuery($range, $paidOnly)
            ->sum(DB::raw('COALESCE(order_items.vendor_earning, 0)')), 2);
        $profit = round($revenue - $vendorEarnings, 2);

        return [
            'revenue' => $revenue,
            'vendor_earnings' => $vendorEarnings,
            'platform_profit' => $profit,
            'margin' => $revenue > 0 ? round($profit / $revenue, 4) : 0.0,
            'orders' => $count,
        ];
    }

    /** order_items joined to their order, range-bound, optionally paid-only. */
    private function itemQuery(DateRange $range, bool $paidOnly)
    {
        $q = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$range->from, $range->to]);
        if ($paidOnly) {
            $q->where('orders.payment_status', 'completed');
        }
        return $q;
    }
}
```

> Note: `sum()` on a fresh builder each call is fine, but `computeSet` calls `$orders->sum(...)` then `(clone $orders)->count()` — sum must run before count consumes nothing (Eloquent aggregates don't mutate the builder, but clone for count is the safe idiom shown). Keep the clone.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ProfitServiceTest`
Expected: PASS (4 tests). If `Order::create`/`OrderItem::create` hits a NOT NULL column beyond the fixture, add it mirroring `tests/Feature/Analytics/OrderAttributionColumnsTest.php` — do not weaken assertions.

- [ ] **Step 5: Commit**

```bash
git add Modules/Admin/app/Services/ProfitService.php tests/Feature/Analytics/ProfitServiceTest.php
git commit -m "feat(analytics): ProfitService — platform profit (placed & paid) + vendor payable balance"
```

---

### Task 2: Profit page (controller + route + Blade + sidebar link)

**Files:**
- Modify: `Modules/Admin/app/Http/Controllers/StatisticsController.php` (add `profitStatistics` + import)
- Modify: `Modules/Admin/routes/web.php` (add `statistics/profit` route in the `admin.auth` group, next to the other `statistics/*` routes)
- Create: `Modules/Admin/resources/views/statistics/profit.blade.php`
- Modify: `resources/views/components/admin/sidebar.blade.php` (add a Profit link next to the other statistics links)
- Test: `tests/Feature/Analytics/ProfitPageTest.php`

**Interfaces:**
- Consumes: `ProfitService`, `DateRange`.
- Produces: `GET statistics/profit` (route name `statistics.profit`) rendering `admin::statistics.profit`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Analytics/ProfitPageTest.php`:

```php
<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Tests\TestCase;

class ProfitPageTest extends TestCase
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

    public function test_admin_sees_profit_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('statistics.profit'))
            ->assertOk()
            ->assertSee('Platform Profit')
            ->assertSee('owed to vendors', false);
    }

    public function test_guest_cannot_reach_profit_page(): void
    {
        $this->get(route('statistics.profit'))->assertRedirect();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProfitPageTest`
Expected: FAIL — route `statistics.profit` not defined.

- [ ] **Step 3: Add the route**

In `Modules/Admin/routes/web.php`, immediately after the `statistics/promotions` route (inside the `admin.auth` group), add:

```php
    Route::get('statistics/profit', [StatisticsController::class, 'profitStatistics'])->name('statistics.profit');
```

- [ ] **Step 4: Add the controller action**

In `Modules/Admin/app/Http/Controllers/StatisticsController.php`, add the import near the other service imports:

```php
use Modules\Admin\Services\ProfitService;
```

Then add the method (after `promotionStatistics`):

```php
    public function profitStatistics(Request $request)
    {
        $range = DateRange::fromRequest($request);

        return view('admin::statistics.profit', [
            'profit' => (new ProfitService())->summary($range),
            'from' => $range->from->toDateString(),
            'to' => $range->to->toDateString(),
        ]);
    }
```

- [ ] **Step 5: Create the Blade view**

Create `Modules/Admin/resources/views/statistics/profit.blade.php`:

```blade
<x-admin-layout>
    <div class="space-y-8 px-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-1">Platform Profit</h1>
            <p class="text-sm text-gray-500">
                Platform profit = product revenue (after discount) minus what we
                owe vendors. Orders before the vendor-earning system (Jun 2026)
                lack recorded vendor earnings and will overstate profit for
                historical ranges.
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

        <div class="grid gap-6 md:grid-cols-3">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Platform profit (paid)</div>
                <div class="text-3xl font-bold">{{ number_format($profit['paid']['platform_profit'], 2) }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ number_format($profit['paid']['margin'] * 100, 1) }}% margin</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Commission collected (paid)</div>
                <div class="text-3xl font-bold">{{ number_format($profit['commission_collected'], 2) }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Total owed to vendors</div>
                <div class="text-3xl font-bold">{{ number_format($profit['total_owed_to_vendors'], 2) }}</div>
                <a href="{{ route('vendor-payouts.index') }}" class="text-xs text-indigo-600 hover:underline mt-1 inline-block">View vendor payouts →</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Placed vs Paid</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4">Basis</th>
                            <th class="py-2 pr-4">Orders</th>
                            <th class="py-2 pr-4">Revenue</th>
                            <th class="py-2 pr-4">Vendor earnings</th>
                            <th class="py-2 pr-4">Platform profit</th>
                            <th class="py-2 pr-4">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (['placed' => 'Placed (all orders)', 'paid' => 'Paid (completed)'] as $key => $label)
                            <tr class="border-b">
                                <td class="py-2 pr-4 font-medium">{{ $label }}</td>
                                <td class="py-2 pr-4">{{ number_format($profit[$key]['orders']) }}</td>
                                <td class="py-2 pr-4">{{ number_format($profit[$key]['revenue'], 2) }}</td>
                                <td class="py-2 pr-4">{{ number_format($profit[$key]['vendor_earnings'], 2) }}</td>
                                <td class="py-2 pr-4 font-semibold">{{ number_format($profit[$key]['platform_profit'], 2) }}</td>
                                <td class="py-2 pr-4">{{ number_format($profit[$key]['margin'] * 100, 1) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
```

- [ ] **Step 6: Add the sidebar link**

In `resources/views/components/admin/sidebar.blade.php`, find the "Coupons & Promotions Stats" `<li>` block (added in the prior follow-up) and insert a Profit link right after its closing `</li>`, mirroring that block's markup:

```blade
                    <!-- Platform Profit -->
                    <li>
                        <a href="{{ route('statistics.profit') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('statistics.profit')
                                     ? 'bg-gradient-to-r from-rose-600 to-rose-500 text-white shadow-lg shadow-rose-500/30'
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg
                                        {{ request()->routeIs('statistics.profit') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22a10 10 0 100-20 10 10 0 000 20z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Profit</span>
                        </a>
                    </li>
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --filter=ProfitPageTest`
Expected: PASS (2 tests). If `<x-admin-layout>` 500s for the test admin, report it — do not stub the layout.

- [ ] **Step 8: Run the full analytics suite + commit**

Run: `php artisan test --filter=Analytics`
Expected: PASS (all analytics tests green).

```bash
git add Modules/Admin/app/Http/Controllers/StatisticsController.php Modules/Admin/routes/web.php Modules/Admin/resources/views/statistics/profit.blade.php resources/views/components/admin/sidebar.blade.php tests/Feature/Analytics/ProfitPageTest.php
git commit -m "feat(analytics): admin Profit page — platform profit + vendor payable, with sidebar link"
```

---

## Definition of done (Phase 5)

- `php artisan test --filter=Analytics` green.
- `GET statistics/profit` renders for an admin: platform-profit headline (paid), commission collected, total owed to vendors (linking to the payout page), and a placed-vs-paid table; date-range respected.
- Profit = revenue − vendor earnings, computed with revenue and earnings summed separately (no join fan-out); paid = `payment_status='completed'`; null vendor_earning coalesced to 0.
- No migration, no capture change; sidebar link added.

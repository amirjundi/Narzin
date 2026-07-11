# Analytics Phase 3 — Traffic Attribution Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Snapshot session UTM onto each order at placement, and report revenue/orders/AOV by channel + campaign as an Attribution section on the admin Funnel page.

**Architecture:** A migration adds nullable `utm_*` columns to `orders`; `placeOrder` copies the session's UTM onto the order (best-effort). `AttributionService` aggregates orders by channel/campaign over a `DateRange`. The existing `funnelStatistics` controller + `funnel.blade.php` gain an Attribution section — no new route or page.

**Tech Stack:** Laravel 11, nwidart modules, PHPUnit 11, SQLite test DB with `RefreshDatabase`.

## Global Constraints

- Money is `decimal(12,2)`; revenue = `SUM(orders.total_amount)` (the figure existing order stats use), rounded to 2. (from spec)
- The order attribution is a **snapshot**: read the order's own `utm_*` columns in the report; never join `orders` → `visit_sessions` at read time. (from spec)
- The snapshot in `placeOrder` is **best-effort/non-blocking** — wrap in try/catch; attribution capture must never break checkout. (from spec)
- Null UTM groups/labels as `'(none)'`. Use `COALESCE(col,'(none)')` — this IS portable across MySQL/Postgres/SQLite (unlike `CONCAT`/`||`). (from spec)
- Reporting is a section on the existing Funnel page; reuse the `DateRange` already built in `funnelStatistics`. No new route/controller/page. (from spec)
- Phase 1/2 exist: `Modules\Telemetry\Models\VisitSession`, `Modules\Admin\Support\DateRange`, `Modules\Admin\Services\{FunnelService,AbandonedCartService}`.

---

### Task 1: orders attribution columns + Order fillable

**Files:**
- Create: `Modules/Checkout/database/migrations/2026_07_11_000000_add_attribution_to_orders_table.php`
- Modify: `Modules/Checkout/app/Models/Order.php` (`$fillable`)
- Test: `tests/Feature/Analytics/OrderAttributionColumnsTest.php`

**Interfaces:**
- Produces six nullable columns on `orders`: `attributed_session_id`, `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content`, all mass-assignable. Tasks 2–3 consume them.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Analytics/OrderAttributionColumnsTest.php`:

```php
<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Checkout\Models\Order;
use Tests\TestCase;

class OrderAttributionColumnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_accepts_attribution_columns(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => 50.00,
            'order_number' => 'T-1',
            'order_status' => 'pending',
            'attributed_session_id' => 'sess-1',
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'july',
            'utm_term' => 'shoes',
            'utm_content' => 'ad1',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id, 'utm_source' => 'google', 'utm_campaign' => 'july',
            'attributed_session_id' => 'sess-1',
        ]);
    }
}
```

> If `Order::create` fails on a missing NOT NULL column not shown here, add the minimum such columns from the orders schema to this fixture (mirror an existing order-creating test like `tests/Feature/Checkout/PlaceOrderTest.php`). The attribution assertion is the point.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=OrderAttributionColumnsTest`
Expected: FAIL — unknown column `attributed_session_id`.

- [ ] **Step 3: Write the migration**

Create `Modules/Checkout/database/migrations/2026_07_11_000000_add_attribution_to_orders_table.php`:

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
            $table->string('attributed_session_id')->nullable();
            $table->string('utm_source')->nullable()->index();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable()->index();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'attributed_session_id', 'utm_source', 'utm_medium',
                'utm_campaign', 'utm_term', 'utm_content',
            ]);
        });
    }
};
```

- [ ] **Step 4: Add the columns to Order `$fillable`**

In `Modules/Checkout/app/Models/Order.php`, add these entries to the `$fillable` array (e.g. after `'free_shipping_promotion_id'`):

```php
        'attributed_session_id',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=OrderAttributionColumnsTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add Modules/Checkout/database/migrations/2026_07_11_000000_add_attribution_to_orders_table.php Modules/Checkout/app/Models/Order.php tests/Feature/Analytics/OrderAttributionColumnsTest.php
git commit -m "feat(analytics): add UTM attribution columns to orders"
```

---

### Task 2: snapshot session UTM onto the order in placeOrder

**Files:**
- Modify: `Modules/Checkout/app/Http/Controllers/V1/Api/CheckoutController.php` (`placeOrder`, right after the Phase 1 `placed` capture at ~line 366-371)
- Test: `tests/Feature/Analytics/OrderAttributionSnapshotTest.php`

**Interfaces:**
- Consumes: `Modules\Telemetry\Models\VisitSession`, the order's request `session_id`.
- Produces: a placed order carries the snapshotted `utm_*` + `attributed_session_id` from its session (or nulls if no session/UTM).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Analytics/OrderAttributionSnapshotTest.php`. It mirrors the place-order fixture from `tests/Feature/Telemetry/CheckoutCaptureTest.php` (seed address/vendor/category/product/variant/zone/method + a `cart` row, mock `NassPaymentService::createTransaction`), and adds a `visit_session` with UTM keyed by the `session_id` it posts:

```php
<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Services\NassPaymentService;
use Modules\Telemetry\Models\VisitSession;
use Tests\TestCase;

class OrderAttributionSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_placed_order_snapshots_session_utm(): void
    {
        $user = User::factory()->create();

        $addressId = DB::table('user_address')->insertGetId(['user_id' => $user->id, 'address' => '123 St', 'created_at' => now(), 'updated_at' => now()]);
        $vendorId = DB::table('vendors')->insertGetId(['store_name_in_arabic' => 'م', 'store_name_in_german' => 'L', 'user_id' => User::factory()->create()->id, 'created_at' => now(), 'updated_at' => now()]);
        $categoryId = DB::table('categories')->insertGetId(['name_arabic' => 'ف', 'name_german' => 'K', 'slug_arabic' => 'cat-ar', 'slug_german' => 'cat-de', 'created_at' => now(), 'updated_at' => now()]);
        $productId = DB::table('products')->insertGetId(['name_arabic' => 'م', 'name_german' => 'P', 'slug_arabic' => 'p-ar', 'slug_german' => 'p-de', 'category_id' => $categoryId, 'vendor_id' => $vendorId, 'is_active' => true, 'weight' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $variantId = DB::table('product_variants')->insertGetId(['product_id' => $productId, 'price' => 100, 'stock' => 10, 'sku' => 'SKU-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false, 'created_at' => now(), 'updated_at' => now()]);
        $zoneId = DB::table('delivery_zones')->insertGetId(['name_english' => 'Z', 'name_german' => 'Z', 'name_arabic' => 'Z', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $methodId = DB::table('delivery_methods')->insertGetId(['delivery_zone_id' => $zoneId, 'name_english' => 'S', 'name_german' => 'S', 'name_arabic' => 'S', 'base_price' => 5, 'price_per_kg' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cart')->insert(['user_id' => $user->id, 'product_id' => $productId, 'product_variant_id' => $variantId, 'quantity' => 1, 'created_at' => now(), 'updated_at' => now()]);

        VisitSession::create([
            'session_id' => 'sess-attr', 'utm_source' => 'google', 'utm_medium' => 'cpc',
            'utm_campaign' => 'july', 'first_seen_at' => now(), 'last_seen_at' => now(),
        ]);

        $this->mock(NassPaymentService::class, function ($mock) {
            $mock->shouldReceive('createTransaction')->once()->andReturn([
                'success' => true, 'data' => ['url' => 'https://pay.example/x', 'transactionParams' => []],
            ]);
        });

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/place-order', [
            'session_id' => 'sess-attr', 'address_id' => $addressId, 'delivery_method_id' => $methodId,
        ])->assertStatus(200);

        $order = DB::table('orders')->where('user_id', $user->id)->first();
        $this->assertSame('google', $order->utm_source);
        $this->assertSame('july', $order->utm_campaign);
        $this->assertSame('sess-attr', $order->attributed_session_id);
    }
}
```

> The seed fixture must match what `placeOrder` needs — if a column/field differs, mirror `tests/Feature/Telemetry/CheckoutCaptureTest.php` (the canonical, passing place-order harness). The three attribution assertions at the end are the point of this test.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=OrderAttributionSnapshotTest`
Expected: FAIL — `$order->utm_source` is null.

- [ ] **Step 3: Add the snapshot after the `placed` capture**

In `CheckoutController::placeOrder`, immediately after the existing Phase 1 `placed` `recordCheckoutEvent(...)` block (~line 366-371, after `DB::commit()`, where `$order` and the request are in scope), insert:

```php
                // Snapshot the session's UTM attribution onto the order (best-effort).
                try {
                    $attrSessionId = $request->input('session_id');
                    if ($attrSessionId) {
                        $visitSession = \Modules\Telemetry\Models\VisitSession::where('session_id', $attrSessionId)->first();
                        if ($visitSession) {
                            $order->update([
                                'attributed_session_id' => $attrSessionId,
                                'utm_source' => $visitSession->utm_source,
                                'utm_medium' => $visitSession->utm_medium,
                                'utm_campaign' => $visitSession->utm_campaign,
                                'utm_term' => $visitSession->utm_term,
                                'utm_content' => $visitSession->utm_content,
                            ]);
                        }
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('order attribution snapshot failed', ['error' => $e->getMessage()]);
                }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=OrderAttributionSnapshotTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add Modules/Checkout/app/Http/Controllers/V1/Api/CheckoutController.php tests/Feature/Analytics/OrderAttributionSnapshotTest.php
git commit -m "feat(analytics): snapshot session UTM onto order at placement"
```

---

### Task 3: AttributionService

**Files:**
- Create: `Modules/Admin/app/Services/AttributionService.php`
- Test: `tests/Feature/Analytics/AttributionServiceTest.php`

**Interfaces:**
- Consumes: `DateRange`, `Modules\Checkout\Models\Order`.
- Produces `Modules\Admin\Services\AttributionService` with `byChannel(DateRange): Collection` (rows: source, medium, orders, revenue, aov) and `byCampaign(DateRange): Collection` (rows: campaign, orders, revenue, aov). Task 4 consumes it.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Analytics/AttributionServiceTest.php`:

```php
<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Services\AttributionService;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Tests\TestCase;

class AttributionServiceTest extends TestCase
{
    use RefreshDatabase;

    private function range(): DateRange
    {
        return new DateRange(now()->subDays(30)->startOfDay(), now()->endOfDay());
    }

    private function order(array $attrs): void
    {
        $user = User::factory()->create();
        Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => 'T-' . uniqid(),
            'order_status' => 'completed',
            'total_amount' => 100.00,
        ], $attrs));
    }

    public function test_by_channel_aggregates_revenue_orders_aov(): void
    {
        $this->order(['utm_source' => 'google', 'utm_medium' => 'cpc', 'total_amount' => 100]);
        $this->order(['utm_source' => 'google', 'utm_medium' => 'cpc', 'total_amount' => 300]);
        $this->order(['utm_source' => 'facebook', 'utm_medium' => 'social', 'total_amount' => 50]);

        $rows = (new AttributionService())->byChannel($this->range());
        $google = $rows->firstWhere('source', 'google');

        $this->assertSame(2, $google['orders']);
        $this->assertEquals(400.00, $google['revenue']);
        $this->assertEquals(200.00, $google['aov']);
    }

    public function test_null_utm_groups_as_none(): void
    {
        $this->order(['total_amount' => 20]); // no utm

        $rows = (new AttributionService())->byChannel($this->range());
        $none = $rows->firstWhere('source', '(none)');
        $this->assertNotNull($none);
        $this->assertSame(1, $none['orders']);
    }

    public function test_by_campaign_groups_by_campaign(): void
    {
        $this->order(['utm_campaign' => 'july', 'total_amount' => 100]);
        $this->order(['utm_campaign' => 'july', 'total_amount' => 100]);

        $rows = (new AttributionService())->byCampaign($this->range());
        $july = $rows->firstWhere('campaign', 'july');
        $this->assertSame(2, $july['orders']);
        $this->assertEquals(200.00, $july['revenue']);
    }
}
```

> If `Order::create` needs a NOT NULL column beyond these, add it to the `order()` helper (mirror an existing order-creating test).

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AttributionServiceTest`
Expected: FAIL — `Class "Modules\Admin\Services\AttributionService" not found`.

- [ ] **Step 3: Write the service**

Create `Modules/Admin/app/Services/AttributionService.php`:

```php
<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Collection;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;

/**
 * Read-only revenue attribution over orders' snapshotted UTM columns.
 * Uses COALESCE(col,'(none)') — portable across MySQL/Postgres/SQLite.
 */
class AttributionService
{
    public function byChannel(DateRange $range): Collection
    {
        return Order::query()
            ->whereBetween('created_at', [$range->from, $range->to])
            ->selectRaw("COALESCE(utm_source, '(none)') as source")
            ->selectRaw("COALESCE(utm_medium, '(none)') as medium")
            ->selectRaw("COUNT(*) as orders")
            ->selectRaw("SUM(total_amount) as revenue")
            ->groupByRaw("COALESCE(utm_source, '(none)'), COALESCE(utm_medium, '(none)')")
            ->get()
            ->map(fn ($r) => $this->row(['source' => $r->source, 'medium' => $r->medium], $r))
            ->sortByDesc('revenue')->values();
    }

    public function byCampaign(DateRange $range): Collection
    {
        return Order::query()
            ->whereBetween('created_at', [$range->from, $range->to])
            ->selectRaw("COALESCE(utm_campaign, '(none)') as campaign")
            ->selectRaw("COUNT(*) as orders")
            ->selectRaw("SUM(total_amount) as revenue")
            ->groupByRaw("COALESCE(utm_campaign, '(none)')")
            ->get()
            ->map(fn ($r) => $this->row(['campaign' => $r->campaign], $r))
            ->sortByDesc('revenue')->values();
    }

    private function row(array $keys, $r): array
    {
        $orders = (int) $r->orders;
        $revenue = round((float) $r->revenue, 2);
        return $keys + [
            'orders' => $orders,
            'revenue' => $revenue,
            'aov' => $orders > 0 ? round($revenue / $orders, 2) : 0.0,
        ];
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=AttributionServiceTest`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
git add Modules/Admin/app/Services/AttributionService.php tests/Feature/Analytics/AttributionServiceTest.php
git commit -m "feat(analytics): AttributionService — revenue by channel + campaign"
```

---

### Task 4: render Attribution on the Funnel page

**Files:**
- Modify: `Modules/Admin/app/Http/Controllers/StatisticsController.php` (`funnelStatistics` — add attribution; add import)
- Modify: `Modules/Admin/resources/views/statistics/funnel.blade.php` (add Attribution section before the closing wrapper)
- Test: `tests/Feature/Analytics/AttributionPageTest.php`

**Interfaces:**
- Consumes: `AttributionService` (Task 3).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Analytics/AttributionPageTest.php`:

```php
<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Tests\TestCase;

class AttributionPageTest extends TestCase
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

    public function test_funnel_page_shows_attribution_section(): void
    {
        $this->actingAs($this->admin())
            ->get(route('statistics.funnel'))
            ->assertOk()
            ->assertSee('Attribution')
            ->assertSee('Campaign');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AttributionPageTest`
Expected: FAIL — "Attribution"/"Campaign" not present.

- [ ] **Step 3: Extend the controller**

In `StatisticsController.php`, add the import near the other service imports (after line ~17):

```php
use Modules\Admin\Services\AttributionService;
```

Then in `funnelStatistics`, build attribution and pass it to the view. The method becomes:

```php
    public function funnelStatistics(Request $request)
    {
        $range = DateRange::fromRequest($request);
        $funnel = (new FunnelService())->funnel($range);
        $abandoned = (new AbandonedCartService())->abandoned($range);
        $attribution = [
            'byChannel' => (new AttributionService())->byChannel($range),
            'byCampaign' => (new AttributionService())->byCampaign($range),
        ];

        return view('admin::statistics.funnel', [
            'funnel' => $funnel,
            'abandoned' => $abandoned,
            'attribution' => $attribution,
            'from' => $range->from->toDateString(),
            'to' => $range->to->toDateString(),
        ]);
    }
```

- [ ] **Step 4: Add the Attribution section to the Blade view**

In `Modules/Admin/resources/views/statistics/funnel.blade.php`, immediately before the final `</div>` that closes `<div class="space-y-8 px-4">` (i.e. before the last two lines `</div>` + `</x-admin-layout>`), insert:

```blade
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-1">Attribution</h2>
            <p class="text-sm text-gray-500 mb-4">
                Revenue by traffic source. Fills in as UTM-tagged visitors place
                orders; untagged/direct traffic shows as “(none)”.
            </p>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="overflow-x-auto">
                    <h3 class="text-sm font-medium mb-2 text-gray-700">By Channel</h3>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2 pr-4">Source</th>
                                <th class="py-2 pr-4">Medium</th>
                                <th class="py-2 pr-4">Orders</th>
                                <th class="py-2 pr-4">Revenue</th>
                                <th class="py-2 pr-4">AOV</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attribution['byChannel'] as $row)
                                <tr class="border-b">
                                    <td class="py-2 pr-4">{{ $row['source'] }}</td>
                                    <td class="py-2 pr-4">{{ $row['medium'] }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['orders']) }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['revenue'], 2) }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['aov'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-4 text-center text-gray-400">No attributed orders yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="overflow-x-auto">
                    <h3 class="text-sm font-medium mb-2 text-gray-700">By Campaign</h3>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2 pr-4">Campaign</th>
                                <th class="py-2 pr-4">Orders</th>
                                <th class="py-2 pr-4">Revenue</th>
                                <th class="py-2 pr-4">AOV</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attribution['byCampaign'] as $row)
                                <tr class="border-b">
                                    <td class="py-2 pr-4">{{ $row['campaign'] }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['orders']) }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['revenue'], 2) }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['aov'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-gray-400">No attributed orders yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=AttributionPageTest`
Expected: PASS.

- [ ] **Step 6: Run the full analytics suite + commit**

Run: `php artisan test --filter=Analytics`
Expected: PASS (Phase 2 + Phase 3 analytics tests green).

```bash
git add Modules/Admin/app/Http/Controllers/StatisticsController.php Modules/Admin/resources/views/statistics/funnel.blade.php tests/Feature/Analytics/AttributionPageTest.php
git commit -m "feat(analytics): Attribution section (channel + campaign) on Funnel page"
```

---

## Definition of done (Phase 3)

- `php artisan test --filter=Analytics` green.
- Placing an order with a UTM-carrying session snapshots the UTM onto the order; the snapshot is best-effort (checkout never breaks on it).
- The admin Funnel page shows an Attribution section: revenue/orders/AOV by channel and by campaign, `(none)` for untagged, date-range respected.
- One migration added (`orders` UTM columns) — deploy runs it.

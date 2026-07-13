# Analytics Phase 9 — Inventory Analytics Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add an admin Inventory analytics page with four report-only views — inventory valuation, reorder worklist, dead stock, expiring stock — over existing tables, no new tables.

**Architecture:** A read-only `InventoryService` runs portable grouped SQL over `product_variants` (+ `products`, `order_items`, `orders`, `categories`, `vendors`). A Blade `statistics/inventory` page renders it. Consistent with Phases 2–8 analytics.

**Tech Stack:** Laravel 11 (nwidart modules), `Modules\Admin\Services`, `DateRange`, Blade `admin::statistics.*`, PHPUnit.

## Global Constraints

- Portable SQL only: `SUM(stock*cost)` / `SUM(stock*price)` arithmetic, `COALESCE`, `groupBy`, `whereNotIn` subquery. NO DB-specific `CASE "double-quoted-string"`, no median/percentile DB functions, no `CONCAT`.
- No N+1: every method runs a fixed small number of queries (grouped aggregates / one subquery + main), never a query per variant.
- Only `is_active = 1` variants count in every view (unsellable/disabled stock is excluded) — state this on the page.
- Thresholds from config: `low_stock_threshold` (default 5), `expiry_days_ahead` (default 30). Do NOT touch the existing Products page's hardcoded `stock < 10` bucket.
- Product display name = `name_german` with `name_arabic` fallback; services return BOTH `product_name_arabic` + `product_name_german` so the blade picks.
- Variant→vendor is 2 hops: `product_variants.product_id → products.vendor_id → vendors`. There is NO `vendor_id` on `product_variants`.
- List views (reorder/dead/expiring) capped at 200 rows (protect the page).
- Money values rounded to 2dp in the returned arrays.
- Analytics services live in `Modules/Admin/app/Services`; sidebar link goes in the app-level `resources/views/components/admin/sidebar.blade.php`.

---

### Task 1: InventoryService + config keys

**Files:**
- Modify: `narzinapp-main/Modules/Telemetry/config/config.php` (2 keys)
- Create: `narzinapp-main/Modules/Admin/app/Services/InventoryService.php`
- Test: `narzinapp-main/tests/Feature/InventoryServiceTest.php`

**Interfaces:**
- Consumes: `Modules\Admin\Support\DateRange` (public `$from`/`$to` Carbon; `new DateRange($from,$to)`); Eloquent models `Modules\Catalog...\ProductVariant`, `Product`, `Modules\Checkout\Models\Order`, `OrderItem`. (Find the exact namespace of `ProductVariant`/`Product`/`OrderItem` by reading the model files — they are under a catalog/product module; do NOT guess the namespace.)
- Produces:
  - `valuation(): array` → `['total_units'=>int,'value_at_cost'=>float,'value_at_retail'=>float,'potential_margin'=>float,'by_category'=>Collection,'by_vendor'=>Collection]` (each breakdown row `['name'=>string,'units'=>int,'value_at_cost'=>float,'value_at_retail'=>float]`)
  - `reorderWorklist(): Collection` of `['sku','product_name_arabic','product_name_german','stock','vendor_name','is_out']`
  - `deadStock(DateRange $range): Collection` of `['sku','product_name_arabic','product_name_german','stock','value_at_cost','vendor_name']`
  - `expiringStock(): Collection` of `['sku','product_name_arabic','product_name_german','stock','expiry_date','value_at_cost','vendor_name']`

- [ ] **Step 1: Add config keys**

In `Modules/Telemetry/config/config.php`, after `fulfillment_sla_hours`:

```php
'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 5),
'expiry_days_ahead' => env('EXPIRY_DAYS_AHEAD', 30),
```

- [ ] **Step 2: Confirm model namespaces + table/column names**

Before writing code, read the model files to get exact namespaces and confirm columns:
- `ProductVariant` (has `stock`, `cost`, `price`, `sku`, `expiry_date`, `is_active`, `product_id`) — find it via: search for `class ProductVariant`.
- `Product` (has `name_arabic`, `name_german`, `vendor_id`, `category_id`).
- `OrderItem` (has `product_variant_id`, `order_id`), `Order` (has `created_at`), `categories`/`vendors` table + name columns (categories use `name_arabic`/`name_german`; vendors — check the vendor name column, likely `store_name` or `name`; read the Vendor model/migration and use the real column).

Record the real table names for the joins (likely `product_variants`, `products`, `order_items`, `orders`, `categories`, `vendors`).

- [ ] **Step 3: Write the failing test**

Seed manually (no `Order::factory()`). Mirror the Phase 8 tests' seeding style for orders; for products/variants create rows directly via the models or `DB::table(...)->insert`. Use the REAL namespaces/columns found in Step 2. Skeleton (adapt names to reality):

```php
<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Services\InventoryService;
use Modules\Admin\Support\DateRange;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    // Helper: create a product (+vendor +category) and a variant with given stock/cost/price/expiry.
    // Return the variant id. Use the real model classes / columns from Step 2.
    private function makeVariant(array $v): int { /* seed product+variant, return variant id */ }

    public function test_valuation_totals_and_margin(): void
    {
        // Variant A: stock 10, cost 2.00, price 5.00 (active)
        // Variant B: stock 4, cost 3.00, price 7.00 (active)
        // Variant C: stock 100, cost 1.00, price 2.00 (INACTIVE — must be excluded)
        $this->makeVariant(['stock' => 10, 'cost' => 2, 'price' => 5, 'is_active' => 1]);
        $this->makeVariant(['stock' => 4, 'cost' => 3, 'price' => 7, 'is_active' => 1]);
        $this->makeVariant(['stock' => 100, 'cost' => 1, 'price' => 2, 'is_active' => 0]);

        $val = (new InventoryService())->valuation();

        $this->assertSame(14, $val['total_units']);                 // 10+4, inactive excluded
        $this->assertEqualsWithDelta(32.0, $val['value_at_cost'], 0.01);   // 10*2 + 4*3
        $this->assertEqualsWithDelta(78.0, $val['value_at_retail'], 0.01); // 10*5 + 4*7
        $this->assertEqualsWithDelta(46.0, $val['potential_margin'], 0.01);
    }

    public function test_reorder_worklist_respects_threshold_and_flags_out(): void
    {
        config(['telemetry.low_stock_threshold' => 5]);
        $this->makeVariant(['stock' => 0, 'cost' => 1, 'price' => 2, 'is_active' => 1, 'sku' => 'OUT-1']);
        $this->makeVariant(['stock' => 3, 'cost' => 1, 'price' => 2, 'is_active' => 1, 'sku' => 'LOW-1']);
        $this->makeVariant(['stock' => 50, 'cost' => 1, 'price' => 2, 'is_active' => 1, 'sku' => 'OK-1']);

        $list = (new InventoryService())->reorderWorklist();
        $skus = $list->pluck('sku')->all();

        $this->assertContains('OUT-1', $skus);
        $this->assertContains('LOW-1', $skus);
        $this->assertNotContains('OK-1', $skus);
        $this->assertSame('OUT-1', $list->first()['sku']); // stock asc → 0 first
        $this->assertTrue((bool) $list->firstWhere('sku', 'OUT-1')['is_out']);
        $this->assertFalse((bool) $list->firstWhere('sku', 'LOW-1')['is_out']);
    }

    public function test_dead_stock_excludes_variants_sold_in_window(): void
    {
        $range = new DateRange(Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31'));

        $soldId = $this->makeVariant(['stock' => 10, 'cost' => 2, 'price' => 5, 'is_active' => 1, 'sku' => 'SOLD']);
        $deadId = $this->makeVariant(['stock' => 20, 'cost' => 2, 'price' => 5, 'is_active' => 1, 'sku' => 'DEAD']);
        // Create an order in-window with an order_item for $soldId (mirror Phase 8 order seeding).
        $this->sellVariantInWindow($soldId, Carbon::parse('2026-07-10'));

        $dead = (new InventoryService())->deadStock($range);
        $skus = $dead->pluck('sku')->all();

        $this->assertContains('DEAD', $skus);
        $this->assertNotContains('SOLD', $skus);
        $this->assertEqualsWithDelta(40.0, $dead->firstWhere('sku', 'DEAD')['value_at_cost'], 0.01); // 20*2
    }

    public function test_expiring_stock_window_and_null_expiry(): void
    {
        config(['telemetry.expiry_days_ahead' => 30]);
        $this->makeVariant(['stock' => 5, 'cost' => 2, 'price' => 5, 'is_active' => 1, 'sku' => 'SOON', 'expiry_date' => Carbon::now()->addDays(10)->toDateString()]);
        $this->makeVariant(['stock' => 5, 'cost' => 2, 'price' => 5, 'is_active' => 1, 'sku' => 'LATER', 'expiry_date' => Carbon::now()->addDays(90)->toDateString()]);
        $this->makeVariant(['stock' => 5, 'cost' => 2, 'price' => 5, 'is_active' => 1, 'sku' => 'NOEXP', 'expiry_date' => null]);

        $exp = (new InventoryService())->expiringStock();
        $skus = $exp->pluck('sku')->all();

        $this->assertContains('SOON', $skus);
        $this->assertNotContains('LATER', $skus);
        $this->assertNotContains('NOEXP', $skus);
    }

    // sellVariantInWindow($variantId, Carbon $at): seed an Order (created_at=$at) + OrderItem(product_variant_id=$variantId).
}
```

Implement `makeVariant`/`sellVariantInWindow` using the real models discovered in Step 2. Keep them minimal — only the columns the queries read need real values; give required NOT-NULL columns any valid value.

- [ ] **Step 4: Run it — verify it fails**

Run: `php artisan test --filter=InventoryServiceTest`
Expected: FAIL (class not found).

- [ ] **Step 5: Implement the service**

Use the real model namespaces/table/column names from Step 2. Reference implementation (adjust `ProductVariant`/`Product`/`OrderItem` namespaces, and the vendor name column, to reality):

```php
<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Support\DateRange;
// use the REAL namespaces:
use Modules\Product\Models\ProductVariant; // <-- replace with real
use Modules\Checkout\Models\OrderItem;      // <-- replace with real

/** Read-only inventory analytics over product_variants (+ products/order_items). */
class InventoryService
{
    private const CAP = 200;

    public function valuation(): array
    {
        // Single aggregate over active variants.
        $totals = DB::table('product_variants')
            ->where('is_active', 1)
            ->selectRaw('COALESCE(SUM(stock),0) as units, COALESCE(SUM(stock*cost),0) as cost_val, COALESCE(SUM(stock*price),0) as retail_val')
            ->first();

        $byCategory = DB::table('product_variants as pv')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->where('pv.is_active', 1)
            ->groupBy('c.id', 'c.name_german', 'c.name_arabic')
            ->selectRaw("COALESCE(c.name_german, c.name_arabic, '(none)') as name, SUM(pv.stock) as units, SUM(pv.stock*pv.cost) as cost_val, SUM(pv.stock*pv.price) as retail_val")
            ->orderByDesc('cost_val')
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'units' => (int) $r->units, 'value_at_cost' => round((float) $r->cost_val, 2), 'value_at_retail' => round((float) $r->retail_val, 2)]);

        $byVendor = DB::table('product_variants as pv')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftJoin('vendors as v', 'p.vendor_id', '=', 'v.id')
            ->where('pv.is_active', 1)
            ->groupBy('v.id', 'v.VENDOR_NAME_COL') // <-- real vendor name column
            ->selectRaw("COALESCE(v.VENDOR_NAME_COL, '(none)') as name, SUM(pv.stock) as units, SUM(pv.stock*pv.cost) as cost_val, SUM(pv.stock*pv.price) as retail_val")
            ->orderByDesc('cost_val')
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'units' => (int) $r->units, 'value_at_cost' => round((float) $r->cost_val, 2), 'value_at_retail' => round((float) $r->retail_val, 2)]);

        $cost = round((float) $totals->cost_val, 2);
        $retail = round((float) $totals->retail_val, 2);

        return [
            'total_units' => (int) $totals->units,
            'value_at_cost' => $cost,
            'value_at_retail' => $retail,
            'potential_margin' => round($retail - $cost, 2),
            'by_category' => $byCategory,
            'by_vendor' => $byVendor,
        ];
    }

    public function reorderWorklist(): \Illuminate\Support\Collection
    {
        $threshold = (int) config('telemetry.low_stock_threshold', 5);
        return DB::table('product_variants as pv')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftJoin('vendors as v', 'p.vendor_id', '=', 'v.id')
            ->where('pv.is_active', 1)
            ->where('pv.stock', '<=', $threshold)
            ->orderBy('pv.stock')
            ->orderBy('p.name_german')
            ->limit(self::CAP)
            ->get(['pv.sku', 'pv.stock', 'p.name_arabic', 'p.name_german', 'v.VENDOR_NAME_COL as vendor_name'])
            ->map(fn ($r) => [
                'sku' => $r->sku,
                'product_name_arabic' => $r->name_arabic,
                'product_name_german' => $r->name_german,
                'stock' => (int) $r->stock,
                'vendor_name' => $r->vendor_name,
                'is_out' => (int) $r->stock <= 0,
            ]);
    }

    public function deadStock(DateRange $range): \Illuminate\Support\Collection
    {
        // Variant ids sold in the window (subquery), excluded from dead stock.
        $soldIds = DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->whereBetween('o.created_at', [$range->from, $range->to])
            ->distinct()
            ->pluck('oi.product_variant_id');

        return DB::table('product_variants as pv')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftJoin('vendors as v', 'p.vendor_id', '=', 'v.id')
            ->where('pv.is_active', 1)
            ->where('pv.stock', '>', 0)
            ->whereNotIn('pv.id', $soldIds)
            ->orderByDesc(DB::raw('pv.stock*pv.cost'))
            ->limit(self::CAP)
            ->get(['pv.sku', 'pv.stock', 'pv.cost', 'p.name_arabic', 'p.name_german', 'v.VENDOR_NAME_COL as vendor_name'])
            ->map(fn ($r) => [
                'sku' => $r->sku,
                'product_name_arabic' => $r->name_arabic,
                'product_name_german' => $r->name_german,
                'stock' => (int) $r->stock,
                'value_at_cost' => round((float) $r->stock * (float) $r->cost, 2),
                'vendor_name' => $r->vendor_name,
            ]);
    }

    public function expiringStock(): \Illuminate\Support\Collection
    {
        $cutoff = Carbon::now()->addDays((int) config('telemetry.expiry_days_ahead', 30))->toDateString();
        return DB::table('product_variants as pv')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftJoin('vendors as v', 'p.vendor_id', '=', 'v.id')
            ->where('pv.is_active', 1)
            ->where('pv.stock', '>', 0)
            ->whereNotNull('pv.expiry_date')
            ->where('pv.expiry_date', '<=', $cutoff)
            ->orderBy('pv.expiry_date')
            ->limit(self::CAP)
            ->get(['pv.sku', 'pv.stock', 'pv.cost', 'pv.expiry_date', 'p.name_arabic', 'p.name_german', 'v.VENDOR_NAME_COL as vendor_name'])
            ->map(fn ($r) => [
                'sku' => $r->sku,
                'product_name_arabic' => $r->name_arabic,
                'product_name_german' => $r->name_german,
                'stock' => (int) $r->stock,
                'expiry_date' => $r->expiry_date,
                'value_at_cost' => round((float) $r->stock * (float) $r->cost, 2),
                'vendor_name' => $r->vendor_name,
            ]);
    }
}
```

Replace every `VENDOR_NAME_COL` with the real vendors name column (from Step 2), and the two `use` imports with the real namespaces (the service uses `DB::table` for the heavy lifting, so model imports may be trimmed if unused — remove any `use` you don't reference to avoid a dead-import finding).

Note: using `DB::table` with real table names is deliberate (portable aggregates, no model overhead). If the project convention strongly favors Eloquent, the same queries via the models are acceptable — but keep them to the same fixed query count.

- [ ] **Step 6: Run the test — verify it passes**

Run: `php artisan test --filter=InventoryServiceTest`
Expected: PASS (4 tests). If a join column name is wrong, fix it against the real schema from Step 2 — do not weaken assertions.

- [ ] **Step 7: Commit**

```bash
git add narzinapp-main/Modules/Admin/app/Services/InventoryService.php narzinapp-main/Modules/Telemetry/config/config.php narzinapp-main/tests/Feature/InventoryServiceTest.php
git commit -m "feat(analytics): InventoryService — valuation, reorder, dead + expiring stock"
```

---

### Task 2: Admin Inventory page

**Files:**
- Modify: `narzinapp-main/Modules/Admin/app/Http/Controllers/StatisticsController.php` (add `inventoryStatistics`)
- Modify: `narzinapp-main/Modules/Admin/routes/web.php` (route)
- Create: `narzinapp-main/Modules/Admin/resources/views/statistics/inventory.blade.php`
- Modify: `narzinapp-main/resources/views/components/admin/sidebar.blade.php` (link)

**Interfaces:**
- Consumes: `InventoryService` (all 4 methods) + `DateRange::fromRequest`.

- [ ] **Step 1: Add the controller method**

Mirror `fulfillmentStatistics`/`profitStatistics` in `StatisticsController`. Add `use Modules\Admin\Services\InventoryService;`.

```php
public function inventoryStatistics(Request $request)
{
    $range = DateRange::fromRequest($request);
    $service = new InventoryService();

    return view('admin::statistics.inventory', [
        'valuation' => $service->valuation(),
        'reorder' => $service->reorderWorklist(),
        'deadStock' => $service->deadStock($range),
        'expiring' => $service->expiringStock(),
        'lowStockThreshold' => (int) config('telemetry.low_stock_threshold', 5),
        'expiryDaysAhead' => (int) config('telemetry.expiry_days_ahead', 30),
        'from' => $range->from->toDateString(),
        'to' => $range->to->toDateString(),
    ]);
}
```

- [ ] **Step 2: Add the route**

In `Modules/Admin/routes/web.php`, next to `statistics/fulfillment`:

```php
Route::get('statistics/inventory', [StatisticsController::class, 'inventoryStatistics'])->name('statistics.inventory');
```

Same admin-auth group as the other statistics routes.

- [ ] **Step 3: Create the blade page**

Read `Modules/Admin/resources/views/statistics/fulfillment.blade.php` first and reuse its layout wrapper + card/table markup. The page shows:
- **Valuation**: four cards (total units, value@cost, value@retail, potential margin), then a "By category" table and a "By vendor" table (`$valuation['by_category']` / `['by_vendor']`, columns name / units / value@cost / value@retail).
- **Reorder worklist**: table over `$reorder` (SKU, product [`name_german ?? name_arabic`], stock, vendor). Flag `is_out` rows red ("OUT"), other rows amber ("LOW"). Show "threshold = {{ $lowStockThreshold }}". `@forelse`/`@empty` with an empty-state.
- **Dead stock**: the date-range GET form (copy from fulfillment.blade.php), then a table over `$deadStock` (SKU, product, stock, value@cost, vendor). Caveat line: "Dead stock reflects the selected date range; the other three views are point-in-time (current stock)."
- **Expiring stock**: table over `$expiring` (SKU, product, expiry date, stock, value@cost, vendor). Show "within {{ $expiryDaysAhead }} days".
- A note: "All figures cover active variants only."
- Money to 2dp (`number_format($v, 2)`); product name `{{ $row['product_name_german'] ?: $row['product_name_arabic'] }}`.

- [ ] **Step 4: Add the sidebar link**

In `resources/views/components/admin/sidebar.blade.php`, copy the `statistics.fulfillment` link block, repoint to `statistics.inventory`, label "Inventory", matching the active-state markup (`request()->routeIs('statistics.inventory')`), in the same Reports section.

- [ ] **Step 5: Verify**

Run: `php artisan route:list --name=statistics.inventory` (route registered).
Run: `php artisan view:cache` then `php artisan view:clear` (blade compiles, no error mentioning inventory).
Expected: both succeed.

- [ ] **Step 6: Commit**

```bash
git add narzinapp-main/Modules/Admin narzinapp-main/resources/views/components/admin/sidebar.blade.php
git commit -m "feat(analytics): admin inventory analytics page"
```

---

## Notes for the final whole-branch review

- Confirm every view filters `is_active = 1` and the value math (`stock*cost`, `stock*price`) matches the spec.
- Confirm the vendor name column and model namespaces used are the REAL ones (Task 1 Step 2), not placeholders.
- Confirm no N+1: valuation = 3 queries, reorder = 1, deadStock = 2 (subquery + main), expiring = 1.
- Confirm `whereNotIn` on the sold-ids subquery behaves correctly when the set is empty (all active in-stock variants become dead stock — correct) and when large (the pluck is one query; acceptable for current scale — note if a `whereNotExists` correlated form would be better at volume).
- Confirm the page renders product names with the german→arabic fallback and doesn't error on a null vendor (`(none)`).

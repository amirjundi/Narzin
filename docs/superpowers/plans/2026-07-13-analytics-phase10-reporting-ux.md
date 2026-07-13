# Analytics Phase 10 — Reporting UX (date-range + CSV export) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A shared date-range filter component (with presets) and per-section CSV export on all 11 admin statistics pages.

**Architecture:** A `CsvExporter` support class streams CSV (mirroring the existing `OrderController::exportCsv` convention) with formula-injection sanitization. A `<x-admin.date-range-filter>` Blade component replaces the duplicated from/to forms and adds preset links. Each statistics controller method gains an `export` branch mapping a section key → `CsvExporter::stream(...)`; each exportable table gets an "Export CSV" link.

**Tech Stack:** Laravel 11 (nwidart modules), Blade components, `Symfony\Component\HttpFoundation\StreamedResponse`, PHPUnit.

## Global Constraints

- CSV streaming mirrors the existing `Modules/Admin/app/Http/Controllers/OrderController.php::exportCsv` (headers: `Content-Type: text/csv`, `Content-Disposition: attachment; filename=...`, no-cache; `fputcsv` to `php://output` inside a `response()->stream()` callback).
- Every exported cell is sanitized against CSV/formula injection: if the cell (cast to string) starts with `=`, `+`, `-`, `@`, tab (`\t`), or CR (`\r`), prefix it with a single apostrophe `'`.
- Export links preserve the current query via `request()->fullUrlWithQuery(['export' => 'KEY'])`; an unknown `export` key → `abort(404)`; an empty dataset → a header-only CSV (not an error).
- The date-range component is a drop-in for the existing from/to form (same `name="from"`/`name="to"` GET inputs) so `DateRange::fromRequest` keeps working unchanged. Preset links point to `url()->current()` with only `from`/`to`.
- No analytics service is modified; no numbers change. Export rows are a flat projection of the SAME data the page renders.
- Support classes live in `Modules/Admin/app/Support`; the Blade component in `resources/views/components/admin/`.

---

### Task 1: CsvExporter + date-range-filter component

**Files:**
- Create: `narzinapp-main/Modules/Admin/app/Support/CsvExporter.php`
- Create: `narzinapp-main/resources/views/components/admin/date-range-filter.blade.php`
- Test: `narzinapp-main/tests/Feature/CsvExporterTest.php`

**Interfaces:**
- Produces: `CsvExporter::stream(string $filename, array $headers, iterable $rows): \Symfony\Component\HttpFoundation\StreamedResponse` — later tasks call this.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use Modules\Admin\Support\CsvExporter;
use Tests\TestCase;

class CsvExporterTest extends TestCase
{
    private function body(\Symfony\Component\HttpFoundation\StreamedResponse $r): string
    {
        ob_start();
        $r->sendContent();
        return ob_get_clean();
    }

    public function test_streams_header_and_rows_as_csv(): void
    {
        $res = CsvExporter::stream('report.csv', ['Name', 'Qty'], [['Widget', 3], ['Gadget', 10]]);

        $this->assertSame('text/csv', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename=report.csv', $res->headers->get('Content-Disposition'));

        $lines = preg_split('/\r?\n/', trim($this->body($res)));
        $this->assertSame('Name,Qty', $lines[0]);
        $this->assertSame('Widget,3', $lines[1]);
        $this->assertSame('Gadget,10', $lines[2]);
    }

    public function test_neutralises_formula_injection(): void
    {
        $res = CsvExporter::stream('x.csv', ['Val'], [['=1+1'], ['+cmd'], ['-2'], ['@ref'], ['safe']]);
        $body = $this->body($res);

        // Dangerous leading chars get a leading apostrophe; safe values untouched.
        $this->assertStringContainsString("'=1+1", $body);
        $this->assertStringContainsString("'+cmd", $body);
        $this->assertStringContainsString("'-2", $body);
        $this->assertStringContainsString("'@ref", $body);
        $this->assertMatchesRegularExpression('/(^|\n)safe(\r?\n|$)/', $body);
    }
}
```

- [ ] **Step 2: Run it — verify it fails**

Run: `php artisan test --filter=CsvExporterTest`
Expected: FAIL (class not found).

- [ ] **Step 3: Implement CsvExporter**

```php
<?php

namespace Modules\Admin\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/** Streams an array of rows as a CSV download (mirrors OrderController::exportCsv). */
class CsvExporter
{
    /**
     * @param string   $filename Download filename (e.g. "returns-2026-07-13.csv").
     * @param string[] $headers  Column header labels.
     * @param iterable $rows     Iterable of arrays of scalar cell values (column order = $headers).
     */
    public static function stream(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $httpHeaders = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_map([self::class, 'sanitize'], $headers));
            foreach ($rows as $row) {
                fputcsv($out, array_map([self::class, 'sanitize'], array_values((array) $row)));
            }
            fclose($out);
        }, 200, $httpHeaders);
    }

    /** Neutralise CSV/formula injection: prefix a leading =,+,-,@,TAB,CR with an apostrophe. */
    private static function sanitize($value): string
    {
        $s = (string) $value;
        if ($s !== '' && in_array($s[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $s;
        }
        return $s;
    }
}
```

- [ ] **Step 4: Run the test — verify it passes**

Run: `php artisan test --filter=CsvExporterTest`
Expected: PASS (2 tests). Note: `fputcsv` only quotes fields containing special chars, so `Widget,3` stays unquoted — the assertions match that.

- [ ] **Step 5: Build the date-range-filter component**

`resources/views/components/admin/date-range-filter.blade.php`:

```blade
@props(['from', 'to'])
@php
    use Illuminate\Support\Carbon;
    $base = url()->current();
    $presets = [
        '7 days' => [Carbon::now()->subDays(7), Carbon::now()],
        '30 days' => [Carbon::now()->subDays(30), Carbon::now()],
        '90 days' => [Carbon::now()->subDays(90), Carbon::now()],
        'This month' => [Carbon::now()->startOfMonth(), Carbon::now()],
        'This year' => [Carbon::now()->startOfYear(), Carbon::now()],
    ];
@endphp
<div class="mt-4">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <label class="text-sm">From <input type="date" name="from" value="{{ $from }}" class="block border rounded px-2 py-1" /></label>
        <label class="text-sm">To <input type="date" name="to" value="{{ $to }}" class="block border rounded px-2 py-1" /></label>
        <button type="submit" class="bg-gray-800 text-white rounded px-4 py-1.5 text-sm">Apply</button>
    </form>
    <div class="mt-2 flex flex-wrap gap-2 text-xs">
        @foreach ($presets as $label => [$pFrom, $pTo])
            <a href="{{ $base }}?from={{ $pFrom->format('Y-m-d') }}&to={{ $pTo->format('Y-m-d') }}"
               class="px-2 py-1 rounded border text-gray-600 hover:bg-gray-100">{{ $label }}</a>
        @endforeach
    </div>
</div>
```

(No test for the component beyond compilation — it's declarative markup. The next tasks exercise it in page render.)

- [ ] **Step 6: Verify the component compiles**

Run: `php artisan view:cache` then `php artisan view:clear`
Expected: no error mentioning `date-range-filter`.

- [ ] **Step 7: Commit**

```bash
git add narzinapp-main/Modules/Admin/app/Support/CsvExporter.php narzinapp-main/resources/views/components/admin/date-range-filter.blade.php narzinapp-main/tests/Feature/CsvExporterTest.php
git commit -m "feat(analytics): CsvExporter (injection-safe) + shared date-range-filter component"
```

---

### Task 2: Wire analytics pages A — funnel, promotions, profit, payments

**Files:**
- Modify: `narzinapp-main/Modules/Admin/app/Http/Controllers/StatisticsController.php` (export branches in the 4 methods)
- Modify: `narzinapp-main/Modules/Admin/resources/views/statistics/{funnel,promotions,profit,payments}.blade.php` (component + export links)
- Test: `narzinapp-main/tests/Feature/StatisticsExportTest.php`

**Interfaces:**
- Consumes: `CsvExporter::stream` (Task 1); the `<x-admin.date-range-filter>` component (Task 1).

- [ ] **Step 1: Study the current shapes**

Read each of the 4 controller methods and blades. Note the exact variables passed to each view and the exact array keys each renders (these ARE the CSV columns). The services and their return shapes:
- funnel: `$funnel` (stages), `$attribution['byChannel']` / `['byCampaign']` (each row has channel/campaign name + placed orders + placed value — confirm the exact keys in `AttributionService`).
- promotions: `$coupons`, `$promotions`, `$summary` (from `DiscountService` — confirm keys).
- profit: `$profit` (from `ProfitService::summary` — an assoc array of metrics).
- payments: the tables `PaymentAnalyticsService` returns (confirm the variable names + row keys in the controller/blade).

- [ ] **Step 2: Write the failing feature test**

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsExportTest extends TestCase
{
    use RefreshDatabase;

    // Reuse the admin auth helper the other admin feature tests use.
    // (AdminCancellationReasonTest actingAsAdmin pattern.)

    public function test_profit_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.profit', ['export' => 'profit']));
        $res->assertOk();
        $res->assertHeader('Content-Type', 'text/csv');
        $this->assertStringContainsString('attachment', $res->headers->get('Content-Disposition'));
        $this->assertNotEmpty($res->streamedContent());
    }

    public function test_unknown_export_key_404s(): void
    {
        $this->actingAsAdmin()->get(route('statistics.profit', ['export' => 'nope']))->assertNotFound();
    }
}
```

Match `actingAsAdmin()` to the existing admin feature-test setup (see `tests/Feature/AdminCancellationReasonTest.php`). If `streamedContent()` needs the response sent, the framework's `TestResponse` handles it for streamed responses.

- [ ] **Step 3: Run it — verify it fails**

Run: `php artisan test --filter=StatisticsExportTest`
Expected: FAIL (no export handling yet → returns the HTML view, wrong Content-Type; unknown key also returns HTML).

- [ ] **Step 4: Add export branches to the 4 controller methods**

Add `use Modules\Admin\Support\CsvExporter;` at the top. In each method, right after `$range = DateRange::fromRequest($request);` and after computing the data (so the export reuses it), add an export branch. Example for `profitStatistics` (fully worked):

```php
public function profitStatistics(Request $request)
{
    $range = DateRange::fromRequest($request);
    $profit = (new ProfitService())->summary($range);

    if ($export = $request->query('export')) {
        if ($export !== 'profit') {
            abort(404);
        }
        $rows = [];
        foreach ($profit as $key => $value) {
            $rows[] = [ucfirst(str_replace('_', ' ', $key)), is_numeric($value) ? number_format((float) $value, 2) : $value];
        }
        return CsvExporter::stream('profit-' . now()->format('Y-m-d') . '.csv', ['Metric', 'Value'], $rows);
    }

    return view('admin::statistics.profit', [ /* unchanged */ ]);
}
```

Apply the same pattern to the other three, mapping each section key to headers + rows built from the already-computed data:
- **funnel** (`funnelStatistics`): keys `funnel` (headers `['Stage','Actors']`, rows from `$funnel`), `attribution_channel` (headers `['Channel','Placed orders','Placed value']`, rows from `$attribution['byChannel']`), `attribution_campaign` (headers `['Campaign','Placed orders','Placed value']`, rows from `$attribution['byCampaign']`). Use the real row keys confirmed in Step 1.
- **promotions** (`promotionStatistics`): keys `coupons` (rows from `$coupons`), `promotions` (rows from `$promotions`) — headers = the columns the promotions table shows (e.g. `['Code/Name','Uses','Discount total']`; align to the actual row keys).
- **payments** (`paymentStatistics`): one key per rendered table (e.g. `status_breakdown`, `methods`, `failure_reasons`) with headers matching the table columns and rows from the corresponding variable.

If a data value is a Collection of arrays, `foreach` it and push `[$row['k1'], $row['k2'], ...]` in the header order. If a numeric money value, `number_format(...,2)`.

- [ ] **Step 5: Add the date-range component + export links to the 4 blades**

In each of the 4 blades:
- Replace the existing `<form method="GET">…from…to…Apply…</form>` block with `<x-admin.date-range-filter :from="$from" :to="$to" />`.
- Above each exportable table, add:
  ```blade
  <a href="{{ request()->fullUrlWithQuery(['export' => 'SECTION_KEY']) }}"
     class="text-xs text-blue-600 hover:underline">Export CSV</a>
  ```
  with the matching `SECTION_KEY` for that table.

(profit has no date form today? If a page lacks a from/to form, still add the component only if it passes `$from`/`$to` to the view — profit/payments do. If a page doesn't pass `$from`/`$to`, add them to its `view(...)` array as `$range->from->toDateString()` / `->to->toDateString()` first.)

- [ ] **Step 6: Run the test + compile views**

Run: `php artisan test --filter=StatisticsExportTest`
Expected: PASS.
Run: `php artisan view:cache` then `php artisan view:clear` — no blade error.

- [ ] **Step 7: Commit**

```bash
git add narzinapp-main/Modules/Admin narzinapp-main/tests/Feature/StatisticsExportTest.php
git commit -m "feat(analytics): CSV export + date-range presets on funnel/promotions/profit/payments"
```

---

### Task 3: Wire analytics pages B — returns, fulfillment, inventory

**Files:**
- Modify: `narzinapp-main/Modules/Admin/app/Http/Controllers/StatisticsController.php` (export branches in the 3 methods)
- Modify: `narzinapp-main/Modules/Admin/resources/views/statistics/{returns,fulfillment,inventory}.blade.php`
- Test: extend `narzinapp-main/tests/Feature/StatisticsExportTest.php`

**Interfaces:**
- Consumes: `CsvExporter`, the date-range component, and the service return shapes from Phases 7–9.

- [ ] **Step 1: Add export branches (same pattern as Task 2)**

Add the `if ($export = $request->query('export')) { ... abort(404) default ... return CsvExporter::stream(...); }` branch to `returnStatistics`, `fulfillmentStatistics`, `inventoryStatistics`, reusing the already-computed data. Section keys + columns:
- **returns**: `by_reason` → `['Reason','Count']` from `$returns['by_reason']` (or the reason-collection the page renders); `summary` → `['Metric','Value']` from the returns summary array.
- **fulfillment**: `sla` → `['Stage','Count','Avg hours','Median hours','P90 hours']`, rows by iterating the 3 stage keys of `$sla['stages']` (`confirm_to_ship`, `ship_to_deliver`, `placed_to_ship`) projecting `count/avg_hours/median_hours/p90_hours`; `cancellations` → `['Reason','Count']` from `$cancellations['by_reason']`.
- **inventory**: `valuation_by_category` → `['Category','Units','Value at cost','Value at retail']` from `$valuation['by_category']`; `valuation_by_vendor` → same with `Vendor` from `$valuation['by_vendor']`; `reorder` → `['SKU','Product','Stock','Vendor','Out of stock']` from `$reorder` (`is_out` → 'Yes'/'No'); `dead_stock` → `['SKU','Product','Stock','Value at cost','Vendor']` from `$deadStock`; `expiring` → `['SKU','Product','Expiry date','Stock','Value at cost','Vendor']` from `$expiring`. Product column = `product_name_german ?: product_name_arabic`.

- [ ] **Step 2: Add component + export links to the 3 blades**

Replace the from/to form with `<x-admin.date-range-filter :from="$from" :to="$to" />` (returns/fulfillment/inventory all already pass `$from`/`$to`). Add an "Export CSV" link above each exportable table with the matching key (inventory has 5).

- [ ] **Step 3: Extend the feature test**

Add to `StatisticsExportTest`:
```php
public function test_inventory_reorder_export_streams_csv(): void
{
    $res = $this->actingAsAdmin()->get(route('statistics.inventory', ['export' => 'reorder']));
    $res->assertOk();
    $res->assertHeader('Content-Type', 'text/csv');
}

public function test_fulfillment_sla_export_streams_csv(): void
{
    $res = $this->actingAsAdmin()->get(route('statistics.fulfillment', ['export' => 'sla']));
    $res->assertOk();
    $res->assertHeader('Content-Type', 'text/csv');
}
```

- [ ] **Step 4: Run + compile**

Run: `php artisan test --filter=StatisticsExportTest` → PASS.
Run: `php artisan view:cache` then `php artisan view:clear` → no error.

- [ ] **Step 5: Commit**

```bash
git add narzinapp-main/Modules/Admin narzinapp-main/tests/Feature/StatisticsExportTest.php
git commit -m "feat(analytics): CSV export + date-range presets on returns/fulfillment/inventory"
```

---

### Task 4: Wire legacy pages — users, vendors, products, orders

**Files:**
- Modify: `narzinapp-main/Modules/Admin/app/Http/Controllers/StatisticsController.php` (add `Request $request` + export branch to the 4 legacy methods)
- Modify: `narzinapp-main/Modules/Admin/resources/views/statistics/{users,vendors,products,orders}.blade.php` (export links)
- Test: extend `narzinapp-main/tests/Feature/StatisticsExportTest.php`

**Interfaces:**
- Consumes: `CsvExporter`.

- [ ] **Step 1: Add `Request $request` + export branch to the 4 legacy methods**

These methods currently take NO argument. Change each signature to `public function userStatistics(Request $request)` etc. (verify no route model binding breaks — they're plain GET routes, so adding a Request param is safe). After the data is computed, add the export branch. Section keys = the principal collections each method builds (read each method):
- **users**: `top_customers` (from `$topCustomers`: name, email, orders_count, total_spent), `popular_categories` (name, purchase_count). (Skip the retention matrix — it's a pivot, not a flat table; export the two flat tables.)
- **vendors**: the principal vendor tables the method builds (read `vendorStatistics` — export each flat collection, e.g. top vendors by sales).
- **products**: `top_products` (name, total_sold), `stock_status` (status, count), plus category/price distributions if flat.
- **orders**: the flat order-stats collections the method builds (e.g. orders by status/day). (Note: this is `statistics/orders`, distinct from the existing `OrderController::exportCsv` which exports the orders *management* list — do not confuse them.)

For each, build headers + rows from the collection and `CsvExporter::stream(...)`; unknown key → `abort(404)`.

- [ ] **Step 2: Add export links to the 4 blades**

Above each principal table, add the `<a href="{{ request()->fullUrlWithQuery(['export' => 'KEY']) }}">Export CSV</a>` link. (No date-range component — these pages have no date filter.)

- [ ] **Step 3: Extend the feature test**

```php
public function test_products_export_streams_csv(): void
{
    $res = $this->actingAsAdmin()->get(route('statistics.products', ['export' => 'top_products']));
    $res->assertOk();
    $res->assertHeader('Content-Type', 'text/csv');
}
```

- [ ] **Step 4: Run + compile**

Run: `php artisan test --filter=StatisticsExportTest` → PASS.
Run: `php artisan view:cache` then `php artisan view:clear` → no error.

- [ ] **Step 5: Commit**

```bash
git add narzinapp-main/Modules/Admin narzinapp-main/tests/Feature/StatisticsExportTest.php
git commit -m "feat(analytics): CSV export on legacy statistics pages (users/vendors/products/orders)"
```

---

## Notes for the final whole-branch review

- Confirm every export key that appears in a blade link has a matching case in its controller method (and vice-versa) — a mismatch = a 404 link or a dead branch.
- Confirm the injection sanitizer runs on ALL exported cells (headers + data) and that money/number formatting is consistent with the on-screen tables.
- Confirm the date-range component is a true drop-in (from/to still POST as GET params; `DateRange::fromRequest` unaffected) and presets produce valid `Y-m-d` links.
- Confirm legacy methods' new `Request $request` param didn't shadow anything and their routes still resolve.
- Confirm no service/query changed — export data must equal rendered data.
- Spot-check one multi-section page (inventory) end to end: 5 distinct keys, each streams the right table.

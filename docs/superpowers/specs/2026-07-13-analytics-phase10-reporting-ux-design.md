# Analytics Phase 10 — Reporting UX (shared date-range + CSV export) — Design

**Date:** 2026-07-13
**Status:** Approved (design)

## Summary

Phase 10 is the cross-cutting reporting-UX layer over the statistics pages. Two
deliverables:

1. **Shared date-range control with presets** — one reusable Blade component
   replacing the duplicated `from`/`to` `<form>` markup on the 7 date-range
   pages, plus quick-preset links (Last 7 / 30 / 90 days, This month, This year).
2. **CSV export on all 11 statistics pages** — a per-section "Export CSV" link
   that streams the section's data as a CSV, preserving the current date range.

No new tables, no service changes. This is presentation + a small support class.

## Existing convention (reused)

`Modules\Admin\...\OrderController::exportCsv` already streams CSV via
`response()->stream($callback, 200, $headers)` with `Content-Type: text/csv` +
`Content-Disposition: attachment` and `fputcsv` to `php://output`. Phase 10
factors that shape into a shared `CsvExporter` and reuses it everywhere.

## The 11 pages

- **7 date-range analytics pages** (already take `DateRange::fromRequest`):
  funnel(+attribution), promotions, profit, payments, returns, fulfillment,
  inventory. These get the date-range component AND per-section export.
- **4 legacy pages** (no date range today): users, vendors, products, orders.
  These get export only (whole-current-snapshot); no date component. Their
  controller methods gain a `Request $request` param solely to read `export`.

## Component 1 — CsvExporter support class

`Modules\Admin\Support\CsvExporter`:

```
public static function stream(string $filename, array $headers, iterable $rows): StreamedResponse
```

- `$headers`: array of column-label strings (the CSV header row).
- `$rows`: iterable of arrays of scalar cell values, in the same column order.
- Streams with the same HTTP headers as the existing `exportCsv` (Content-Type
  `text/csv`, `Content-Disposition: attachment; filename=$filename`, no-cache).
- **CSV-injection safe**: each cell is sanitized before write — a value whose
  first character is one of `= + - @`, tab, or CR gets a leading apostrophe so a
  spreadsheet doesn't evaluate it as a formula. Vendor/product names, reasons,
  campaign strings are vendor/user-supplied, so this matters.
- Uses `fputcsv` to `php://output` inside the stream callback.

## Component 2 — date-range-filter Blade component

`resources/views/components/admin/date-range-filter.blade.php`, used as
`<x-admin.date-range-filter :from="$from" :to="$to" />`:

- The existing `from`/`to` date inputs + Apply button (same markup the pages use
  now), so it is a drop-in replacement.
- **Preset links** below the inputs: "7 days", "30 days", "90 days", "This
  month", "This year" — each an `<a href>` to the current path with computed
  `from`/`to` query params (Carbon `now()`-relative, `Y-m-d`). The active preset
  isn't specially highlighted (YAGNI) — plain links.
- Presets link to `url()->current()` with just `from`/`to` (dropping any stale
  `export`/page params), so clicking a preset always lands on the HTML view.

## Component 3 — per-section export links

Each exportable table/section in a blade gets a small link:

```blade
<a href="{{ request()->fullUrlWithQuery(['export' => 'SECTION_KEY']) }}">Export CSV</a>
```

`fullUrlWithQuery` preserves the current `from`/`to` so the CSV matches what's on
screen. `SECTION_KEY` identifies which dataset to stream.

## Component 4 — controller export handling

Each statistics method, at the top (after building `$range` where applicable):

```php
if ($export = $request->query('export')) {
    return $this->export<Page>($export, $range);   // or ($export) for legacy
}
```

A small private `export<Page>` (or an inline `match`) maps the `export` key to
`[$filename, $headers, $rows]` and returns `CsvExporter::stream(...)`. An
unknown key falls through to a 404 (`abort(404)`) — no silent empty file.

The `$rows` are built from the SAME service calls the page already makes (the
data is identical to the rendered table), just flattened to ordered arrays.

### Export sections per page (keys + columns)

- **funnel**: `funnel` (stage, actors), `attribution_channel` (channel, placed_orders, placed_value), `attribution_campaign` (campaign, placed_orders, placed_value).
- **promotions**: `coupons` (code/name, uses, discount_total, ...), `promotions` (name, uses, discount_total, ...) — columns mirror the rendered tables.
- **profit**: `profit` — the summary rows (basis label, revenue, vendor earnings, platform profit, total owed) as label/value rows.
- **payments**: one key per rendered table (e.g. `status_breakdown`, `methods`, `failure_reasons`) — columns mirror the PaymentAnalyticsService output the page renders.
- **returns**: `by_reason` (reason, count) and `summary` (metric, value).
- **fulfillment**: `sla` (stage, count, avg_hours, median_hours, p90_hours) and `cancellations` (reason, count).
- **inventory**: `valuation_by_category` / `valuation_by_vendor` (name, units, value_at_cost, value_at_retail), `reorder` (sku, product, stock, vendor, is_out), `dead_stock` (sku, product, stock, value_at_cost, vendor), `expiring` (sku, product, expiry_date, stock, value_at_cost, vendor).
- **legacy users/vendors/products/orders**: one key per principal table already
  rendered on the page (e.g. users → `top_customers`, `popular_categories`;
  products → `top_products`, `stock_status`; etc.). The implementer reads each
  method + blade and exports the collections it already builds — columns mirror
  the rendered table headers.

The exact column set per section = whatever that table already shows. Where a
service returns a Collection of arrays (Phases 2–9), the row map is a direct
projection of those arrays.

## Error handling

- Bad `export` key → `abort(404)`.
- Empty dataset → a CSV with just the header row (valid, not an error).
- Date range invalid → `DateRange::fromRequest` already falls back to the default
  window; export honors whatever range resolves.

## Testing

- `CsvExporterTest` (unit): asserts the streamed content has the header row, a
  data row in column order, and that a formula-injection cell (`=1+1`) is
  neutralised (leading apostrophe). Asserts the `text/csv` + attachment headers.
- Per page-group, a feature test hitting `?export=<key>`: asserts 200,
  `Content-Type: text/csv`, `Content-Disposition: attachment`, and that the
  streamed body's first line is the expected header row. One representative key
  per page is enough (the mapping is mechanical); plus one `?export=bogus` → 404.

## Out of scope

- PDF/XLSX export (CSV only).
- Charting/visual changes beyond the date presets.
- Adding date ranges to the 4 legacy pages (they export current snapshots).
- Changing any analytics service or its numbers.
- Client (web/Flutter) UI.
- Async/queued export for very large tables (streamed synchronous is fine at
  current scale; the list services already cap at 200 rows where relevant).

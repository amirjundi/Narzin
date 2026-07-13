# Analytics Phase 9 — Inventory Analytics — Design

**Date:** 2026-07-13
**Status:** Approved (design)

## Summary

Phase 9 of the admin/marketing analytics roadmap. A new admin **Inventory** page
with four report-only views the existing Products-statistics page lacks:

1. **Inventory valuation** — units on hand + value at cost (Σ stock×cost) and at
   retail (Σ stock×price), overall and broken down by category and by vendor.
2. **Reorder worklist** — actionable list of low/out-of-stock active variants
   (SKU, product name, stock, vendor), worst-first, threshold from config.
3. **Dead stock** — active variants with stock on hand but ZERO sales in the
   selected window (capital in non-moving goods), by tied-up cost value.
4. **Expiring stock** — active variants with `expiry_date` within N days and
   stock > 0, with value-at-risk (uses the existing `expiry_date` column).

All read-only over `product_variants` + `products` + `order_items` + `orders`
(+ `categories`/`vendors` for breakdowns). **No new tables.** Follows the
established conventions: `Modules\Admin\Services`, `DateRange`, portable SQL,
Blade `admin::statistics.*` page + sidebar link. It does NOT duplicate the
existing `statistics/products` page (stock-status counts, top sellers, category
/price/variant distributions stay there).

## Data model (reused)

- `product_variants`: `product_id`, `stock` (int), `cost`, `price`, `sku`,
  `expiry_date` (nullable date), `is_active`, `is_out_of_stock`.
- `ProductVariant belongsTo Product`; `Product belongsTo Vendor` (`vendor_id`) +
  `belongsTo Category` (`category_id`). Variant→vendor is 2 hops via product.
- Product display names: `name_arabic` / `name_german` (no English) — the blade
  shows `name_german` with `name_arabic` fallback, matching existing pages.
- `order_items`: `product_variant_id`, `quantity`, `vendor_id`, `order_id`
  (`belongsTo Order`). Sales velocity = join order_items→orders, filter
  `orders.created_at` in window.

## Time basis

Valuation / reorder / expiring are **point-in-time** (current stock now).
**Dead stock is window-dependent** — "no sales in the selected date range."
The page carries a date-range filter that affects ONLY the dead-stock view;
a note states this so the other three aren't misread as ranged.

## Config

Add to `Modules/Telemetry/config/config.php`:
- `low_stock_threshold` = `env('LOW_STOCK_THRESHOLD', 5)` — variants with
  `stock <= threshold` (includes 0) appear on the reorder worklist.
- `expiry_days_ahead` = `env('EXPIRY_DAYS_AHEAD', 30)` — expiry window.

(The existing Products page hardcodes `stock < 10` for its "Low Stock" bucket;
Phase 9 does NOT touch that — the reorder worklist is a separate, configurable
view. The two thresholds are independent by design.)

## Component 1 — InventoryService

`Modules\Admin\Services\InventoryService`, read-only. Portable SQL only:
`SUM(stock*cost)` / `SUM(stock*price)` (arithmetic in SQL is portable),
`COALESCE`, `groupBy`; no DB-specific `CASE "string"` or median functions. Money
folding in single grouped queries (no N+1). Methods:

- **`valuation(): array`** →
  `['total_units'=>int, 'value_at_cost'=>float, 'value_at_retail'=>float,
    'potential_margin'=>float, 'by_category'=>Collection<['name','units','value_at_cost','value_at_retail']>,
    'by_vendor'=>Collection<same>]`.
  Over `product_variants` where `is_active=1` (only sellable stock counts —
  stated on page). `potential_margin = value_at_retail - value_at_cost`.
  Category/vendor breakdowns join through `products`; group by category/vendor
  id + name (ONLY_FULL_GROUP_BY-safe); null category/vendor → `(none)` via COALESCE.

- **`reorderWorklist(): Collection`** → active variants with
  `stock <= config('telemetry.low_stock_threshold',5)`, each
  `['sku','product_name_arabic','product_name_german','stock','vendor_name','is_out']`
  (`is_out = stock<=0`), ordered `stock asc` then product name. Join products
  (name) + vendors (name). Cap the list at a sane limit (e.g. 200) to protect
  the page; note if truncated.

- **`deadStock(DateRange $range): Collection`** → active variants with `stock>0`
  whose `id` is NOT in the set of `order_items.product_variant_id` for orders
  with `orders.created_at` in range. Each
  `['sku','product_name_arabic','product_name_german','stock','value_at_cost','vendor_name']`,
  ordered by `value_at_cost desc` (biggest tied-up capital first). Cap at 200.
  Implement as `whereNotIn('id', <subquery of sold variant ids in range>)` — two
  queries at most (subquery + main), no per-variant query.

- **`expiringStock(): Collection`** → active variants with `stock>0`,
  `expiry_date` not null, `expiry_date <= now()+config('telemetry.expiry_days_ahead',30) days`,
  each `['sku','product_name_arabic','product_name_german','stock','expiry_date','value_at_cost','vendor_name']`,
  ordered `expiry_date asc` (soonest first). Cap at 200.

## Component 2 — admin page

- `StatisticsController::inventoryStatistics(Request)` → `DateRange::fromRequest`,
  calls the service, `return view('admin::statistics.inventory', [...])`.
- Route `statistics/inventory` name `statistics.inventory` (same admin-auth group).
- Blade `admin::statistics.inventory` (mirror the returns/fulfillment page shell):
  - Valuation cards: total units, value@cost, value@retail, potential margin;
    plus by-category and by-vendor tables.
  - Reorder worklist table (SKU, product, stock, vendor; OUT rows flagged red,
    LOW amber) with the threshold shown.
  - Dead-stock table (with the date-range form; caveat that the range only
    affects this view).
  - Expiring-stock table (with the N-days window shown).
  - Money rendered to 2dp; product name via `name_german ?? name_arabic`.
- Sidebar link in `resources/views/components/admin/sidebar.blade.php` (app-level),
  in the Reports section next to the other statistics links.

## Verification

`InventoryServiceTest` (RefreshDatabase, manual seeding like the Phase 8 tests —
`Order::factory()` isn't registered; seed products/variants/order_items directly):
- valuation: seed variants with known stock/cost/price → assert total_units,
  value_at_cost, value_at_retail, potential_margin, and one category/vendor bucket.
- reorderWorklist: seed variants above/below threshold → assert only ≤threshold
  active ones appear, ordered worst-first, `is_out` correct at stock 0.
- deadStock: seed a variant sold in-window (excluded) and one not sold (included);
  assert the unsold one appears and the sold one doesn't.
- expiringStock: seed variants with expiry inside/outside the window and a null
  expiry → assert only in-window stock>0 ones appear, soonest first.

## Out of scope

- New stock-movement/inventory-ledger table or historical stock trend (current
  state + sales-in-window only; a movement log would be a separate project).
- Per-product reorder points (single global `low_stock_threshold`).
- Editing stock / reorder actions from the page (report-only).
- Touching the existing `statistics/products` page or its hardcoded `<10` bucket.
- Client (web/Flutter) UI.

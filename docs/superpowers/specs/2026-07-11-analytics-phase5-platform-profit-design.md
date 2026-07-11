# Analytics Phase 5 — Platform Profit — Design

**Date:** 2026-07-11
**Status:** Approved (design)

## Summary

Report the platform's profit on product sales — what the platform keeps after
paying vendors — shown for both **placed** and **paid** orders, plus a headline
of the **total currently owed to vendors**. A new admin **Profit** page. **No
migration and no capture change**: every input is already stored.

## Why no new capture (roadmap correction)

The roadmap assumed Phase 5 needed a product `cost_price` column and an
`order_items.unit_cost` snapshot. Investigation shows that's unnecessary:
- `product_variants.cost` already exists and is captured (`required|numeric|min:0`
  on product create/update).
- More importantly, this phase measures **platform** profit (per the product
  owner's decision), not COGS margin — and the marketplace already computes and
  **stores per-item vendor economics** on `order_items`:
  `vendor_base_subtotal`, `vendor_commission_amount`, `vendor_discount_absorbed`,
  `vendor_earning` (persisted in `placeOrder` via `VendorEarningCalculator`).
- "How much we owe the shops" **already exists** as the **Vendor Payouts** admin
  page (`VendorPayoutController@index/show`, backed by
  `VendorLedgerService::payableBalance`). Phase 5 links to it, does not rebuild
  it.

So Phase 5 is pure reporting over stored data.

## Money model (exact identities)

Per order (products only; shipping/wallet excluded — shipping is a passthrough,
wallet is a payment method not revenue):

- **revenue** = `orders.price_after_discount` (product total after order discount)
- **vendor_earnings** = `SUM(order_items.vendor_earning)` for the order
  (`COALESCE(vendor_earning, 0)` — see caveat)
- **platform_profit** = `revenue − vendor_earnings`
- **margin%** = `platform_profit / revenue` (0 when revenue is 0)

Aggregated over a `DateRange` on `orders.created_at`, for two sets:
- **placed** — all orders in range
- **paid** — orders in range with `payment_status = 'completed'` (the success
  value; `not_paid`/`failed`/`expired`/`processing` are the others)

**Total owed to vendors** (headline) = `SUM(vendor_transactions.amount)` across
all vendors — the current outstanding ledger balance (earning credited on
payment, payouts/adjustments subtracted). This is a running balance, **not**
range-bound. It reconciles with the paid set because the ledger only credits on
payment.

**Supporting figure:** commission collected = `SUM(vendor_commission_amount)`
(directly stored, unambiguous) — shown for transparency.

### Caveat (stated on the page)

Orders placed before the vendor-earning system (2026-06-28) have
`vendor_earning = NULL`; `COALESCE(...,0)` treats their vendor cost as 0, which
**overstates** platform profit for those orders. The default range is the last
30 days, so current data is correct; a note flags this for historical ranges.

## Component 1 — ProfitService

`Modules/Admin/app/Services/ProfitService.php`, consuming `DateRange`.

`summary(DateRange $range): array` returns:

```
[
  'placed' => ['revenue'=>, 'vendor_earnings'=>, 'platform_profit'=>, 'margin'=>, 'orders'=>],
  'paid'   => ['revenue'=>, 'vendor_earnings'=>, 'platform_profit'=>, 'margin'=>, 'orders'=>],
  'commission_collected' => float,   // paid set
  'total_owed_to_vendors' => float,  // current ledger balance, all vendors
]
```

- `vendor_earnings` for a set = `SUM(COALESCE(order_items.vendor_earning,0))`
  over that set's orders (join order_items to orders, filter by range/status).
- `revenue` = `SUM(orders.price_after_discount)` over the set (guard: an order
  with null price_after_discount coalesces to `total_amount`? No — keep it
  simple: `COALESCE(price_after_discount, total_amount)` so pre-discount-column
  orders still count their full revenue).
- money rounded to 2; margin rounded to 4.

## Component 2 — Profit page (new)

- Route: `Route::get('statistics/profit', [StatisticsController::class,
  'profitStatistics'])->name('statistics.profit');` in the `admin.auth` group
  (next to the other `statistics/*` routes).
- Controller: `StatisticsController::profitStatistics(Request $request)` builds a
  `DateRange`, calls `ProfitService::summary`, returns
  `view('admin::statistics.profit', …)`.
- View: `Modules/Admin/resources/views/statistics/profit.blade.php`, `<x-admin-layout>`,
  shared date-range filter. Shows:
  - headline cards: platform profit (paid), total owed to vendors (with a link to
    `route('vendor.payouts...')` / the existing payout page);
  - a placed-vs-paid table: revenue, vendor earnings, platform profit, margin;
  - commission collected;
  - the historical-null caveat note.
- **Sidebar:** add a "Profit" link in `resources/views/components/admin/sidebar.blade.php`
  next to the other statistics links.

## Testing

- `ProfitService::summary`: seeded orders + order_items with vendor_earning
  produce correct placed/paid revenue, vendor_earnings, platform_profit, margin;
  paid set excludes non-`completed` orders; null vendor_earning coalesces to 0;
  total_owed_to_vendors sums `vendor_transactions.amount`; zero orders → no
  divide-by-zero.
- Profit page: an admin gets 200 and sees the profit headline + placed/paid
  table; a guest is redirected.

## Out of scope

- Per-vendor profit/payables breakdown — already on the Vendor Payouts page.
- Shipping/wallet P&L. Paid-only reconciliation audit. CSV (Phase 10).
- Rewriting the old hardcoded product/order stats pages.

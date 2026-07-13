# Analytics Phase 8 — Fulfillment SLA + Cancellation Reasons — Design

**Date:** 2026-07-13
**Status:** Approved (design)

## Summary

Phase 8 of the admin/marketing analytics roadmap. Two deliverables:

1. **Fulfillment SLA reporting** — how long orders take to move through
   fulfillment (confirmed→shipped, shipped→delivered, placed→shipped), plus
   the share breaching an SLA threshold. Read-only, computed entirely from the
   existing `order_audits` table (every status transition already logs
   `old_order_status`, `new_order_status`, `created_at`). **No new table, no
   `shipped_at`/`delivered_at` columns** — the audit trail already has the timestamps.

2. **Cancellation reasons** — capture *why* orders are cancelled (none is
   captured today; admin cancel only writes free-text `notes`) and report the
   breakdown. Adds ONE nullable column `orders.cancellation_reason`.

Follows the established analytics conventions: `Modules\Admin\Services`,
`DateRange` value object, portable SQL (`COALESCE`, group-by; medians computed
in PHP), a Blade `admin::statistics.*` page + sidebar link.

## Cancellation reason vocabulary

`orders.cancellation_reason` (nullable string, no DB enum — app-enforced, matches
how `order_status` is a plain string). Values:

- **Admin-chosen** (dropdown on the order status-update form, only meaningful
  when status set to `cancelled`): `out_of_stock`, `customer_request`,
  `fraud_suspected`, `pricing_error`, `other`.
- **System auto-labeled** at the code paths that already cancel orders:
  - `return_refund` — `OrderRefundService::refundWholeOrder` (refund/return path).
  - `customer_request` — customer self-cancel API (`CheckoutController`, the
    user-triggered `order_cancelled` path).
  - `payment_failed` — payment webhook failure path (`CheckoutController`, the
    `payment_status=failed` branch).

Cron/expiry uses `order_status='expired'` (a distinct status, not `cancelled`) —
left untouched; expired orders are not "cancellations."

### Cancellation status spelling

The payment-webhook-failure path writes `order_status='canceled'` (one L) while
every other path and the admin validation use `'cancelled'` (two L). This is a
pre-existing inconsistency. Phase 8:
- **Fixes the write** at that one site to `'cancelled'` (two L) so all
  cancellations share one status value — it is unambiguously a typo (admin
  validation only accepts two-L, so a one-L order can never transition again).
- **Defensively, the report still counts both spellings** (`whereIn(['cancelled','canceled'])`)
  so historical one-L rows aren't dropped from the breakdown.

## Component 1 — migration + model

- Migration: `orders.cancellation_reason` nullable string, indexed (report groups by it).
- `Order::$fillable` gains `cancellation_reason`.

## Component 2 — capture wiring (4 sites)

1. **Admin `OrderController::updateStatus`** — validation gains
   `cancellation_reason` (nullable, `in:out_of_stock,customer_request,fraud_suspected,pricing_error,other`);
   when the new status is `cancelled`, persist it onto the order. Non-cancel
   updates ignore it. Include it in the OrderAudit `data` for traceability.
2. **Admin order `show.blade.php`** — add a `cancellation_reason` `<select>` to
   the existing status-update form (the 5 admin values + a blank default). It is
   only consumed by the backend when `cancelled` is chosen; rendering it always
   is fine (keeps the form dumb).
3. **`OrderRefundService::refundWholeOrder`** — set `cancellation_reason='return_refund'`
   in the same `$order->update([...])` that sets `order_status='cancelled'`.
4. **`CheckoutController`** — customer self-cancel path sets
   `cancellation_reason='customer_request'`; payment-webhook-failure path sets
   `cancellation_reason='payment_failed'` (alongside the spelling fix).

All capture writes are best-effort in spirit but here they piggyback on writes
that already happen in the same transaction — no new failure surface.

## Component 3 — FulfillmentService

`Modules\Admin\Services\FulfillmentService`, read-only over `order_audits` + `orders`.

- **`slaSummary(DateRange $range): array`** — for orders created in range,
  derive per-order stage timestamps from `order_audits` (earliest audit row per
  order whose `new_order_status` is `confirmed` / `shipped` / `delivered`), then
  compute stage durations in **hours**:
  - `confirm_to_ship` = shipped − confirmed (fallback confirmed→order.created_at when no explicit confirmed row)
  - `ship_to_deliver` = delivered − shipped
  - `placed_to_ship` = shipped − order.created_at
  For each stage return `{count, avg_hours, median_hours, p90_hours}` — **median
  and p90 computed in PHP** (sort the array, pick the index) for cross-DB
  portability. Also `breach_rate` = share of shipped orders whose `placed_to_ship`
  exceeds `config('telemetry.fulfillment_sla_hours', 48)`.
  Pull the audit rows with ONE query (`whereIn order_id`, ordered), fold in PHP —
  no per-order query (avoid N+1).
- **`cancellationsByReason(DateRange $range): Collection`** — orders in range with
  `order_status IN ('cancelled','canceled')`, grouped by
  `COALESCE(cancellation_reason,'(unspecified)')`, `{reason, count}` desc.
  Plus a `cancellation_rate` = cancelled / total orders (returned from a small
  companion summary, or folded into the same array the controller passes).

Basis note (consistent with prior phases): SLA is measured over orders *created*
in the window; an order placed near the window's end may not have shipped yet —
it simply won't contribute a `shipped` timestamp. Stated on the page.

## Component 4 — admin page

- `StatisticsController::fulfillmentStatistics(Request)` → `DateRange::fromRequest`,
  calls the service, `return view('admin::statistics.fulfillment', [...])`.
- Route `statistics/fulfillment` name `statistics.fulfillment` (admin group).
- Blade `admin::statistics.fulfillment` mirroring the returns/profit pages:
  date-range form, SLA cards (avg/median/p90 per stage, breach rate), a
  cancellations-by-reason table, and the basis caveat.
- Sidebar link in `resources/views/components/admin/sidebar.blade.php` (the
  app-level sidebar, NOT the module views) next to the other statistics links.

## Verification

- `FulfillmentService` unit test (Laravel): seed a couple of orders + audit rows
  with known transition timestamps → assert stage durations, median, p90, breach
  rate; seed cancelled orders with reasons → assert the breakdown and that both
  `cancelled`/`canceled` spellings are counted.
- Capture: a feature/unit test that admin `updateStatus` to `cancelled` with a
  reason persists `cancellation_reason`; and that a non-cancel status update
  ignores it.

## Out of scope

- Per-item fulfillment timing (whole-order only).
- Client (web/Flutter) UI — this is admin analytics + backend capture only.
- Backfilling `cancellation_reason` for historical cancellations (stays
  `(unspecified)`; the report tolerates it).
- Changing the expiry/`expired` flow.

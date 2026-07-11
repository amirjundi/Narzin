# Analytics Phase 3 — Traffic Attribution — Design

**Date:** 2026-07-11
**Status:** Approved (design)

## Summary

Answer *"which channel/campaign drives revenue?"* by snapshotting a session's
UTM attribution onto each order at placement, then reporting revenue / orders /
AOV grouped by channel and campaign. The report is a new **Attribution** section
on the existing admin **Funnel** page (Phase 2), reusing its `DateRange` filter.

Now unblocked by the web tracking hooks: the web sends UTM to `/track/session`
(→ `visit_sessions`) and `session_id` to `place-order`, so `placeOrder` can look
up the session and copy its UTM onto the order.

## Data flow & caveat

`visit_sessions.utm_*` (set first-touch from `/track/session`) → snapshot onto
`orders` at placement (keyed by the order's `session_id`) → grouped in the report.

Populates only for orders placed **after this ships** by users who arrived with
UTM params, via web (Flutter hooks later). Direct/organic/no-session orders show
as **"(none)"** — the report labels them honestly, never drops them.

## Component 1 — Order attribution snapshot

### Migration — `orders` add columns (all nullable):
| column | type |
|--------|------|
| attributed_session_id | string nullable |
| utm_source | string nullable |
| utm_medium | string nullable |
| utm_campaign | string nullable |
| utm_term | string nullable |
| utm_content | string nullable |

Index `utm_source` and `utm_campaign` (grouped/filtered in the report).

### Snapshot in `CheckoutController::placeOrder`
After the order is created and committed (right after the existing `placed`
`CheckoutEvent` capture from Phase 1, where `$order` and the request's
`session_id` are in scope), copy the session's UTM onto the order:

- Read `session_id` from `$request->input('session_id')` (now sent by web).
- If present, look up `VisitSession::where('session_id', $sessionId)->first()`.
- If found, set `orders.attributed_session_id` + the five `utm_*` columns from
  the session, and save.
- **Best-effort and non-blocking:** wrap in `try/catch` and log — attribution
  capture must never break checkout (same discipline as Phase 1 capture).

This is a **snapshot**: the order keeps the attribution it had at purchase even
if the session is later reused with different UTM. Immutability is inherent (the
values are copied, never joined at read time).

## Component 2 — AttributionService

`Modules/Admin/app/Services/AttributionService.php`, consuming `DateRange`.

- `byChannel(DateRange $range): Collection` — orders in range grouped by
  `(utm_source, utm_medium)`; each row: `source`, `medium`, `orders` (count),
  `revenue` (`SUM(total_amount)`), `aov` (`revenue/orders`). Null source/medium
  → `'(none)'`.
- `byCampaign(DateRange $range): Collection` — grouped by `utm_campaign`; each
  row: `campaign`, `orders`, `revenue`, `aov`. Null campaign → `'(none)'`.

Both sorted by `revenue` desc. Revenue uses `orders.total_amount` (the same
figure the existing order statistics use), in the stored price currency. Money
rounded to 2 decimals. Coalesce null UTM to `'(none)'` in PHP (portable), not a
DB `COALESCE` (mirrors the Phase 2 portability decision), or use a groupBy over
the fetched rows.

## Component 3 — Reporting (extend the Funnel page)

- `StatisticsController::funnelStatistics` additionally builds
  `$attribution = ['byChannel' => …, 'byCampaign' => …]` (via `AttributionService`
  over the same `DateRange` already constructed) and passes it to the view.
- `Modules/Admin/resources/views/statistics/funnel.blade.php` gains an
  **Attribution** section: two tables (by channel, by campaign) showing
  orders / revenue / AOV, with an honest empty/`(none)` handling and a short
  note that attribution fills in as UTM-tagged traffic places orders.

No new route, controller, or page — it's a section on the existing Funnel page.

## Testing

- Migration: `orders` has the six new columns.
- Snapshot: placing an order whose `session_id` maps to a `visit_session` with
  UTM copies those `utm_*` + `attributed_session_id` onto the order; an order
  with no `session_id` (or no matching session) leaves them null; a capture
  failure does not break `placeOrder` (returns success).
- `AttributionService::byChannel` / `byCampaign`: revenue/orders/AOV aggregate
  correctly; orders with null UTM group under `'(none)'`; out-of-range orders
  excluded.
- Funnel page: an admin sees the Attribution tables (channel + campaign headers)
  render 200.

## Cross-cutting notes

- **Snapshot immutability** is the whole point — never compute attribution by
  joining `orders` to `visit_sessions` at read time; always read the order's own
  copied columns.
- **Performance:** the report is two range-bounded `GROUP BY` aggregates over
  `orders` (indexed on `utm_source`/`utm_campaign`); the snapshot adds one
  indexed lookup + one update per placed order.
- **No fabrication:** untagged orders are shown as `(none)`, not hidden.
- Out of scope: multi-touch attribution, per-campaign order drill-down, CSV
  (Phase 10), Flutter session/UTM hooks.

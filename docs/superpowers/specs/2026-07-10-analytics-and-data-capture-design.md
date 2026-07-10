# Analytics & Data Capture — Design

**Date:** 2026-07-10
**Status:** Approved (design)

## Summary

The admin panel today reports revenue, orders, top products/vendors, a retention
cohort, and (as of 2026-07-04) product views via the Telemetry module. It is
missing the behavioral, financial, and operational data that an admin and a
marketing team need to actually run the business: conversion funnel, abandoned
carts, traffic attribution, search demand, coupon/promotion effectiveness,
profit/margin, payment success, returns, fulfillment SLA, and inventory health.

The central insight driving the sequencing: **revenue can be recomputed for any
past date, but behavioral data not captured is lost forever.** So the first
phase stands up the capture layer; reporting phases read from it afterward.

This is delivered **backend-first** — most capture is server-side and needs zero
client work. The only client footprint is **two thin hooks**: a cart-event POST
and a UTM/referrer stamp on session start. New reporting extends the existing
server-rendered `admin::statistics.*` Blade pages (plus a CSV-download button
where finance/marketing need raw rows). Behavioral data is stored in **typed
tables per event**, not a generic event log.

## Scope

Ten phases, each independently shippable. Build order = dependency + leverage.
Phase 10's date-range query service is seeded in Phase 1 so later phases inherit
it.

| # | Phase | Depends on |
|---|-------|-----------|
| 1 | Capture foundation (sessions, cart/checkout/search events) | — |
| 2 | Conversion funnel + abandoned cart | 1 |
| 3 | Traffic attribution | 1 |
| 4 | Coupon / promotion performance | — |
| 5 | Profit & margin | — |
| 6 | Payment analytics | — |
| 7 | Returns & refunds (feature, not just a report) | — |
| 8 | Fulfillment SLA + cancellation reasons | — |
| 9 | Inventory analytics | 5 |
| 10 | Reporting UX: date-range + CSV on all pages | 1 (service seed) |

**Out of scope / deferred:** abandoned-cart *recovery email sending* (thin
add-on after Phase 2); a separate SPA/React analytics dashboard (Blade only);
JSON analytics APIs.

## Conventions

- New capture lives in the existing **`Telemetry` module** (already owns
  `user_product_views` and the `session_id` concept). Transactional tables
  (`coupon_redemptions`, `payment_attempts`, `order_returns`) live in the module
  that owns their domain (Checkout).
- All money columns: `decimal(12,2)`, stored in the price currency (matching
  `order_items.subtotal` today; exchange rate is display-only, per the vendor
  payouts spec).
- Every capture write is **best-effort and non-blocking**: a tracking failure
  must never break checkout, search, or payment. Wrap writes in try/catch that
  logs and continues.
- Timestamps use `occurred_at` (the event's own clock) separate from
  `created_at` (row insert).

---

## Phase 1 — Capture foundation

### `visit_sessions`
Anchors attribution and ties anonymous behavior to a user once they log in.

| column | type | notes |
|--------|------|-------|
| id | bigint PK | |
| session_id | string, unique, indexed | client-generated UUID; the join key everywhere |
| user_id | bigint nullable, FK users | set/backfilled on login |
| utm_source, utm_medium, utm_campaign, utm_term, utm_content | string nullable | from first landing |
| referrer | string nullable | document.referrer / deep-link referrer |
| landing_url | string nullable | |
| first_seen_at | timestamp | |
| last_seen_at | timestamp | touched on activity |
| timestamps | | |

### `cart_events`
| column | type | notes |
|--------|------|-------|
| id | bigint PK | |
| session_id | string, indexed | |
| user_id | bigint nullable | |
| product_id | bigint FK | |
| variant_id | bigint nullable FK | |
| action | enum(add, remove, update) | |
| quantity | int | |
| unit_price | decimal(12,2) nullable | for cart-value analytics |
| occurred_at | timestamp, indexed | |

### `checkout_events`
| column | type | notes |
|--------|------|-------|
| id | bigint PK | |
| session_id | string, indexed | |
| user_id | bigint nullable | |
| step | enum(checkout_start, address, shipping, payment, placed) | |
| order_id | bigint nullable FK | set on `placed` |
| occurred_at | timestamp, indexed | |

### `search_logs`
| column | type | notes |
|--------|------|-------|
| id | bigint PK | |
| session_id | string nullable, indexed | |
| user_id | bigint nullable | |
| query | string | raw |
| normalized_query | string, indexed | lowercased/trimmed for aggregation |
| results_count | int | 0 ⇒ zero-result search (demand gap) |
| occurred_at | timestamp, indexed | |

### Capture points
- **search_logs** — inside the product search controller (the case-insensitive
  keyword search shipped in `c8ac0e1`). Backend-only.
- **checkout_events** — `CheckoutController`: emit `checkout_start` when the
  checkout/invoice endpoint is hit, `placed` on successful order creation.
  Backend-only.
- **cart_events** — thin `POST /api/v1/track/cart` (`TrackingController@cart`),
  called by React + both Flutter apps on add/remove. **Client hook #1.**
- **visit_sessions** — session bootstrap: clients send `session_id` + UTM/referrer
  on first request (header or `POST /api/v1/track/session`). React reads
  `window.location` query params; Flutter reads deep-link params.
  **Client hook #2.** `user_id` backfilled on login by matching `session_id`.

### Date-aware query service (seed for Phase 10)
Introduce `Modules\Admin\Services\StatisticsQueryService` that accepts a
`DateRange` (from/to, default last 30 days). Migrate the existing hardcoded
`subDays(30)` / `subMonth()` queries in `StatisticsController` into it
incrementally. Later phases and Phase 10 build on this.

### Tests (Phase 1)
- Search endpoint writes a `search_logs` row with correct `results_count`
  (including a zero-result case).
- Placing an order writes `checkout_start` + `placed` `checkout_events`.
- `POST /track/cart` writes a `cart_events` row; malformed payload returns 2xx
  and writes nothing (non-blocking contract).
- Login backfills `user_id` onto the matching `visit_sessions` row.

---

## Phase 2 — Conversion funnel + abandoned cart

No new tables. Adds `FunnelService` computing the stage counts and rates:

```
sessions → product_view → cart_add → checkout_start → placed
```
from `user_product_views`, `cart_events`, `checkout_events`. Conversion rate =
placed / sessions; drop-off per stage.

**Abandoned cart** = a `session_id` with ≥1 `cart_add` and no `placed`
`checkout_event` within N hours (N configurable, default 24). Report lists
abandoned sessions with cart value (`sum(unit_price × quantity)` of last cart
state), user (if known), and age.

Blade: funnel bar/step chart + abandoned-cart table on the orders statistics
page (or a new `statistics.funnel` view).

Tests: funnel counts from seeded events; abandoned-cart query excludes sessions
that later placed and respects the N-hour window.

---

## Phase 3 — Traffic attribution

At order placement, snapshot the session's attribution onto the order so
historical reports are stable even if the session is later reused:

`orders` += `attributed_session_id` (nullable), `utm_source`, `utm_medium`,
`utm_campaign` (nullable snapshot).

Report: revenue, order count, AOV grouped by channel (utm_source/medium) and by
campaign. Blade: a channel/campaign table + trend on the orders or vendors page.

Tests: placing an order copies the session UTM onto the order; revenue-by-channel
aggregates correctly.

---

## Phase 4 — Coupon / promotion performance

### `coupon_redemptions`
| column | type | notes |
|--------|------|-------|
| id | bigint PK | |
| coupon_id | bigint nullable FK | |
| promotion_id | bigint nullable FK | exactly one of coupon/promotion set |
| order_id | bigint FK | |
| user_id | bigint nullable | |
| discount_amount | decimal(12,2) | actual value granted |
| occurred_at | timestamp | |

Capture in `CheckoutController` where a coupon/promotion is applied
(backend-only). Report: redemptions per coupon/campaign, total discount cost,
orders and revenue that used a discount vs not.

Tests: applying a coupon at checkout writes one redemption with the right
`discount_amount`; report totals match.

---

## Phase 5 — Profit & margin

- `product_variants` += `cost_price` decimal(12,2) nullable (vendor/admin cost).
- `order_items` += `unit_cost` decimal(12,2) nullable — **snapshot** of cost at
  purchase time, so historical margin is stable when cost changes later.
- Thin admin/vendor form field to enter `cost_price` (backend + existing product
  form).

Reports add profit (`revenue − cost`) and margin % to orders/products/vendors
pages. Rows without a cost are shown as "cost not set" rather than counted as
100% margin.

Tests: placing an order snapshots `unit_cost` from the variant; margin excludes
null-cost items from the margin % and flags them.

---

## Phase 6 — Payment analytics

### `payment_attempts`
| column | type | notes |
|--------|------|-------|
| id | bigint PK | |
| order_id | bigint nullable FK | |
| gateway | string | e.g. `nass`, `wallet` |
| status | enum(initiated, success, failed) | |
| failure_reason | string nullable | gateway message/code |
| amount | decimal(12,2) | |
| occurred_at | timestamp, indexed | |

Capture in `NassPaymentService` (initiate) and the payment callback/verify
(success/failed). Backend-only. Report: success/failure rate, method mix,
top failure reasons.

Tests: a simulated success and a simulated failure each write the correct
`payment_attempts` row; success-rate aggregation is correct.

---

## Phase 7 — Returns & refunds (feature)

Larger than the others — adds a request/approve flow, not just a report.

### `order_returns`
| column | type | notes |
|--------|------|-------|
| id | bigint PK | |
| order_id | bigint FK | |
| order_item_id | bigint nullable FK | null ⇒ whole-order return |
| user_id | bigint FK | |
| reason | string | from a fixed reason list |
| status | enum(requested, approved, rejected, refunded) | |
| refund_amount | decimal(12,2) nullable | set on refund |
| requested_at, resolved_at | timestamp | |

Endpoints: customer requests a return; admin approves/rejects/refunds (refund
reuses the existing wallet-refund path from `handleExpiredButPaidOrder`).
Report: return rate overall and by product/vendor, reason breakdown, refund
totals.

Tests: request→approve→refund transitions; refund credits the wallet once
(idempotent); return-rate aggregation.

---

## Phase 8 — Fulfillment SLA + cancellation reasons

- `orders` += `cancellation_reason` (nullable), captured when an order is
  cancelled (fixed reason list).
- Ensure `orders` has `shipped_at` / `delivered_at` (add if absent; otherwise
  derive from `OrderAudit` status transitions, which already track before/after
  state).

Reports: median/avg time-to-ship and time-to-deliver, late-shipment rate
(> SLA threshold), cancellation-reason breakdown, per-vendor SLA.

Tests: status transition stamps the timestamp; SLA and cancellation aggregations.

---

## Phase 9 — Inventory analytics

No new tables (uses `product_variants.stock`, `order_items`, and `cost_price`
from Phase 5). Metrics:

- **Sell-through rate** = units sold / (units sold + on-hand) over the range.
- **Dead stock** = variants with stock > 0 and zero sales in the range.
- **Stockout frequency / lost demand** = `cart_add` events on variants at
  `stock = 0` (reuses Phase 1 cart events).
- **Inventory valuation** = `sum(stock × cost_price)`.

Blade: an inventory-health section on the products statistics page.

Tests: sell-through and dead-stock queries on seeded data; valuation excludes
null-cost variants and flags them.

---

## Phase 10 — Reporting UX (date-range + CSV)

- Every statistics page gets a **date-range picker** (from/to), wired through the
  `StatisticsQueryService` seeded in Phase 1. Removes the hardcoded
  "last 30 days" / "last month" windows.
- A **CSV export** button per page/section that streams the underlying rows
  (Blade controller action returning a streamed CSV response — no JSON API).

Tests: a query service call respects the supplied range; CSV export returns the
expected header + row count for a seeded range.

---

## Cross-cutting risks & decisions

- **Write volume.** `cart_events` / `search_logs` / `checkout_events` can grow
  fast. Mitigation: indexes on `(occurred_at)` and the join keys; a scheduled
  prune/rollup command can come later if needle moves — not built now (YAGNI).
- **Non-blocking capture.** Every capture write is try/caught; failures log and
  continue. Enforced by the Phase 1 "malformed payload still 2xx" test.
- **Guest → user stitching.** `session_id` is the spine; `user_id` is backfilled
  on login. Attribution is snapshotted onto the order so later session reuse
  can't rewrite history.
- **Cost data quality.** Margin/valuation depend on `cost_price` being entered.
  Reports flag null-cost items instead of silently assuming full margin.

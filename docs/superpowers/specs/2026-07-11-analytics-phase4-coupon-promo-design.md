# Analytics Phase 4 — Coupon / Promotion Performance — Design

**Date:** 2026-07-11
**Status:** Approved (design)

## Summary

Report how coupons and promotions perform: redemptions, discount given, placed
value driven, and AOV per coupon/promotion, plus overall discount penetration.
A new admin **Promotions** statistics page. **No client dependency and no new
table** — it aggregates existing order data, so it shows real numbers from day
one.

## Data reality (verified, no new capture needed)

Orders already record everything:
- `orders.coupon_id` / `orders.promotion_id` — at most one set per order
  (best-one-wins: `PromotionEvaluator` picks the winner; the loser is nulled).
- `orders.free_shipping_promotion_id` — separate free-shipping promo.
- `orders.total_amount` — pre-discount cart total (marked up).
- `orders.price_after_discount` — `total_amount − discount`.
- **Discount amount per order = `total_amount − price_after_discount`** (exact;
  the `discount_breakdown` column is dead/never populated — ignore it).
- `coupons.code`, `coupons.used` (counter); `promotions.name`.

So Phase 4 is a pure read-model. It joins `orders` to `coupons`/`promotions` and
aggregates — no schema change, no checkout change.

## Revenue basis (consistent with Phase 3)

"Placed value" = `SUM(orders.total_amount)` — gross placed-order value including
unpaid/cancelled, the same basis as the existing order stats and the Attribution
report. Labeled honestly as "Placed value", not settled revenue. Discount given
= `SUM(total_amount − price_after_discount)`.

## Component 1 — DiscountService

`Modules/Admin/app/Services/DiscountService.php`, consuming `DateRange`.

- `byCoupon(DateRange $range): Collection` — orders in range with a non-null
  `coupon_id`, grouped by coupon; each row:
  `code`, `coupon_id`, `redemptions` (order count), `discount_given`
  (`SUM(total_amount − price_after_discount)`), `placed_value`
  (`SUM(total_amount)`), `aov` (`placed_value / redemptions`). Join `coupons`
  for `code`; a coupon row deleted after use shows `code = '(deleted)'`.
- `byPromotion(DateRange $range): Collection` — orders with non-null
  `promotion_id`, grouped by promotion; each row: `name`, `promotion_id`,
  `redemptions`, `discount_given`, `placed_value`, `aov`. Join `promotions` for
  `name`; missing → `'(deleted)'`.
- `summary(DateRange $range): array` — headline penetration:
  `discounted_orders` (count with coupon_id OR promotion_id),
  `total_orders` (all in range), `discount_rate`
  (`discounted_orders / total_orders`, 0 when no orders),
  `total_discount` (`SUM(total_amount − price_after_discount)` over discounted
  orders).

All money rounded to 2. Rows sorted by `discount_given` desc (the cost lever
marketers care about). Range-bound on `orders.created_at` (consistent with the
other services). Coalesce missing joined names to `'(deleted)'` in PHP.

Rationale for grouping on the order's own `coupon_id`/`promotion_id` (not
`coupons.used`): `used` is a lifetime counter with no date dimension and counts
across all time; the order join gives date-ranged, revenue-linked redemptions.
Stored as a `rationale` on the discount-performance concept.

## Component 2 — Promotions page (new)

- Route: `Route::get('statistics/promotions', [StatisticsController::class,
  'promotionStatistics'])->name('statistics.promotions');` in
  `Modules/Admin/routes/web.php`, inside the `admin.auth` group next to the
  other `statistics/*` routes (~line 132).
- Controller: `StatisticsController::promotionStatistics(Request $request)` —
  builds a `DateRange`, calls `DiscountService::{byCoupon,byPromotion,summary}`,
  returns `view('admin::statistics.promotions', …)`.
- View: `Modules/Admin/resources/views/statistics/promotions.blade.php`, using
  `<x-admin-layout>` like the other stats pages, with the shared date-range
  filter (from/to GET). Shows:
  - a summary card (discount penetration: discounted orders / total, total
    discount given);
  - a **Coupons** table (code, redemptions, discount given, placed value, AOV);
  - a **Promotions** table (name, redemptions, discount given, placed value, AOV);
  - honest empty-states ("No coupon redemptions in this range.").
  - the same "Placed value = gross placed-order value, not settled revenue" note
    used on the attribution report.

## Testing

- `DiscountService::byCoupon`: orders with a coupon aggregate redemptions /
  discount_given / placed_value / aov correctly; discount = total_amount −
  price_after_discount; out-of-range excluded; orders with no coupon excluded.
- `byPromotion`: same for promotion_id.
- `summary`: discount_rate = discounted/total; total_discount correct; zero
  orders → 0 rate (no divide-by-zero).
- Deleted coupon/promotion → `'(deleted)'` label, row still counted.
- Promotions page: an admin gets 200 and sees the Coupons + Promotions headers;
  a guest is redirected; date range respected.

## Out of scope

- Free-shipping-promotion cost analysis (`free_shipping_promotion_id`) — the
  discount isn't a line amount on the order; deferred.
- Paid-only revenue filter (kept gross for consistency; a roadmap-wide decision).
- CSV export (Phase 10). Per-campaign redemption trends over time.

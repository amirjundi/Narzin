# Promotions Engine (v1) — Design

**Date:** 2026-06-28
**Status:** Approved (design)

## Summary

Give the platform auto-applied, threshold-based promotions — the marketing
levers that big marketplaces use to grow basket size (e.g. "free shipping over
€100", "10% off over €75"). v1 is a focused slice of a future cart-rules engine:
**single-threshold promotions** (one condition → one action), auto-applied at
checkout, with a per-promotion **funding split** that reuses the
discount-absorption mechanism already built for vendor payouts.

This is distinct from the existing **coupons** (which are code-based, entered by
the customer). Promotions are automatic and configured by an admin.

## Decisions (from brainstorming)

1. **Funding:** configurable split per promo — each promotion carries an
   `absorbed_by_vendor_percentage` (0 = platform funds it entirely, 100 = vendor
   funds it, between = split), feeding the existing vendor-earning calculation.
2. **Stacking:** discounts are **best-one-wins** — the larger of {best
   qualifying discount-promo, applied coupon} is used; they never stack. **Free
   shipping is separate** and always applies on top when it qualifies.
3. **Complexity:** **single-threshold per promo** (`subtotal ≥ X` → one action).
   Admins create as many promos as they like; best-one-wins picks the best.

## Funding / money model

- A **free-shipping** promo waives the order's shipping cost. Shipping is a
  platform/delivery charge that vendors never receive, so **free shipping is
  always a platform cost and has zero effect on vendor earnings.**
  `absorbed_by_vendor_percentage` is ignored for `free_shipping` promos.
- A **discount** promo (`percentage`/`fixed`) reduces the shared subtotal. Like
  a coupon, the discount is allocated to order items proportionally by each
  item's marked-up `subtotal`, and the vendor bears
  `absorbed_by_vendor_percentage` of its allocated share — fed into the existing
  `VendorEarningCalculator`. The difference from a coupon: a coupon uses the
  **vendor's own** `discount_absorption_percentage`; a promo uses the
  **promo's** `absorbed_by_vendor_percentage`.
- Because discounts are best-one-wins, **at most one discount source** (coupon
  OR promo) ever applies to an order, so the earning calc still sees a single
  order-level discount — only the absorption % source changes.

## Goals

- An admin-managed `promotions` table of single-threshold rules.
- Auto-apply the best qualifying promotion(s) at checkout: best-one-wins for
  discounts, free shipping on top.
- Correct funding: free shipping = platform cost (no earning impact); discount
  promo = allocated to items, absorbed per the promo's %.
- Admin CRUD for promotions, mirroring the coupons screen.

## Non-goals (v1)

- Tiered promotions ("spend more, save more" in one promo).
- Per-vendor / per-category / per-product targeting (v1 is platform-wide).
- Buy-X-get-Y, free gifts, customer segments, flash-sale scheduling beyond a
  single date range.
- Stacking multiple discounts.
- Storefront "you're €X away from free shipping" nudge (frontend, later).
- Changing the existing coupon behavior.

## Data model

**`promotions`** (Checkout module, `database/migrations`):
- `id`
- `name` string
- `type` enum(`free_shipping`, `percentage`, `fixed`)
- `value` decimal(8,2) nullable — the percent (for `percentage`) or € amount
  (for `fixed`); **null/ignored** for `free_shipping`
- `minimum_cart_amount` decimal(8,2) — the subtotal threshold (≥)
- `absorbed_by_vendor_percentage` decimal(5,2) default 0 — funding split for
  discount promos; ignored for `free_shipping`
- `start_date` date nullable
- `end_date` date nullable
- `is_active` boolean default true
- timestamps

A `Promotion` model with `$fillable` for all the above and casts
(`is_active` bool, dates).

"Active and in-window" = `is_active = true` AND (`start_date` is null or ≤ today)
AND (`end_date` is null or ≥ today).

## Components

**`PromotionEvaluator`** (Checkout module service) — pure, no DB writes,
unit-tested in isolation:

```
evaluate(float $subtotal, float $couponDiscount): PromotionResult
```

- Loads active, in-window promotions whose `minimum_cart_amount ≤ $subtotal`.
- Computes each discount-promo's value:
  - `percentage` → `round($subtotal × value / 100, 2)`
  - `fixed` → `min(value, $subtotal)` (never discount below 0)
- Picks the **best discount-promo** (largest value), then **best-one-wins**
  against `$couponDiscount`: the larger wins.
- Determines **free shipping**: true if any qualifying promo is `free_shipping`.
- Returns a `PromotionResult` with:
  - `discountAmount` (float) — the winning discount
  - `discountSource` (enum `coupon` | `promotion` | `none`)
  - `promotionId` (int|null) — the discount promo used, if any
  - `freeShipping` (bool)
  - `freeShippingPromotionId` (int|null)
  - `absorbedByVendorPercentage` (float|null) — the promo's % when
    `discountSource = promotion`; null otherwise (coupon path uses the vendor's
    own setting)

The evaluator takes plain scalars in and returns plain data — the controller
owns DB loading of the cart/order. (Loading the promotions list may be done in
the evaluator via the `Promotion` model query; that single read is acceptable.)

## Checkout integration

In `CheckoutController@placeOrder` (Checkout API), the current flow computes
`$totalAmount` (sum of marked-up item subtotals), `$discountAmount` (coupon),
`$priceAfterDiscount = $totalAmount − $discountAmount`, and
`$shippingCost = max(base_price, weight × price_per_kg)`.

Insert promotion evaluation **after** the coupon discount is known and **before**
totals are finalized:

1. `$result = (new PromotionEvaluator())->evaluate($totalAmount, $couponDiscount);`
2. `$discountAmount = $result->discountAmount;` (best-one-wins replaces the
   coupon-only discount)
3. If `$result->freeShipping` → `$shippingCost = 0;`
4. Recompute `$priceAfterDiscount = $totalAmount − $discountAmount;`
   `$finalAmount = $priceAfterDiscount + $shippingCost;`
5. Persist on the order: existing discount/shipping fields, plus record which
   promotion(s) applied (`promotion_id`, `free_shipping_promotion_id` columns on
   `orders`, nullable) for audit.

**Vendor-earning interaction** (the snapshot built in the payouts feature):
- The earning calc already allocates `order_coupon_discount` to items and
  absorbs a %. Now the absorbed % per item is chosen by the discount source:
  - `discountSource = coupon` → the **vendor's** `discount_absorption_percentage`
    (existing behavior, unchanged).
  - `discountSource = promotion` → the **promo's** `absorbed_by_vendor_percentage`
    (same value for every vendor on the order).
  - `discountSource = none` → no discount, absorption irrelevant.
- Free shipping never enters the earning calc (platform cost).

## Admin UI

A **Promotions** area under the existing `admin.auth` group, mirroring the
Coupons screens:
- **Index** — list promotions (name, type, value, threshold, absorbed-by %,
  window, active).
- **Create / Edit** — form with: name, type (free_shipping / percentage /
  fixed), value (hidden/disabled when type = free_shipping), minimum cart
  amount, absorbed-by-vendor % (hidden/disabled when type = free_shipping),
  start/end dates, active toggle.
- **Delete / toggle active.**

Validation: `type` in the enum; `value` required & numeric when type ≠
free_shipping (and ≤ 100 when `percentage`); `minimum_cart_amount` ≥ 0;
`absorbed_by_vendor_percentage` 0–100.

## Edge cases

- No qualifying promotion → behaves exactly as today (coupon-only or no
  discount).
- A `fixed` promo larger than the subtotal → capped at the subtotal (no negative
  total).
- Multiple qualifying discount promos → the largest is the candidate, then
  best-one-wins against the coupon.
- Multiple qualifying free-shipping promos → shipping is simply 0 (idempotent).
- Promo and coupon tie → either is fine; pick the promo (so its absorption %
  applies) — make this explicit in the evaluator.
- Expired/inactive promos are never considered.
- Snapshot immutability: the applied discount and its source are computed at
  order time and stored; later promo edits don't rewrite past orders.

## Testing

- **`PromotionEvaluator`** (unit): threshold met / not met; percentage vs fixed
  value; fixed capped at subtotal; best-one-wins when promo > coupon, when
  coupon > promo, and the tie rule; free-shipping flag set independently of the
  discount winner; no qualifying promo returns `none`.
- **Checkout** (feature, sqlite): free-shipping promo zeroes the shipping and
  leaves vendor earnings unchanged; a discount promo that beats a weaker coupon
  is applied and absorbed by the **promo's** % in the earning snapshot; a coupon
  that beats the promo keeps the **vendor's** absorption %.
- **Admin** (feature): create/validate a promotion behind `admin.auth`;
  free_shipping promo doesn't require `value`.

## Rollout

Additive and backward-compatible: a new table, new nullable `orders` columns, a
new service, and one insertion point in `placeOrder`. No change to coupons or
existing orders. Ships through the normal CI deploy; the migrations run in
`deploy-api.sh`. After deploy, create promotions in the new admin screen.

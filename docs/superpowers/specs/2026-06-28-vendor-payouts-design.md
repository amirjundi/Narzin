# Vendor Payouts / Settlement — Design

**Date:** 2026-06-28
**Status:** Approved (design)

## Summary

Give the platform a way to know what each vendor is owed and to record paying
them. The platform sells items it doesn't own: customers pay the vendor's base
price **plus a markup**, and the platform also deducts a **commission** from the
vendor's price; customer discounts are split between platform and vendor by a
configurable ratio. This feature computes each vendor's earning per sold item,
tracks it through a ledger (earning → reversal → payout), and lets an admin
record manual payouts and view per-vendor statements. The actual money transfer
happens outside the system (bank transfer); the system is the ledger of record.

## Money model

Three per-vendor settings, each with a global default (null on the vendor = use
the global default):

- **Markup %** — added on top of the vendor price; customer-facing. *Already
  exists* (`vendors.markup_percentage` + `platform_markups`). Not changed here.
- **Commission %** — deducted from the vendor's base price (platform revenue).
- **Discount-absorption %** — share of a customer discount the vendor bears
  (0 = platform absorbs all, 100 = vendor absorbs all, between = split).

Per sold order item, in the **stored price currency** (the exchange rate is for
customer display only and does not affect payouts):

```
vendor_base_subtotal    = vendor_base_price × quantity
vendor_commission_amount = round(vendor_base_subtotal × commission% / 100, 2)
item_discount_allocated  = order_coupon_discount × (order_item.subtotal / order.total_amount)
vendor_discount_absorbed = round(item_discount_allocated × discount_absorption% / 100, 2)
vendor_earning           = vendor_base_subtotal − vendor_commission_amount − vendor_discount_absorbed
```

- `vendor_base_price` is `product_variants.price` at order time.
- `order_item.subtotal` is the marked-up line total already stored today.
- `order_coupon_discount` is the order's coupon discount, defined as
  `orders.total_amount − orders.price_after_discount` (shipping and wallet are
  excluded). It is allocated to items proportionally by their marked-up
  `subtotal`. If `order_coupon_discount` is 0, every item's discount share is 0.
  Wallet credit is a payment method, **not** a discount, and never reduces a
  vendor's earning.
- All four computed values are **snapshotted onto the order item at creation** so
  later changes to markup/commission/absorption/price never rewrite history.

Earning lifecycle: `pending` (order placed) → **`payable`** when the item is
collected from the vendor → `reversed` if the customer returns/cancels it after
collection → settled when the admin records a payout.

A vendor's **payable balance** = SUM of their ledger entries (earnings positive,
reversals and payouts negative).

## Goals

- Per-vendor commission % and discount-absorption %, with global defaults.
- Accurate per-item vendor earning, snapshotted at order time.
- A ledger that yields payable balance, pending earnings, total paid.
- Admin records manual payouts (capped at payable balance) and views statements.
- Reversal of earnings on post-collection returns; manual `adjustment` entries.

## Non-goals (v1)

- Automated money transfer / payout provider integration (manual only).
- Vendor-facing payout UI (admin-only for now; vendor view is a later phase).
- Multi-currency payout conversion (single stored currency).
- Backfilling historical orders (the production DB has no real orders yet).
- Changing the existing markup behavior.

## Data model

All migrations live in the relevant module's `database/migrations`.

1. **`vendors`** (Vendor module) — add two nullable decimals:
   `commission_percentage` decimal(5,2) nullable, `discount_absorption_percentage`
   decimal(5,2) nullable. Add both to the `Vendor` model `$fillable`.

2. **`payout_settings`** (Admin module) — singleton settings table mirroring
   `platform_markups`: `id`, `default_commission_percentage` decimal(5,2) default 0,
   `default_discount_absorption_percentage` decimal(5,2) default 0, timestamps.
   A `PayoutSetting` model with a static `getCurrent()` returning the latest row
   (creating a zero-default row if none).

3. **`order_items`** (Checkout module) — add four nullable decimals (snapshot):
   `vendor_base_subtotal`, `vendor_commission_amount`, `vendor_discount_absorbed`,
   `vendor_earning`, all decimal(10,2) nullable. Add to `OrderItem` `$fillable`.

4. **`vendor_transactions`** (Vendor module) — the ledger:
   `id`, `vendor_id` (FK), `type` enum(`earning`,`reversal`,`payout`,`adjustment`),
   `amount` decimal(10,2) (signed: + earning, − reversal/payout),
   `order_item_id` (FK nullable), `payout_id` (FK nullable),
   `description` string nullable, `created_by` (users.id, nullable), timestamps.
   `VendorTransaction` model. Indexes on `vendor_id` and `order_item_id`.

5. **`vendor_payouts`** (Vendor module):
   `id`, `vendor_id` (FK), `amount` decimal(10,2), `method` string nullable,
   `reference` string nullable, `notes` text nullable, `paid_at` datetime,
   `created_by` (users.id, nullable), timestamps. `VendorPayout` model.

### Resolution of effective rates

A small service resolves the effective rate for a vendor:
`commission% = vendor.commission_percentage ?? PayoutSetting::getCurrent()->default_commission_percentage`
(and likewise for discount-absorption %).

## Components

- **`VendorEarningCalculator`** (Vendor module service) — pure function:
  given an order item's base price, quantity, the order's coupon discount and
  total, and the vendor's effective commission%/absorption%, returns the four
  snapshot values. Unit-tested in isolation.

- **`VendorLedgerService`** (Vendor module) — the only writer of
  `vendor_transactions`:
  - `creditEarning(OrderItem)` — idempotent; writes one `earning` row for an item
    if none exists.
  - `reverseEarning(OrderItem)` — writes a `reversal` (−earning) if an earning
    exists and isn't already reversed.
  - `recordPayout(Vendor, amount, method, reference, notes, adminId)` — validates
    `amount ≤ payableBalance(vendor)`, creates a `vendor_payouts` row and a
    `payout` ledger row in one transaction.
  - `adjust(Vendor, amount, description, adminId)` — manual correction.
  - `payableBalance(Vendor)` / `pendingEarnings(Vendor)` / `totalPaid(Vendor)`.

## Trigger flow (where it hooks in)

1. **Order creation** — in `CheckoutController@placeOrder` (Checkout API) where
   `OrderItem::create([...])` runs (~line 287): also compute via
   `VendorEarningCalculator` and store the four snapshot fields. The order's
   coupon discount and total are in scope there.

2. **Collected from vendor** — where `collection_status` is set to `collected`
   (Admin `ShipmentController` collect / collect-vendor actions): call
   `VendorLedgerService::creditEarning($orderItem)` for each item transitioning to
   `collected`. Idempotent so re-runs don't double-credit.

3. **Return/cancel after collection** — wherever an order item is marked
   returned/cancelled (Admin order status update / shipment `unavailable`): if the
   item had a `payable` earning, call `reverseEarning($orderItem)`.

4. **Record payout** — new admin controller action calls `recordPayout(...)`.

## Admin UI (Admin module, new "Vendor Payouts" area)

- **Index** — table of vendors with: pending earnings, payable balance, total
  paid. Link to each vendor's statement.
- **Vendor statement** — the ledger entries (earning per order item, reversals,
  payouts, adjustments) with running balance; a **Record payout** form (amount
  defaulted to payable balance, method, reference, notes) that posts to
  `recordPayout`; an **Adjustment** form.
- **Vendor edit page** (existing) — add `commission_percentage` and
  `discount_absorption_percentage` inputs (blank = use default).
- **Payout settings page** — edit the two global defaults (writes a new
  `payout_settings` row, like markup).

Routes under the existing `admin.auth` group.

## Edge cases

- Return before collection: no ledger row exists; nothing to reverse.
- Payout amount > payable balance: rejected with a validation error.
- Partial payouts allowed; multiple payouts accumulate as separate rows.
- Re-collecting / status toggling never double-credits (idempotent earning).
- Rate/price changes after order: snapshot is immutable, balances unaffected.
- Vendor with null overrides always falls back to the global default.
- Wallet credit never reduces vendor earning.

## Testing

- **`VendorEarningCalculator`** (unit): commission + proportional discount
  absorption math; absorption 0/50/100; zero discount; rounding.
- **`VendorLedgerService`** (feature, sqlite): earning credited once
  (idempotent); reversal flips balance; payout reduces balance and is capped at
  payable; adjustment applies; balance = sum of entries.
- **Admin payout flow** (feature): record-payout endpoint creates payout + ledger
  row and rejects over-balance amounts (admin-authenticated).
- Note: tests run on sqlite with FKs enforced — create required parent rows
  (vendor + its user, order, order_item) and bypass any MySQL-only model global
  scopes as needed.

## Rollout

Additive and backward-compatible: all new columns are nullable, new tables are
independent, and snapshot/ledger writes are new code paths. Ships through the
normal CI deploy; the migrations run in `deploy-api.sh`. Set the global default
commission % and discount-absorption % in the admin settings after deploy.

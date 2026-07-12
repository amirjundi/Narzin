# Returns — Backend Feature — Design

**Date:** 2026-07-12
**Status:** Approved (design)

## Summary

Build the backend for customer returns: a customer requests to return an order,
an admin approves/rejects, and an approved+refunded return credits the wallet
(reusing the existing refund flow). Adds an `order_returns` table + state
machine + customer API + admin API + return-rate reporting. This is **sub-project
1 of 3** for full-stack Returns (backend → web UI → Flutter UI); the clients wire
against this API.

**Scope decision (stated, not asked):** whole-order returns only in v1. The
schema carries a nullable `order_item_id` so per-item returns are a later
enhancement, but the refund path reuses the proven whole-order `refundToWallet`
logic and avoids fragile partial refund/stock/ledger math.

## What exists (reused, not rebuilt)

- `OrderController::refundToWallet($id)` (admin, route `orders.refund`): whole-order
  refund — credits `UserWallet`, writes a `WalletTransaction`, refills stock
  (`refillOrderStock`), reverses vendor earnings (`VendorLedgerService::reverseEarning`
  per item), sets `payment_status='refunded'` + `order_status='cancelled'`, logs
  `OrderAudit`. The return **refund** step reuses this exact logic (extracted so
  both the legacy admin button and the returns flow call one method).
- `payment_status='refunded'`, wallet, vendor ledger, `OrderAudit` — all existing.

## Data model — `order_returns`

| column | type | notes |
|--------|------|-------|
| id | bigint PK | |
| order_id | bigint FK orders, cascade | |
| order_item_id | bigint nullable FK order_items | null = whole-order (v1 always null) |
| user_id | bigint FK users | the requester (order owner) |
| reason | string | from a fixed reason list (validated) |
| status | enum(requested, approved, rejected, refunded) | state machine |
| refund_amount | decimal(12,2) nullable | set when refunded |
| admin_note | string nullable | reject/approve note |
| requested_at | timestamp | |
| resolved_at | timestamp nullable | set on approve/reject/refund |
| timestamps | | |

`OrderReturn` model (`Modules\Checkout\Models\OrderReturn`), `belongsTo` Order + User.

## State machine

```
requested ──approve──▶ approved ──refund──▶ refunded
    │
    └────reject───▶ rejected
```

- Only `requested` → `approved`/`rejected`.
- Only `approved` → `refunded` (refund executes the wallet credit).
- `rejected`/`refunded` are terminal.
- Illegal transitions return 422.

## Reason list

Fixed set (validated `in:`): `damaged`, `wrong_item`, `not_as_described`,
`no_longer_needed`, `other`. (Clients render these; `other` allows a free-text
note in `admin_note`/a request note — keep the reason enum-constrained.)

## Customer API (sanctum auth)

- **`POST /api/v1/orders/{id}/returns`** — request a return.
  - Body: `reason` (required, in the list), optional `note`.
  - Guards: order belongs to the authenticated user; order is refundable
    (`payment_status in ['completed','processing']`); no existing non-rejected
    return for the order (one active return per order in v1); (optional) within a
    return window if configured — v1: no window.
  - Creates `order_returns` row `status='requested'`, `requested_at=now`.
  - 201 with the return; 422 on guard failure.
- **`GET /api/v1/returns`** — list the authenticated user's returns (with order).

## Admin API/web (admin.auth)

- **`GET /admin/returns`** — list return requests (filter by status), newest first.
- **`POST /admin/returns/{id}/approve`** — `requested`→`approved`, `resolved_at=now`, optional `admin_note`.
- **`POST /admin/returns/{id}/reject`** — `requested`→`rejected`, `resolved_at=now`, `admin_note`.
- **`POST /admin/returns/{id}/refund`** — `approved`→`refunded`: executes the
  shared whole-order refund (the extracted `refundToWallet` logic) for the
  return's order, sets `refund_amount` = refunded amount, `status='refunded'`,
  `resolved_at=now`. Idempotent: refunding an already-refunded order/return is a
  no-op guarded by the order's `payment_status='refunded'`.

**Refactor:** extract the body of `OrderController::refundToWallet` into a
`Modules\Checkout\Services\OrderRefundService::refundWholeOrder(Order $order,
string $reason, ?int $adminId): float` returning the refunded amount. The legacy
admin button and the return-refund both call it. Keep behavior identical
(transaction, wallet, stock, ledger reversal, audit).

## Reporting — admin Returns analytics

`Modules/Admin/app/Services/ReturnAnalyticsService.php` over `order_returns` +
`orders`, consuming `DateRange`:
- `summary(DateRange)`: total returns requested, approved, rejected, refunded;
  return_rate = returns requested / orders placed in range; total refunded amount.
- `byReason(DateRange)`: returns grouped by `reason`, count desc.
- `byProduct(DateRange)` / `byVendor(DateRange)`: for per-item returns — **deferred**
  with per-item returns (v1 whole-order has no product granularity beyond the
  order's items; skip until per-item lands).

New admin **Returns** page (`statistics/returns`) — summary + reason breakdown +
a recent-returns table; sidebar link. Reuses the shared date filter.

## Testing

- Migration/model: `order_returns` row round-trips; state enum.
- `OrderRefundService::refundWholeOrder`: credits wallet by `final_price`, refills
  stock, reverses vendor earnings, sets `refunded`/`cancelled`, returns the amount;
  idempotent on an already-refunded order.
- Legacy `refundToWallet` still works (now delegates to the service).
- Customer request: valid request creates a `requested` return; guards reject
  (not owner, not paid, duplicate active return) with 422/403.
- Admin transitions: approve/reject from `requested`; refund from `approved`
  executes the refund + credits wallet; illegal transitions 422.
- `ReturnAnalyticsService`: return_rate, byReason, refunded totals over a range;
  zero-orders → no divide-by-zero.
- Returns page: admin 200 sees summary + reasons; guest redirected.

## Out of scope (this sub-project)

- Per-item / partial returns (schema-ready via nullable `order_item_id`).
- Client UIs (sub-projects 2 web, 3 Flutter).
- Refund to the original gateway (Nass) — v1 refunds to wallet, matching the
  existing admin refund. Return shipping/logistics. Return windows/SLAs. CSV.

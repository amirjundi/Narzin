# Analytics Phase 6 — Payment Analytics — Design

**Date:** 2026-07-12
**Status:** Approved (design)

## Summary

Capture and report payment health: gateway success/failure rate, failure-reason
breakdown (Nass response codes), retry behavior, and payment-method mix. Adds a
`payment_attempts` capture table (retry-level data currently lost — each retry
overwrites the order), non-blocking capture at 3 points in the Nass flow, a
`PaymentAnalyticsService`, and a new admin **Payments** page.

Dual data sources for immediate + richer value:
- **Order-level** (`orders.payment_status`, `wallet_usage`) — works from day one:
  overall success rate and method mix over all existing orders.
- **Attempt-level** (new `payment_attempts`) — fills in going forward: retry
  counts, per-attempt failure reasons, failed-then-recovered.

## Why capture (retry data is being lost)

Each payment retry overwrites the order's `payment_status`/`callback_data`, so an
order only remembers its final outcome. Nass failures currently go only to the
Laravel log — not queryable. `payment_attempts` records each gateway attempt so
retry/failure-trend analytics exist going forward. Wallet-only payments are
internal/instant and are **not** gateway attempts — they're covered by the
order-level method mix, not `payment_attempts`.

## Component 1 — payment_attempts table

`Modules/Checkout/database/migrations/..._create_payment_attempts_table.php`:

| column | type | notes |
|--------|------|-------|
| id | bigint PK | |
| order_id | bigint nullable, indexed | FK to orders, nullOnDelete |
| user_id | bigint nullable | |
| gateway | string | `'nass'` (future: others) |
| status | enum(initiated, success, failed) | |
| response_code | string nullable, indexed | Nass `responseCode` (`'00'`=success) |
| amount | decimal(12,2) nullable | charged amount (post-wallet) |
| occurred_at | timestamp, indexed | |
| timestamps | | |

`PaymentAttempt` model (`Modules\Checkout\Models\PaymentAttempt`), `$fillable`
all of the above.

## Component 2 — non-blocking capture

`Modules\Checkout\Services\PaymentAttemptRecorder` with a static, best-effort
method that never throws (mirrors Phase 1 `CaptureService` discipline —
try/catch(\Throwable), log-and-continue; a capture failure must never break
checkout/payment):

`record(?int $orderId, ?int $userId, string $gateway, string $status, ?string $responseCode, ?float $amount): void`

**3 capture points in `CheckoutController`** (each a single call, `$order` in scope):
1. **Initiate** — `placeOrder`, after `createTransaction` succeeds (~line 411):
   `record($order->id, …, 'nass', 'initiated', null, $finalAmount)`.
2. **Resolve (verify)** — `verifyPayment`, right after
   `checkTransactionStatus($paymentId)` returns (~line 535): read
   `$nassStatus['data']['responseCode']`; status `'success'` if `'00'` else
   `'failed'`; pass the response_code.
3. **Resolve (webhook)** — `nassWebhook`, same, after its
   `checkTransactionStatus` (~line 725).

Recording on every status check naturally captures retries (repeated checks →
multiple attempt rows). `checkTransactionStatus` caches successful responses for
60s and early-returns the cache; that's fine — a cached hit still reflects a real
resolution, and recording it is acceptable (idempotency not required for
analytics counts, but keep it lightweight).

## Component 3 — PaymentAnalyticsService

`Modules/Admin/app/Services/PaymentAnalyticsService.php`, consuming `DateRange`.

- `orderPaymentSummary(DateRange): array` — over `orders` in range grouped by
  `payment_status`: counts for completed/failed/expired/processing/not_paid, and
  `success_rate` = completed / (completed+failed+expired) [resolved orders; 0
  when none resolved]. Works immediately over existing orders.
- `methodMix(DateRange): array` — over `orders` in range: `wallet_involved`
  (`wallet_usage > 0`) count + `gateway_only` count (+ placed value each).
  Inferred (no explicit method column); labeled as inferred.
- `attemptSummary(DateRange): array` — over `payment_attempts` in range:
  total attempts, success/failed/initiated counts, `gateway_success_rate` =
  success / (success+failed). Fills in going forward.
- `failureReasons(DateRange): Collection` — failed `payment_attempts` grouped by
  `response_code`, count desc. (Optional: a small known-code→label map for common
  Nass codes; otherwise show the raw code.)

Money rounded 2, rates rounded 4, divide-by-zero guarded. Range on the
respective table's timestamp (`orders.created_at` / `payment_attempts.occurred_at`).

## Component 4 — Payments page (new)

- Route `statistics/payments` (name `statistics.payments`) in the `admin.auth`
  group; `StatisticsController::paymentStatistics`; view
  `admin::statistics.payments` (`<x-admin-layout>`, shared date filter).
- Shows: order-level success-rate + payment-status breakdown (immediate),
  method-mix, attempt-level success rate, and a failure-reason (response code)
  table — each with an honest empty-state and a note that attempt-level metrics
  fill in as new payments flow.
- Sidebar link in `resources/views/components/admin/sidebar.blade.php`.

## Testing

- Migration + model: `payment_attempts` accepts a row with all fields.
- `PaymentAttemptRecorder::record`: writes a row; a malformed/throwing write is
  swallowed (never throws) — assert a forced DB error doesn't propagate.
- Capture wiring: an initiate call records an `initiated` attempt; a resolution
  with responseCode `'00'` records `success`, a non-`00` records `failed` with
  the code. (Feature test hitting placeOrder/verify with the gateway mocked, or a
  unit test of the recorder — keep it focused.)
- `PaymentAnalyticsService`: order summary success_rate from seeded orders;
  method mix (wallet vs gateway); attempt summary + failure-reason grouping from
  seeded `payment_attempts`; out-of-range excluded; zero → no divide-by-zero.
- Payments page: admin 200 sees the sections; guest redirected.

## Out of scope

- Non-Nass gateways. Nass response-code → human-label dictionary beyond a small
  common set. Alerting on failure spikes. CSV (Phase 10).
- Reconciling attempt-level vs order-level exactly (they measure different
  things: attempts count retries, orders count final outcomes).

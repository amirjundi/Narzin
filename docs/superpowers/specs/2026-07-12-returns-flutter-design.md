# Returns — Flutter User App — Design

**Date:** 2026-07-12
**Status:** Approved (design)

## Summary

Sub-project 3 of full-stack Returns: the return flow in the Flutter user app
(`Narzin-app/user/narzin`), wired to the returns API. Adds a `ReturnsModel` +
`ReturnsCubit`, and reworks the existing placeholder `returns_screen.dart` (which
today only lists returnable orders, no real return functionality) into a real
"My Returns" list + a request flow.

Follows the app's **existing conventions** (Cubit doing raw `http`, `token`
passed from `LoginCubit`, `Constants.apiBaseUrl`, models via `fromJson`) — this
is not the place to re-architect the app's known single-state/no-repository
debt; consistency wins.

## Existing context (reused)

- Token: `BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? ''`.
- API base: `Constants.apiBaseUrl` = `.../api/v1/`; auth header `Bearer $token`.
- The **profile menu already has a Returns tile** that opens `ReturnsScreen`
  (and calls `getMyOrder`) — that navigation stays; the screen gets real content.
- `OrderCubit.getMyOrder` populates `myOrdersModel` (order history) — reused for
  the "pick an order to return" list; each order has `id`, `order_number`,
  `payment_status`.
- l10n `returns` key exists (ar/de).

## API contract (from sub-project 1)

- `GET /api/v1/returns` → `{status, data:[{id, order_id, reason, status,
  refund_amount, requested_at, order:{order_number, ...}}]}`.
- `POST /api/v1/orders/{id}/returns` body `{reason, note}` → 201 `{status,data}`;
  403/422 `{status:false, message}`.
- Reasons: `damaged, wrong_item, not_as_described, no_longer_needed, other`.
- Eligible order = `payment_status in [completed, processing]`; one active return.

## Component 1 — ReturnsModel

`lib/model_layer/returns_model.dart`: `ReturnsModel.fromJson(Map)` with
`bool? status` + `List<ReturnItem> data`; `ReturnItem` fields: `id, orderId,
reason, status, refundAmount, requestedAt, orderNumber` (from `order.order_number`
when present, else null). Null-safe parsing with `?.toString()` on numeric fields
(matching the app's model conventions). Tolerates a missing `order` relation.

## Component 2 — ReturnsCubit

`lib/bussiness_logic/returns_cubits/returns_cubit.dart` + `returns_state.dart`,
registered in `main.dart`'s `MultiBlocProvider`. Mirrors `OrderCubit`'s style
(fields + `isLoading` + `emit` of simple states):

- `fetchReturns({required String token})` — GET `${Constants.apiBaseUrl}returns`;
  parse into `returnsModel`; emit loaded/error. HTML-response guard like
  `OrderCubit` (checks for `<!DOCTYPE`/`<html>` before json.decode).
- `requestReturn({required String token, required int orderId, required String
  reason, String? note})` — POST `${Constants.apiBaseUrl}orders/$orderId/returns`
  with `{reason, note}`; on 2xx emit success; on 4xx parse `message` and emit an
  error state carrying it (so the screen can show the backend message, e.g.
  "A return already exists for this order").
- Best-effort/defensive: try/catch, no crash on network error (emit error state).

State: a small state set (e.g. `ReturnsInitial`, `ReturnsLoading`,
`ReturnsLoaded`, `ReturnsError(message)`, `ReturnRequestSuccess`,
`ReturnRequestError(message)`) — or the app's `isLoading`+emit idiom; match
whatever `OrderCubit` does so the screen's `BlocBuilder` stays consistent.

## Component 3 — returns_screen.dart (real)

Rework the screen. On open (already dispatched from the menu; also call
`fetchReturns(token)` on init):

- **My Returns** — from `ReturnsCubit.returnsModel`: each row shows order number
  (or order_id), reason (readable label), status (colored chip:
  requested/approved/rejected/refunded), requested date. Empty-state text.
  Loading + error states surfaced.
- **Request a Return** — the existing eligible-orders list (from `myOrdersModel`,
  filtered to `payment_status in [completed, processing]`) → tapping an order
  opens a dialog/bottom-sheet with a reason picker (the 5 values, readable
  labels) + optional note field → `requestReturn(...)`. On success: snackbar +
  refresh (`fetchReturns`); on failure: snackbar with the backend `message`.

Reason value sent to the API is exactly the enum string; labels display-only.
Consistent with the app's Arabic/German i18n where reasonable (reason labels can
be plain for v1; add l10n keys only if trivial).

## Verification (no cubit-http test precedent in the app)

- `flutter test test/returns_model_test.dart` — a `ReturnsModel.fromJson` parse
  test (mirrors the existing `test/home_blocks_model_test.dart`), covering a full
  return row (with `order.order_number`) and a row with a missing `order`.
- `flutter analyze` — no new errors/warnings introduced.
- `flutter build apk --debug` (or at least `flutter analyze` if a full build is
  too heavy in CI) to confirm it compiles.
- Cubit HTTP behavior is verified by analyze + compile + structure review (the
  app has no http-mock test harness; adding one is out of scope).

## Out of scope

- Vendor app returns (customer-only feature). Re-architecting the cubit to a
  repository/proper-state pattern. Return tracking timeline. Cancelling a return.
  A full widget test of the screen (the app tests models/simple widgets, not
  cubit-driven screens with http).

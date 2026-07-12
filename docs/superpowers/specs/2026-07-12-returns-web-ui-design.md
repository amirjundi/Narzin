# Returns — Web UI — Design

**Date:** 2026-07-12
**Status:** Approved (design)

## Summary

Sub-project 2 of full-stack Returns: wire the React web app (`narzin-main`) to
the returns backend API (shipped in sub-project 1). Replaces the 323-line mock
`Returns.jsx` (`src/components/pages/MyAccount/Returns.jsx`) with a real returns
list + a "start a return" form. Self-contained on the Returns tab — no surgery
on Orders.jsx.

(`src/pages/Return.jsx` is a static returns-policy page — left untouched.)

## API (from sub-project 1)

- `POST /api/v1/orders/{id}/returns` body `{reason, note?}` → 201 `{status,data}`;
  403 (not owner), 422 (unpaid / duplicate active / invalid reason) with
  `{status:false, message}`.
- `GET /api/v1/returns` → `{status, data:[...returns with .order]}`.
- Reasons (fixed): `damaged, wrong_item, not_as_described, no_longer_needed, other`.
- Eligible order = `payment_status === 'completed'` (or `processing`); one active
  return per order.

## Component 1 — ReturnsSlice

`src/Store/slices/ReturnsSlice.js` (registered in `src/Store/store.js` as
`returns`), mirroring `MyOrdersSlice` conventions (`createAsyncThunk`, reads
`response.data.data`, `api` from `../../api/axios`):

- `requestReturn({ orderId, reason, note })` — `POST /v1/orders/${orderId}/returns`
  with `{reason, note}`; on error `rejectWithValue(error.response?.data)` so the
  component can show the backend `message`.
- `fetchReturns()` — `GET /v1/returns`.
- State: `{ returns: [], status: 'idle', error: null, submitting: false,
  submitError: null }`. `requestReturn.pending`→submitting; `.fulfilled`→push/refetch,
  clear submitError; `.rejected`→submitError = payload.message. `fetchReturns`
  fills `returns`.

## Component 2 — Returns.jsx (real)

Replace the mock. On mount, `dispatch(fetchReturns())` and (for the order picker)
`dispatch(fetchOrders())` (existing MyOrders thunk) if not already loaded. Render:

- **My returns** — the `returns` from state: order number, reason (labeled),
  status (badge, colored by status), requested date. Honest empty-state ("You
  haven't requested any returns yet.").
- **Start a return** — a form: a `<select>` of the user's **eligible** orders
  (payment_status completed/processing and no existing active return — filter
  client-side; the backend also enforces), a reason `<select>` (the 5 values with
  readable labels), an optional note `<textarea>`, and a Submit that dispatches
  `requestReturn`. On success: clear the form + refetch returns + a success toast
  (reuse the app's `ShowToast`/react-toastify). On failure: show `submitError`
  (the backend message, e.g. "A return already exists for this order").

Keep it accessible and consistent with the existing MyAccount styling (Tailwind).
All display values are React-escaped by default (JSX) — no `dangerouslySetInnerHTML`.

## Non-negotiables

- No secrets/tokens in code; uses the shared `api` (Sanctum cookie auth) like
  every other slice.
- Reason values sent to the API are exactly the 5 enum strings (labels are
  display-only).
- Graceful: a failed fetch shows an error state, not a crash; a failed request
  shows the message and keeps the form.

## Testing (Vitest — configured)

- `ReturnsSlice`: `requestReturn` posts `/v1/orders/{id}/returns` with
  `{reason, note}` and maps a rejection to `submitError`; `fetchReturns` GETs
  `/v1/returns` and fills `returns`. (Mock `api`; mirror `CardSlice.track.test.js`.)
- Reducer: pending/fulfilled/rejected transitions set submitting/returns/submitError.
- (Optional, light) a Returns.jsx render smoke test with a preloaded store showing
  the empty-state — only if it fits the existing `renderWithProviders` harness.

## Out of scope

- Flutter UI (sub-project 3). Return shipping labels/tracking (the mock's fake
  tracking timeline is dropped — backend has no tracking). Cancelling a return.
  i18n of reason labels (plain English v1; keys can be added later).

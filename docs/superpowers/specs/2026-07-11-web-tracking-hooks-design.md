# Web Client Tracking Hooks — Design

**Date:** 2026-07-11
**Status:** Approved (design)

## Summary

Wire the React web app (`narzin-main`) to the Phase 1 capture endpoints so the
analytics that are currently backend-ready actually receive data. Today the web
app tracks only product views (`/v1/telemetry/view`). This adds the two missing
signals — session/UTM bootstrap and cart events — plus stitches the checkout to
the session. Result: Phase 2's `cart_add`/`sessions` funnel stages and the
abandoned-cart report light up, and Phase 3 (attribution) gets the `session_id`
it needs on the order.

Web only. The Flutter user/vendor apps get the same hooks later.

## Reuse (already in the app)

- `getSessionId()` (`src/helpers/session.js`) — stable per-browser UUID in
  `localStorage` (`nz_session_id`). Already used by the telemetry/view call.
- `api` (`src/api/axios.js`) — Sanctum cookie auth (`withCredentials`,
  `withXSRFToken`). The telemetry/view call's 419→`getCsrfCookie()`→retry
  pattern is the template for state-changing tracking POSTs.

## Component 1 — Session + UTM bootstrap

New `src/helpers/tracking.js` exporting `trackSession()`:
- Reads `utm_source/medium/campaign/term/content` from `window.location.search`,
  `document.referrer`, and `window.location.href` (landing URL).
- POSTs `/v1/track/session` with `session_id: getSessionId()` + those fields.
- Best-effort: swallow errors; on 419, refresh CSRF and retry once (mirroring
  the existing telemetry call). Never throws to the caller.

Called once on app mount (a `useEffect` in `src/App.jsx`). The backend
`recordSession` is first-touch, so calling on every load only sets UTM the first
time — safe to call unconditionally.

## Component 2 — Cart events

Same `tracking.js` exports `trackCartEvent({ action, product_id, variant_id,
quantity, unit_price })` → POSTs `/v1/track/cart` with `session_id`. Best-effort,
non-blocking (the endpoint always returns 200 anyway).

**Scope for this task: `add` events only.** Fire `trackCartEvent` with
`action:'add'` from the `addToCart` thunk after the cart API succeeds — this is
the one thunk that carries full product identity (`product_id`,
`product_variant_id`, `quantity`) plus the threaded `unit_price`. It fully powers
the funnel's `cart_add` stage and seeds accurate abandoned-cart value.

**Deferred (noted fast-follow):** `updateCartItem`/`removeCartItem` receive only
`cartItemId`, not `product_id`/`variant_id`, so `update`/`remove` tracking needs
a Redux cart-state lookup to resolve the line. Left out of this task; until it
lands, abandoned-cart value reflects adds only (a cart fully emptied client-side
may still read as abandoned — acceptable best-effort). Map `product_variant_id`
→ the endpoint's `variant_id`.

**Cart-value contract (critical — backend depends on this):** the backend
`AbandonedCartService::netCart` is **last-write-wins on absolute quantity per
line** (a second `add` for the same product/variant overwrites, does not
accumulate; `remove` drops the line). So the web hook MUST send the **absolute
resulting quantity** for that line, never a delta. `addToCart`/`updateCartItem`
already carry an absolute `quantity`, which satisfies this.

**unit_price:** `addToCart` currently receives only `{product_id,
product_variant_id, quantity}`. Add an optional `unit_price` argument
(backward-compatible, defaults `undefined`/omitted) and pass the displayed unit
price from the main add-to-cart call sites (e.g. ProductPage). Where a caller
doesn't supply it, the event is sent without `unit_price` (backend stores null;
abandoned-cart value best-effort for that line). `updateCartItem`/`removeCartItem`
send without price (value is recomputed from the last add's price on the backend
per-line state — a remove needs no price, an update keeps the prior line price
only if re-sent; acceptable best-effort for this phase).

## Component 3 — Checkout session stitching

In `CheckoutSlice.js` `initiatePayment`, include `session_id: getSessionId()` in
the `/v1/place-order` payload (spread it into `checkoutData`). This lets the
backend stamp `checkout_events.session_id` (improving funnel stitching) and, in
Phase 3, snapshot the session's UTM onto the order.

## Non-negotiable: non-blocking

No tracking call may break the cart, checkout, or app load. Every tracking
function catches its own errors and returns; cart/checkout thunks do not await
tracking in a way that can reject their own flow (fire-and-forget, or awaited
inside a try/catch that ignores failures).

## Testing (Vitest — already configured)

- `trackSession`: builds the correct payload from a mocked
  `window.location`/`document.referrer` and calls `api.post('/v1/track/session', …)`;
  a rejected `api.post` does not throw out of `trackSession`.
- `trackCartEvent`: posts `/v1/track/cart` with the mapped fields
  (`variant_id` from `product_variant_id`); swallows errors.
- `addToCart` thunk: on success, fires a cart-track with `action:'add'`, the
  absolute quantity, and the threaded `unit_price`; a tracking failure does not
  fail the thunk.
- `initiatePayment`: the posted place-order body includes `session_id`.

Mock `api` (the axios instance) in tests; do not hit the network.

## Out of scope

- Flutter user/vendor app hooks (same endpoints, later task).
- Wishlist / search-from-client events (search is already captured server-side).
- Recovery emails / any new backend (this is client wiring over existing Phase 1
  endpoints).

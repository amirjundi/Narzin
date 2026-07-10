# Web Client Tracking Hooks Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Wire the React web app (`narzin-main`) to the Phase 1 `/track` endpoints so the funnel's `cart_add`/`sessions` stages and abandoned-cart populate, and checkout carries `session_id` (enabling Phase 3 attribution).

**Architecture:** A small `tracking.js` helper posts to the existing capture endpoints (reusing `getSessionId()` and the shared `api`). It's called on app mount (session/UTM), from the `addToCart` thunk (cart add events), and the place-order payload gets `session_id`. All tracking is best-effort and never breaks the app.

**Tech Stack:** React 18 + Vite, Redux Toolkit, Vitest (jsdom, globals — configured in `vitest.config.js`, setup `src/test/setup.js`). Tests live in `__tests__/*.test.js(x)`; run a file with `npx vitest run <path>` from `narzin-main`.

## Global Constraints

- Non-blocking: no tracking call may break cart, checkout, or app load — every tracking fn catches its own errors; callers fire-and-forget or wrap in try/catch. (from spec)
- Reuse `getSessionId()` (`src/helpers/session.js`) and `api` + `getCsrfCookie` (`src/api/axios.js`); mirror the telemetry/view 419→CSRF→retry pattern. (from spec)
- Cart quantities are ABSOLUTE per line (the backend valuation is last-write-wins), never deltas — `addToCart` already sends an absolute `quantity`. (from spec)
- Scope: `add` cart events only this task; web only. (from spec)
- Run web commands from `C:\xampp\htdocs\Narzin\narzin-main`.

---

### Task 1: tracking.js helper + session bootstrap

**Files:**
- Create: `src/helpers/tracking.js`
- Modify: `src/App.jsx` (call `trackSession()` once on mount)
- Test: `src/helpers/__tests__/tracking.test.js`

**Interfaces:**
- Produces `readAttribution(search?, referrer?, href?)`, `trackSession()`, `trackCartEvent({action, product_id, variant_id, quantity, unit_price})` — all from `src/helpers/tracking.js`. Task 2 consumes `trackCartEvent`.

- [ ] **Step 1: Write the failing test**

Create `src/helpers/__tests__/tracking.test.js`:

```js
import { describe, it, expect, vi, beforeEach } from "vitest";

vi.mock("../../api/axios", () => ({
  default: { post: vi.fn(() => Promise.resolve({ data: {} })) },
  getCsrfCookie: vi.fn(() => Promise.resolve()),
}));
vi.mock("../session", () => ({ getSessionId: () => "sess-test" }));

import api from "../../api/axios";
import { readAttribution, trackSession, trackCartEvent } from "../tracking";

describe("readAttribution", () => {
  it("extracts utm params, referrer, landing url", () => {
    const a = readAttribution(
      "?utm_source=google&utm_medium=cpc&utm_campaign=july",
      "https://ref.example/",
      "https://shop.example/?utm_source=google"
    );
    expect(a.utm_source).toBe("google");
    expect(a.utm_medium).toBe("cpc");
    expect(a.utm_campaign).toBe("july");
    expect(a.referrer).toBe("https://ref.example/");
    expect(a.landing_url).toBe("https://shop.example/?utm_source=google");
  });

  it("omits absent params as undefined", () => {
    const a = readAttribution("", "", "https://shop.example/");
    expect(a.utm_source).toBeUndefined();
    expect(a.referrer).toBeUndefined();
    expect(a.landing_url).toBe("https://shop.example/");
  });
});

describe("trackSession", () => {
  beforeEach(() => vi.clearAllMocks());

  it("posts session_id + attribution to /v1/track/session", async () => {
    await trackSession();
    expect(api.post).toHaveBeenCalledWith(
      "/v1/track/session",
      expect.objectContaining({ session_id: "sess-test" })
    );
  });

  it("swallows a rejected post", async () => {
    api.post.mockRejectedValueOnce({ response: { status: 500 } });
    await expect(trackSession()).resolves.toBeUndefined();
  });
});

describe("trackCartEvent", () => {
  beforeEach(() => vi.clearAllMocks());

  it("posts mapped fields to /v1/track/cart", async () => {
    await trackCartEvent({ action: "add", product_id: 7, variant_id: 3, quantity: 2, unit_price: 9.5 });
    expect(api.post).toHaveBeenCalledWith("/v1/track/cart", {
      session_id: "sess-test",
      action: "add",
      product_id: 7,
      variant_id: 3,
      quantity: 2,
      unit_price: 9.5,
    });
  });

  it("defaults missing variant_id/unit_price to null and swallows errors", async () => {
    api.post.mockRejectedValueOnce({ response: { status: 500 } });
    await expect(
      trackCartEvent({ action: "add", product_id: 1, quantity: 1 })
    ).resolves.toBeUndefined();
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `npx vitest run src/helpers/__tests__/tracking.test.js`
Expected: FAIL — cannot resolve `../tracking`.

- [ ] **Step 3: Write the helper**

Create `src/helpers/tracking.js`:

```js
import api, { getCsrfCookie } from "../api/axios";
import { getSessionId } from "./session";

// Pure: build the attribution payload from location/referrer values.
export function readAttribution(
  search = window.location.search,
  referrer = document.referrer,
  href = window.location.href
) {
  const p = new URLSearchParams(search || "");
  const pick = (k) => p.get(k) || undefined;
  return {
    utm_source: pick("utm_source"),
    utm_medium: pick("utm_medium"),
    utm_campaign: pick("utm_campaign"),
    utm_term: pick("utm_term"),
    utm_content: pick("utm_content"),
    referrer: referrer || undefined,
    landing_url: href || undefined,
  };
}

// Best-effort POST with the app's 419 CSRF-retry pattern. Never throws.
async function postTracking(url, body) {
  try {
    await api.post(url, body);
  } catch (e) {
    if (e?.response?.status === 419) {
      try {
        await getCsrfCookie();
        await api.post(url, body);
      } catch {
        /* ignore */
      }
    }
    // swallow all other errors — tracking must never break the app
  }
}

export function trackSession() {
  return postTracking("/v1/track/session", {
    session_id: getSessionId(),
    ...readAttribution(),
  });
}

export function trackCartEvent({ action, product_id, variant_id, quantity, unit_price }) {
  return postTracking("/v1/track/cart", {
    session_id: getSessionId(),
    action,
    product_id,
    variant_id: variant_id ?? null,
    quantity,
    unit_price: unit_price ?? null,
  });
}
```

- [ ] **Step 4: Wire session bootstrap into App.jsx**

In `src/App.jsx`, add the import near the other imports:

```js
import { trackSession } from "./helpers/tracking";
```

Then add a mount-only effect inside the `App` component, next to the existing `useEffect`s (it must run once — empty dependency array):

```js
  useEffect(() => {
    trackSession();
  }, []);
```

- [ ] **Step 5: Run test to verify it passes**

Run: `npx vitest run src/helpers/__tests__/tracking.test.js`
Expected: PASS (5 tests).

- [ ] **Step 6: Verify the app still builds**

Run: `npm run build`
Expected: build succeeds (no import/syntax errors from the App.jsx wiring).

- [ ] **Step 7: Commit**

```bash
git add src/helpers/tracking.js src/helpers/__tests__/tracking.test.js src/App.jsx
git commit -m "feat(web): session+UTM tracking helper, bootstrapped on app mount"
```

---

### Task 2: cart 'add' event from addToCart

**Files:**
- Modify: `src/Store/slices/CardSlice.js` (`addToCart` thunk)
- Modify: the main add-to-cart call site(s) to pass `unit_price` (find via grep — see Step 4)
- Test: `src/Store/slices/__tests__/CardSlice.track.test.js`

**Interfaces:**
- Consumes: `trackCartEvent` from `src/helpers/tracking.js` (Task 1).

- [ ] **Step 1: Write the failing test**

Create `src/Store/slices/__tests__/CardSlice.track.test.js`:

```js
import { describe, it, expect, vi, beforeEach } from "vitest";

vi.mock("../../../api/axios", () => ({
  default: { post: vi.fn(() => Promise.resolve({ data: { ok: true } })) },
  getCsrfCookie: vi.fn(),
}));
vi.mock("../../../helpers/tracking", () => ({ trackCartEvent: vi.fn() }));

import { trackCartEvent } from "../../../helpers/tracking";
import { addToCart } from "../CardSlice";

const run = (arg) => addToCart(arg)(vi.fn(), vi.fn(), undefined);

describe("addToCart tracking", () => {
  beforeEach(() => vi.clearAllMocks());

  it("fires an 'add' cart-track on success with variant_id + unit_price", async () => {
    await run({ product_id: 7, product_variant_id: 3, quantity: 2, unit_price: 9.5 });
    expect(trackCartEvent).toHaveBeenCalledWith({
      action: "add",
      product_id: 7,
      variant_id: 3,
      quantity: 2,
      unit_price: 9.5,
    });
  });

  it("does not fail the thunk if tracking throws", async () => {
    trackCartEvent.mockImplementationOnce(() => {
      throw new Error("boom");
    });
    const result = await run({ product_id: 1, product_variant_id: null, quantity: 1 });
    expect(result.type).toMatch(/fulfilled$/);
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `npx vitest run src/Store/slices/__tests__/CardSlice.track.test.js`
Expected: FAIL — `trackCartEvent` not called (import/wiring absent).

- [ ] **Step 3: Wire tracking into the addToCart thunk**

In `src/Store/slices/CardSlice.js`, add the import at the top (with the other imports):

```js
import { trackCartEvent } from "../../helpers/tracking";
```

Then change the `addToCart` thunk to accept `unit_price` and fire the track on success. Replace the existing `addToCart` definition with:

```js
export const addToCart = createAsyncThunk(
  'cart/addToCart',
  async ({ product_id, product_variant_id, quantity, unit_price }, { rejectWithValue }) => {
    try {
      const response = await api.post('/v1/cart', {
        product_id,
        product_variant_id,
        quantity
      });
      // Best-effort analytics — must never break the cart.
      try {
        trackCartEvent({
          action: 'add',
          product_id,
          variant_id: product_variant_id ?? null,
          quantity,
          unit_price,
        });
      } catch { /* ignore */ }
      return response.data;
    } catch (error) {
      return rejectWithValue(error.response.data);
    }
  }
);
```

- [ ] **Step 4: Pass unit_price from the main add-to-cart call site**

Find where the product page dispatches addToCart:

Run: `grep -rn "addToCart(" src/pages src/components | grep dispatch`

In the primary product add-to-cart dispatch (ProductPage — the one with the displayed price and quantity), add `unit_price` using the page's displayed unit-price value (the same price shown to the user for the selected variant). Example shape:

```js
dispatch(addToCart({ product_id, product_variant_id, quantity, unit_price: price }));
```

Use whatever variable already holds the selected variant's displayed unit price on that page. Do NOT change other call sites — they omit `unit_price` and the event is sent with `unit_price: null` (best-effort), which is fine. If you cannot identify a clear unit-price variable on the page, leave that call site unchanged and note it in your report rather than guessing a wrong field.

- [ ] **Step 5: Run test to verify it passes**

Run: `npx vitest run src/Store/slices/__tests__/CardSlice.track.test.js`
Expected: PASS (2 tests).

- [ ] **Step 6: Verify build**

Run: `npm run build`
Expected: build succeeds.

- [ ] **Step 7: Commit**

```bash
git add src/Store/slices/CardSlice.js src/Store/slices/__tests__/CardSlice.track.test.js src/pages/ProductPage.jsx
git commit -m "feat(web): track cart 'add' events with unit price"
```

(Adjust the `git add` path in Step 7 to whichever call-site file you actually edited.)

---

### Task 3: session_id on place-order

**Files:**
- Modify: `src/Store/slices/CheckoutSlice.js` (`initiatePayment` thunk)
- Test: `src/Store/slices/__tests__/CheckoutSlice.track.test.js`

**Interfaces:**
- Consumes: `getSessionId` from `src/helpers/session.js`.

- [ ] **Step 1: Write the failing test**

Create `src/Store/slices/__tests__/CheckoutSlice.track.test.js`:

```js
import { describe, it, expect, vi, beforeEach } from "vitest";

vi.mock("../../../api/axios", () => ({
  default: { post: vi.fn(() => Promise.resolve({ data: { ok: true } })) },
  getCsrfCookie: vi.fn(),
}));
vi.mock("../../../helpers/session", () => ({ getSessionId: () => "sess-test" }));

import api from "../../../api/axios";
import { initiatePayment } from "../CheckoutSlice";

describe("initiatePayment", () => {
  beforeEach(() => vi.clearAllMocks());

  it("includes session_id in the place-order payload", async () => {
    await initiatePayment({ address_id: 1, delivery_method_id: 2 })(vi.fn(), vi.fn(), undefined);
    expect(api.post).toHaveBeenCalledWith(
      "/v1/place-order",
      expect.objectContaining({ session_id: "sess-test", address_id: 1, delivery_method_id: 2 })
    );
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `npx vitest run src/Store/slices/__tests__/CheckoutSlice.track.test.js`
Expected: FAIL — posted body has no `session_id`.

- [ ] **Step 3: Add session_id to the payload**

In `src/Store/slices/CheckoutSlice.js`, add the import at the top:

```js
import { getSessionId } from "../../helpers/session";
```

Then in `initiatePayment`, spread the session id into the posted body:

```js
      const response = await api.post('/v1/place-order', {
        ...checkoutData,
        session_id: getSessionId(),
      });
```

- [ ] **Step 4: Run test to verify it passes**

Run: `npx vitest run src/Store/slices/__tests__/CheckoutSlice.track.test.js`
Expected: PASS (1 test).

- [ ] **Step 5: Verify build + commit**

Run: `npm run build`
Expected: build succeeds.

```bash
git add src/Store/slices/CheckoutSlice.js src/Store/slices/__tests__/CheckoutSlice.track.test.js
git commit -m "feat(web): send session_id with place-order for attribution"
```

---

## Definition of done

- `npx vitest run src/helpers/__tests__/tracking.test.js src/Store/slices/__tests__/CardSlice.track.test.js src/Store/slices/__tests__/CheckoutSlice.track.test.js` all green.
- `npm run build` succeeds.
- On app load the browser POSTs `/v1/track/session` (with UTM if present); adding to cart POSTs `/v1/track/cart` (`action:'add'`); placing an order includes `session_id`.
- `update`/`remove` cart tracking is deferred (noted in the spec); abandoned-cart value is add-based best-effort until then.

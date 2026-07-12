# Returns Web UI Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the mock web Returns UI with a real returns list + request form wired to the returns API.

**Architecture:** A `ReturnsSlice` (Redux Toolkit) exposes `requestReturn` + `fetchReturns` thunks over the shared `api`. `Returns.jsx` (the MyAccount Returns tab) consumes it: lists the user's returns and offers a "start a return" form (eligible order + reason + note).

**Tech Stack:** React 18 + Vite, Redux Toolkit, Vitest (jsdom, globals — `vitest.config.js`). Slice tests in `src/Store/slices/__tests__/*.test.js`. Run a file: `npx vitest run <path>` from `narzin-main`.

## Global Constraints

- Reason values sent to the API are exactly `damaged, wrong_item, not_as_described, no_longer_needed, other` (labels display-only). (from spec)
- Use the shared `api` (`src/api/axios.js`, Sanctum cookie auth) like every slice; thunks read `response.data.data` and `rejectWithValue(error.response?.data)`. (mirrors MyOrdersSlice)
- No `dangerouslySetInnerHTML`; all display values JSX-escaped. (from spec)
- Run web commands from `C:\xampp\htdocs\Narzin\narzin-main`.

---

### Task 1: ReturnsSlice + store registration

**Files:**
- Create: `src/Store/slices/ReturnsSlice.js`
- Modify: `src/Store/store.js` (register `returns` reducer)
- Test: `src/Store/slices/__tests__/ReturnsSlice.test.js`

**Interfaces:**
- Produces thunks `requestReturn({orderId, reason, note})`, `fetchReturns()` and default reducer from `src/Store/slices/ReturnsSlice.js`; state slice `returns` in the store. Task 2 consumes them.

- [ ] **Step 1: Write the failing test**

Create `src/Store/slices/__tests__/ReturnsSlice.test.js`:

```js
import { describe, it, expect, vi, beforeEach } from "vitest";

vi.mock("../../../api/axios", () => ({
  default: {
    post: vi.fn(() => Promise.resolve({ data: { status: true, data: { id: 1, status: "requested" } } })),
    get: vi.fn(() => Promise.resolve({ data: { status: true, data: [{ id: 1, reason: "damaged", status: "requested" }] } })),
  },
  getCsrfCookie: vi.fn(),
}));

import api from "../../../api/axios";
import reducer, { requestReturn, fetchReturns } from "../ReturnsSlice";

const run = (thunk) => thunk(vi.fn(), vi.fn(), undefined);

describe("ReturnsSlice thunks", () => {
  beforeEach(() => vi.clearAllMocks());

  it("requestReturn posts reason+note to the order returns endpoint", async () => {
    await run(requestReturn({ orderId: 7, reason: "damaged", note: "cracked" }));
    expect(api.post).toHaveBeenCalledWith("/v1/orders/7/returns", { reason: "damaged", note: "cracked" });
  });

  it("requestReturn rejection surfaces the backend message", async () => {
    api.post.mockRejectedValueOnce({ response: { data: { status: false, message: "A return already exists for this order" } } });
    const result = await run(requestReturn({ orderId: 7, reason: "damaged" }));
    expect(result.type).toMatch(/rejected$/);
    expect(result.payload.message).toBe("A return already exists for this order");
  });

  it("fetchReturns gets the returns list", async () => {
    const result = await run(fetchReturns());
    expect(api.get).toHaveBeenCalledWith("/v1/returns");
    expect(result.payload[0].reason).toBe("damaged");
  });
});

describe("ReturnsSlice reducer", () => {
  it("sets submitting on pending and stores returns on fetch fulfilled", () => {
    let state = reducer(undefined, { type: requestReturn.pending.type });
    expect(state.submitting).toBe(true);

    state = reducer(state, { type: fetchReturns.fulfilled.type, payload: [{ id: 1 }] });
    expect(state.returns).toHaveLength(1);

    state = reducer(state, { type: requestReturn.rejected.type, payload: { message: "boom" } });
    expect(state.submitError).toBe("boom");
    expect(state.submitting).toBe(false);
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `npx vitest run src/Store/slices/__tests__/ReturnsSlice.test.js`
Expected: FAIL — cannot resolve `../ReturnsSlice`.

- [ ] **Step 3: Write the slice**

Create `src/Store/slices/ReturnsSlice.js`:

```js
import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import api from "../../api/axios";

export const fetchReturns = createAsyncThunk(
  "returns/fetchReturns",
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get("/v1/returns");
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || { message: "Failed to load returns" });
    }
  }
);

export const requestReturn = createAsyncThunk(
  "returns/requestReturn",
  async ({ orderId, reason, note }, { rejectWithValue }) => {
    try {
      const response = await api.post(`/v1/orders/${orderId}/returns`, { reason, note });
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || { message: "Failed to request return" });
    }
  }
);

const returnsSlice = createSlice({
  name: "returns",
  initialState: {
    returns: [],
    status: "idle",
    error: null,
    submitting: false,
    submitError: null,
  },
  reducers: {
    clearSubmitError: (state) => { state.submitError = null; },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchReturns.pending, (state) => { state.status = "loading"; state.error = null; })
      .addCase(fetchReturns.fulfilled, (state, action) => { state.status = "succeeded"; state.returns = action.payload || []; })
      .addCase(fetchReturns.rejected, (state, action) => { state.status = "failed"; state.error = action.payload?.message || "Failed to load returns"; })
      .addCase(requestReturn.pending, (state) => { state.submitting = true; state.submitError = null; })
      .addCase(requestReturn.fulfilled, (state, action) => {
        state.submitting = false;
        if (action.payload) state.returns.unshift(action.payload);
      })
      .addCase(requestReturn.rejected, (state, action) => {
        state.submitting = false;
        state.submitError = action.payload?.message || "Failed to request return";
      });
  },
});

export const { clearSubmitError } = returnsSlice.actions;
export default returnsSlice.reducer;
```

- [ ] **Step 4: Register the reducer in the store**

In `src/Store/store.js`, add the import near the other slice imports:

```js
import returnsReducer from "./slices/ReturnsSlice";
```

Then add to the `reducer` map inside `configureStore({ reducer: { ... } })`:

```js
    returns: returnsReducer,
```

(Match the exact existing formatting/key style of the reducer object.)

- [ ] **Step 5: Run test to verify it passes**

Run: `npx vitest run src/Store/slices/__tests__/ReturnsSlice.test.js`
Expected: PASS (4 tests).

- [ ] **Step 6: Verify build**

Run: `npm run build`
Expected: build succeeds.

- [ ] **Step 7: Commit**

```bash
git add src/Store/slices/ReturnsSlice.js src/Store/store.js src/Store/slices/__tests__/ReturnsSlice.test.js
git commit -m "feat(web-returns): ReturnsSlice — requestReturn + fetchReturns thunks"
```

---

### Task 2: Real Returns.jsx (list + request form)

**Files:**
- Modify (replace): `src/components/pages/MyAccount/Returns.jsx`
- Test: `src/components/pages/MyAccount/__tests__/Returns.test.jsx` (light smoke — only if the `renderWithProviders` harness supports it; see Step 5)

**Interfaces:**
- Consumes: `ReturnsSlice` (`fetchReturns`, `requestReturn`, `clearSubmitError`) + the existing `fetchOrders` from `MyOrdersSlice` + `state.myOrders.orders` and `state.returns`.

- [ ] **Step 1: Replace the mock component**

Rewrite `src/components/pages/MyAccount/Returns.jsx` to remove the hardcoded mock `returns` array + fake tracking timeline, and render real data. It must:

- On mount: `dispatch(fetchReturns())`; and `dispatch(fetchOrders())` if `state.myOrders.orders` is empty (for the eligible-order picker).
- Read `{ returns, status, submitting, submitError }` from `state.returns` and `orders` from `state.myOrders`.
- **My returns section:** map `returns` → each row shows `order?.order_number` (or the order_id), a readable reason label, a status badge (color per status: requested=amber, approved=blue, rejected=red, refunded=green), and the requested date. Empty-state when `returns.length === 0`: "You haven't requested any returns yet."
- **Start a return form:**
  - reason options via a constant:
    ```js
    const REASONS = [
      { value: "damaged", label: "Item arrived damaged" },
      { value: "wrong_item", label: "Wrong item received" },
      { value: "not_as_described", label: "Not as described" },
      { value: "no_longer_needed", label: "No longer needed" },
      { value: "other", label: "Other" },
    ];
    ```
  - an order `<select>` listing eligible orders: `orders.filter(o => ['completed','processing'].includes(o.payment_status) && !returns.some(r => r.order_id === o.id && ['requested','approved','refunded'].includes(r.status)))` — each option shows the order number.
  - a reason `<select>` (REASONS), an optional note `<textarea maxLength={1000}>`, and a Submit button (disabled while `submitting` or when no order/reason selected).
  - On submit: `dispatch(requestReturn({ orderId, reason, note }))`, then `.unwrap().then(() => { ShowToast("Return requested", "success"); resetForm(); }).catch(() => {})` — the slice already stores `submitError`, which the form renders below the button.
  - Show `submitError` (backend message like "A return already exists for this order") when present.
- Use the existing Tailwind styling idiom from the surrounding MyAccount components; import `ShowToast` from the app's `components/ShowToast` (default export, called `ShowToast(msg, type)`), and `useDispatch`/`useSelector` from `react-redux`.
- All dynamic values via JSX (auto-escaped). No `dangerouslySetInnerHTML`.

Keep the component focused; if it grows large, that's acceptable for a page component, but do not add unrelated features (no tracking timeline, no cancel-return — out of scope).

- [ ] **Step 2: Verify the mock is gone**

Run: `grep -n "const returns = \[" src/components/pages/MyAccount/Returns.jsx`
Expected: no match (the hardcoded mock array is removed).

- [ ] **Step 3: Verify build**

Run: `npm run build`
Expected: build succeeds (no unresolved imports, valid JSX).

- [ ] **Step 4: Lint-level sanity (imports resolve)**

Confirm the component imports only real modules: `react`, `react-redux`, the two slices, `ShowToast`. Run `npm run build` already covers this.

- [ ] **Step 5: Light smoke test (best-effort)**

If `src/test/renderWithProviders.jsx` supports preloading multiple reducers, add `src/components/pages/MyAccount/__tests__/Returns.test.jsx`:

```jsx
import { describe, it, expect } from "vitest";
import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../test/renderWithProviders";
import returnsReducer from "../../../../Store/slices/ReturnsSlice";
import myOrdersReducer from "../../../../Store/slices/MyOrdersSlice";
import Returns from "../Returns";

describe("Returns", () => {
  it("shows the empty-state when there are no returns", () => {
    renderWithProviders(<Returns />, {
      reducers: { returns: returnsReducer, myOrders: myOrdersReducer },
      preloadedState: { returns: { returns: [], status: "succeeded", submitting: false, submitError: null }, myOrders: { orders: [] } },
    });
    expect(screen.getByText(/haven't requested any returns/i)).toBeInTheDocument();
  });
});
```

Run: `npx vitest run src/components/pages/MyAccount/__tests__/Returns.test.jsx`
Expected: PASS. If `renderWithProviders` doesn't accept per-test `reducers`/`preloadedState` in this shape (check its signature first — mirror an existing component test like the navbar `__tests__`), SKIP this smoke test and note it in the report; the slice tests (Task 1) + `npm run build` are the binding coverage. Do NOT fight the harness.

- [ ] **Step 6: Commit**

```bash
git add src/components/pages/MyAccount/Returns.jsx
# include the smoke test only if you added it:
# git add src/components/pages/MyAccount/__tests__/Returns.test.jsx
git commit -m "feat(web-returns): real Returns tab — list + request form wired to the API"
```

---

## Definition of done

- `npx vitest run src/Store/slices/__tests__/ReturnsSlice.test.js` green; `npm run build` succeeds.
- The Returns tab fetches and lists the user's real returns (with status badges + empty-state) and offers a request form (eligible order + reason + optional note) that POSTs to the API, toasts on success, and shows the backend message on failure.
- The mock `returns` array + fake tracking timeline are gone. `Return.jsx` (policy page) untouched.

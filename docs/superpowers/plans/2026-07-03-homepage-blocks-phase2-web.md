# Homepage Content Blocks — Phase 2 (React Web Renderer) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the hardcoded React homepage with a server-driven block renderer consuming `GET /api/v1/home` — Shein-density layout in Narzin's navy/sand/gold brand, mobile-first, RTL-correct, with skeletons and an API-failure fallback.

**Architecture:** A new `HomeSlice` fetches the resolved block list per locale. Layout-level blocks (`announcement_bar`, `popup`) render site-wide from `Layout.jsx`; the six page-level types render on `Home.jsx` through a `BlockRenderer` with a `type → component` registry that silently skips unknown types. Carousels (hero, category circles, product rails) use native CSS scroll-snap — no new slider dependency, native touch swipe, RTL-safe. The legacy `BeforeNavSlice`/hardcoded hero are deleted.

**Tech Stack:** React 18 + Vite 6, Redux Toolkit 2, react-router-dom 7, Tailwind 3 (+ existing daisyUI), i18next (languages `ar` default/RTL and `du` for German — the API normalizes `du`→`de`), axios instance `src/api/axios.js`. NEW dev deps: vitest + @testing-library/react + @testing-library/jest-dom + jsdom.

**Spec:** `docs/superpowers/specs/2026-07-02-homepage-blocks-design.md` §6 (web homepage). Backend (Phase 1) is merged: the API and block shapes below are live.

## Global Constraints

- All work in `narzin-main/`; run every command from `C:\xampp\htdocs\Narzin\narzin-main`. Git repo root is `C:\xampp\htdocs\Narzin` (commit paths accordingly).
- API contract (Phase 1, live): `GET /v1/home?platform=web&locale={lng}` via the shared axios instance → `{status: true, data: [{id, type, content}, ...]}`. Block types: `announcement_bar`, `popup`, `hero_slider`, `category_grid`, `product_rail`, `countdown_banner`, `info_strip`, `promo_tiles`. Unknown types must render nothing (forward compatibility). Pass `i18n.language` as `locale` verbatim (`ar` or `du`; server normalizes).
- Resolved content shapes (all text already locale-collapsed to plain strings; all image values are absolute URLs):
  - announcement_bar: `{text, link, bg_color, text_color}`
  - popup: `{image, title, text, button_label, link, frequency: {mode: 'once_per_session'|'once_per_days', days}, delay_seconds}`
  - hero_slider: `{slides: [{image, title, subtitle, link}]}`
  - category_grid: `{categories: [{id, name, image}]}`
  - product_rail: `{title, rule, products: [{id, name_arabic, name_german, slug_arabic, slug_german, image, min_price, min_price_iqd, min_price_variant_id}]}`
  - countdown_banner: `{text, ends_at (ISO8601), link, image, bg_color, text_color}`
  - info_strip: `{items: [{icon: 'truck'|'shield'|'star'|'returns'|'support'|'tag', text, link}]}`
  - promo_tiles: `{tiles: [{image, label, link}]}`
  - Links: `{type: 'category'|'product'|'url', value}` or `null`. Mapping: product → `/product/{value}`; category → `/store?category_id={value}`; url → external `<a href target="_blank" rel="noopener">`; null → not clickable.
- Brand (DESIGN.md): Tailwind color tokens to add — `narzin-navy: #141923`, `narzin-sand: #C5A880`, `narzin-gold: #D4AF37`, `narzin-bg: #F7F9FB`. Shein's density, Narzin's palette: no red urgency; countdown is the only urgency device. Dual currency display: `€{min_price}` primary + `{min_price_iqd} IQD` secondary.
- Mobile-first; RTL mirroring for `ar` (App.jsx already sets `document.documentElement.dir`; use direction-agnostic CSS — `start/end` utilities, scroll-snap works in RTL natively); images below the fold get `loading="lazy"`; per-block skeletons while loading (no layout jump); if the home fetch fails entirely, `Home.jsx` falls back to the existing `Categories` + `ProductsSection` components fed by the `categories`/`products` props.
- Popup rules: max ONE popup per page visit; respects `delay_seconds`; frequency capping — `once_per_session` via sessionStorage key `home_popup_seen_{id}`, `once_per_days` via localStorage key `home_popup_seen_{id}` storing an epoch-ms timestamp.
- Gates for every task: `npx vitest run` passes AND `npm run build` succeeds. (`npm run lint` has pre-existing state — do not introduce NEW lint errors in files you touch.)
- Commits: conventional style `feat(web-home): ...`, each ending with the trailer line `Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>`.
- Locale/product-name selection in new components: `i18n.language === 'ar' ? name_arabic : (name_german || name_arabic)`.

## File Structure

**Created:**
- `narzin-main/vitest.config.js`, `narzin-main/src/test/setup.js`, `narzin-main/src/test/renderWithProviders.jsx` — test infra
- `narzin-main/src/Store/slices/HomeSlice.js` — fetch + state + selectors
- `narzin-main/src/components/pages/home/blocks/blockLink.js` — link→target helper + `SmartLink`
- `.../blocks/useCountdown.js` — ticking countdown hook
- `.../blocks/popupFrequency.js` — pure popup gating logic
- `.../blocks/BlockRenderer.jsx` — registry + renderer + skeletons
- `.../blocks/BlockSkeleton.jsx`
- `.../blocks/AnnouncementBar.jsx`, `HeroSlider.jsx`, `CategoryCircles.jsx`, `ProductRail.jsx`, `RailProductCard.jsx`, `CountdownBanner.jsx`, `InfoStrip.jsx`, `PromoTiles.jsx`, `HomePopup.jsx`
- Tests: `.../blocks/__tests__/blockLink.test.jsx`, `useCountdown.test.js`, `popupFrequency.test.js`, `BlockRenderer.test.jsx`, `AnnouncementBar.test.jsx`, `ProductRail.test.jsx`, `HomePopup.test.jsx`; `narzin-main/src/Store/slices/__tests__/HomeSlice.test.js`
- `narzin-main/src/pages/HomeFallback.jsx` — extracted legacy fallback view

**Modified:**
- `narzin-main/package.json` (devDeps + `"test": "vitest run"`), `narzin-main/tailwind.config.js` (brand colors)
- `narzin-main/src/Store/store.js` (swap `beforeNav` reducer for `home`)
- `narzin-main/src/App.jsx` (dispatch `fetchHome`, refetch on language change, drop `fetchBeforeNav`)
- `narzin-main/src/components/Layout.jsx` (announcement bar above navbar + popup portal, from home feed)
- `narzin-main/src/components/New/NavBar.jsx` (remove legacy beforeNav strip)
- `narzin-main/src/pages/Home.jsx` (BlockRenderer + fallback)

**Deleted (Task 10):**
- `narzin-main/src/components/pages/home/Banners.jsx`, `narzin-main/src/Store/slices/BeforeNavSlice.js`

---

### Task 1: Test infrastructure (vitest + Testing Library)

**Files:**
- Modify: `narzin-main/package.json`
- Create: `narzin-main/vitest.config.js`, `narzin-main/src/test/setup.js`, `narzin-main/src/test/renderWithProviders.jsx`, `narzin-main/src/test/smoke.test.jsx`

**Interfaces:**
- Produces: `npx vitest run` works; `renderWithProviders(ui, {preloadedState})` renders with a Redux store (reducers passed in by later tasks' tests), a MemoryRouter, and a minimal i18n instance whose language defaults to `du` (call `renderWithProviders(ui, {language: 'ar'})` to switch). It returns Testing Library's render result plus `{store}`.

- [ ] **Step 1: Install dev dependencies**

Run (in `narzin-main/`): `npm install --save-dev vitest @testing-library/react @testing-library/jest-dom jsdom`
Expected: packages added to `devDependencies`, lockfile updated, exit 0.

- [ ] **Step 2: Add the test script**

In `narzin-main/package.json` `"scripts"`, add: `"test": "vitest run"` (keep existing scripts unchanged).

- [ ] **Step 3: Create vitest config and setup**

Create `narzin-main/vitest.config.js`:

```js
import { defineConfig } from "vitest/config";
import react from "@vitejs/plugin-react";

export default defineConfig({
  plugins: [react()],
  test: {
    environment: "jsdom",
    globals: true,
    setupFiles: "./src/test/setup.js",
  },
});
```

Create `narzin-main/src/test/setup.js`:

```js
import "@testing-library/jest-dom/vitest";
```

- [ ] **Step 4: Create the providers helper**

Create `narzin-main/src/test/renderWithProviders.jsx`:

```jsx
import React from "react";
import { render } from "@testing-library/react";
import { Provider } from "react-redux";
import { configureStore } from "@reduxjs/toolkit";
import { MemoryRouter } from "react-router-dom";
import { I18nextProvider } from "react-i18next";
import i18n from "i18next";
import { initReactI18next } from "react-i18next";

export function makeTestI18n(language = "du") {
  const instance = i18n.createInstance();
  instance.use(initReactI18next).init({
    lng: language,
    fallbackLng: "du",
    resources: { ar: { translation: {} }, du: { translation: {} } },
    interpolation: { escapeValue: false },
  });
  return instance;
}

export function renderWithProviders(
  ui,
  { reducers = {}, preloadedState = {}, language = "du", route = "/" } = {}
) {
  const store = configureStore({
    reducer: { _: (s = {}) => s, ...reducers },
    preloadedState,
  });
  const testI18n = makeTestI18n(language);

  const result = render(
    <Provider store={store}>
      <I18nextProvider i18n={testI18n}>
        <MemoryRouter initialEntries={[route]}>{ui}</MemoryRouter>
      </I18nextProvider>
    </Provider>
  );

  return { ...result, store };
}
```

- [ ] **Step 5: Write a smoke test and run it**

Create `narzin-main/src/test/smoke.test.jsx`:

```jsx
import { screen } from "@testing-library/react";
import { renderWithProviders } from "./renderWithProviders";

test("test harness renders a component", () => {
  renderWithProviders(<p>hello narzin</p>);
  expect(screen.getByText("hello narzin")).toBeInTheDocument();
});
```

Run: `npx vitest run`
Expected: 1 test passes.

- [ ] **Step 6: Verify build still works**

Run: `npm run build`
Expected: vite build succeeds (exit 0).

- [ ] **Step 7: Commit**

```bash
git add narzin-main/package.json narzin-main/package-lock.json narzin-main/vitest.config.js narzin-main/src/test
git commit -m "feat(web-home): vitest + testing-library test infrastructure"
```

---

### Task 2: Brand colors + HomeSlice

**Files:**
- Modify: `narzin-main/tailwind.config.js`, `narzin-main/src/Store/store.js`
- Create: `narzin-main/src/Store/slices/HomeSlice.js`
- Test: `narzin-main/src/Store/slices/__tests__/HomeSlice.test.js`

**Interfaces:**
- Consumes: `api` from `src/api/axios.js`.
- Produces:
  - `fetchHome(locale)` async thunk → GET `/v1/home?platform=web&locale={locale}`.
  - State `state.home = {blocks: [], status: 'idle'|'loading'|'succeeded'|'failed', error}`.
  - Selectors: `selectHomeStatus(state)`, `selectLayoutBlocks(state)` (types `announcement_bar` + `popup`, in feed order), `selectPageBlocks(state)` (everything else, in feed order).
  - Tailwind classes `bg-narzin-navy`, `text-narzin-sand`, `text-narzin-gold`, `bg-narzin-bg` etc.

- [ ] **Step 1: Write the failing tests**

Create `narzin-main/src/Store/slices/__tests__/HomeSlice.test.js`:

```js
import { describe, it, expect, vi } from "vitest";
import { configureStore } from "@reduxjs/toolkit";

vi.mock("../../../api/axios", () => ({
  default: { get: vi.fn() },
}));

import api from "../../../api/axios";
import homeReducer, {
  fetchHome,
  selectLayoutBlocks,
  selectPageBlocks,
  selectHomeStatus,
} from "../HomeSlice";

const makeStore = () => configureStore({ reducer: { home: homeReducer } });

const feed = [
  { id: 1, type: "announcement_bar", content: { text: "hi" } },
  { id: 2, type: "hero_slider", content: { slides: [] } },
  { id: 3, type: "popup", content: { title: "app" } },
  { id: 4, type: "product_rail", content: { title: "deals", products: [] } },
];

describe("HomeSlice", () => {
  it("stores blocks on success and splits layout vs page blocks", async () => {
    api.get.mockResolvedValueOnce({ data: { status: true, data: feed } });
    const store = makeStore();

    await store.dispatch(fetchHome("ar"));

    expect(api.get).toHaveBeenCalledWith("/v1/home", {
      params: { platform: "web", locale: "ar" },
    });
    const state = store.getState();
    expect(selectHomeStatus(state)).toBe("succeeded");
    expect(selectLayoutBlocks(state).map((b) => b.id)).toEqual([1, 3]);
    expect(selectPageBlocks(state).map((b) => b.id)).toEqual([2, 4]);
  });

  it("marks failed on error and keeps blocks empty", async () => {
    api.get.mockRejectedValueOnce(new Error("network down"));
    const store = makeStore();

    await store.dispatch(fetchHome("du"));

    const state = store.getState();
    expect(selectHomeStatus(state)).toBe("failed");
    expect(selectPageBlocks(state)).toEqual([]);
  });
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `npx vitest run src/Store/slices/__tests__/HomeSlice.test.js`
Expected: FAIL — module `../HomeSlice` not found.

- [ ] **Step 3: Implement the slice**

Create `narzin-main/src/Store/slices/HomeSlice.js`:

```js
import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import api from "../../api/axios";

const LAYOUT_TYPES = ["announcement_bar", "popup"];

export const fetchHome = createAsyncThunk(
  "home/fetchHome",
  async (locale, { rejectWithValue }) => {
    try {
      const response = await api.get("/v1/home", {
        params: { platform: "web", locale },
      });
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data?.message || error.message);
    }
  }
);

const HomeSlice = createSlice({
  name: "home",
  initialState: { blocks: [], status: "idle", error: null },
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchHome.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchHome.fulfilled, (state, action) => {
        state.status = "succeeded";
        state.blocks = Array.isArray(action.payload) ? action.payload : [];
      })
      .addCase(fetchHome.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload || "Failed to fetch homepage";
      });
  },
});

export const selectHomeStatus = (state) => state.home.status;
export const selectLayoutBlocks = (state) =>
  state.home.blocks.filter((b) => LAYOUT_TYPES.includes(b.type));
export const selectPageBlocks = (state) =>
  state.home.blocks.filter((b) => !LAYOUT_TYPES.includes(b.type));

export default HomeSlice.reducer;
```

- [ ] **Step 4: Register the reducer**

In `narzin-main/src/Store/store.js`: import `homeReducer from './slices/HomeSlice'` and add `home: homeReducer` to the reducer map. Do NOT remove `beforeNav` yet (that happens in Task 10 — App/NavBar still reference it until then).

- [ ] **Step 5: Add brand colors**

In `narzin-main/tailwind.config.js`, set:

```js
  theme: {
    extend: {
      colors: {
        "narzin-navy": "#141923",
        "narzin-sand": "#C5A880",
        "narzin-gold": "#D4AF37",
        "narzin-bg": "#F7F9FB",
      },
    },
  },
```

- [ ] **Step 6: Run tests + build**

Run: `npx vitest run` then `npm run build`
Expected: all tests pass; build succeeds.

- [ ] **Step 7: Commit**

```bash
git add narzin-main/src/Store narzin-main/tailwind.config.js
git commit -m "feat(web-home): HomeSlice with layout/page block selectors and brand colors"
```

---

### Task 3: Shared primitives — blockLink/SmartLink, useCountdown, popupFrequency

**Files:**
- Create: `narzin-main/src/components/pages/home/blocks/blockLink.js`, `useCountdown.js`, `popupFrequency.js`
- Test: `narzin-main/src/components/pages/home/blocks/__tests__/blockLink.test.jsx`, `useCountdown.test.js`, `popupFrequency.test.js`

**Interfaces:**
- Produces:
  - `linkTarget(link)` → `{kind: 'internal', to}` | `{kind: 'external', href}` | `null`.
  - `<SmartLink link={...} className children>` — renders `<Link to>` for internal, `<a href target="_blank" rel="noopener noreferrer">` for external, plain `<div className>` when null.
  - `useCountdown(endsAtIso)` → `{days, hours, minutes, seconds, expired}` ticking every second.
  - `shouldShowPopup(content, now = Date.now())` → boolean; `markPopupSeen(content, now = Date.now())` — implement the storage contract from Global Constraints.

- [ ] **Step 1: Write the failing tests**

Create `narzin-main/src/components/pages/home/blocks/__tests__/blockLink.test.jsx`:

```jsx
import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import { linkTarget, SmartLink } from "../blockLink";

describe("linkTarget", () => {
  it("maps product, category, url and null", () => {
    expect(linkTarget({ type: "product", value: 7 })).toEqual({
      kind: "internal",
      to: "/product/7",
    });
    expect(linkTarget({ type: "category", value: 3 })).toEqual({
      kind: "internal",
      to: "/store?category_id=3",
    });
    expect(linkTarget({ type: "url", value: "https://x.test/a" })).toEqual({
      kind: "external",
      href: "https://x.test/a",
    });
    expect(linkTarget(null)).toBeNull();
    expect(linkTarget({ type: "weird", value: 1 })).toBeNull();
  });
});

describe("SmartLink", () => {
  it("renders router link for internal targets", () => {
    renderWithProviders(
      <SmartLink link={{ type: "product", value: 9 }}>go</SmartLink>
    );
    expect(screen.getByRole("link", { name: "go" })).toHaveAttribute(
      "href",
      "/product/9"
    );
  });

  it("renders external anchor with safe rel", () => {
    renderWithProviders(
      <SmartLink link={{ type: "url", value: "https://x.test" }}>out</SmartLink>
    );
    const a = screen.getByRole("link", { name: "out" });
    expect(a).toHaveAttribute("target", "_blank");
    expect(a).toHaveAttribute("rel", expect.stringContaining("noopener"));
  });

  it("renders a plain wrapper when there is no link", () => {
    renderWithProviders(<SmartLink link={null}>flat</SmartLink>);
    expect(screen.queryByRole("link")).toBeNull();
    expect(screen.getByText("flat")).toBeInTheDocument();
  });
});
```

Create `narzin-main/src/components/pages/home/blocks/__tests__/useCountdown.test.js`:

```js
import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { renderHook, act } from "@testing-library/react";
import { useCountdown } from "../useCountdown";

describe("useCountdown", () => {
  beforeEach(() => vi.useFakeTimers());
  afterEach(() => vi.useRealTimers());

  it("counts down and ticks", () => {
    vi.setSystemTime(new Date("2026-07-03T10:00:00Z"));
    const { result } = renderHook(() =>
      useCountdown("2026-07-04T11:01:05Z")
    );
    expect(result.current).toMatchObject({
      days: 1,
      hours: 1,
      minutes: 1,
      seconds: 5,
      expired: false,
    });
    act(() => vi.advanceTimersByTime(5000));
    expect(result.current.seconds).toBe(0);
    expect(result.current.minutes).toBe(1);
  });

  it("reports expired for past dates", () => {
    vi.setSystemTime(new Date("2026-07-03T10:00:00Z"));
    const { result } = renderHook(() => useCountdown("2026-07-01T00:00:00Z"));
    expect(result.current.expired).toBe(true);
  });
});
```

Create `narzin-main/src/components/pages/home/blocks/__tests__/popupFrequency.test.js`:

```js
import { describe, it, expect, beforeEach } from "vitest";
import { shouldShowPopup, markPopupSeen } from "../popupFrequency";

const sessionPopup = {
  id: 5,
  frequency: { mode: "once_per_session", days: 0 },
};
const daysPopup = { id: 6, frequency: { mode: "once_per_days", days: 7 } };

describe("popup frequency capping", () => {
  beforeEach(() => {
    sessionStorage.clear();
    localStorage.clear();
  });

  it("shows a session popup once per session", () => {
    expect(shouldShowPopup(sessionPopup)).toBe(true);
    markPopupSeen(sessionPopup);
    expect(shouldShowPopup(sessionPopup)).toBe(false);
  });

  it("shows a days popup again only after N days", () => {
    const now = Date.parse("2026-07-03T10:00:00Z");
    expect(shouldShowPopup(daysPopup, now)).toBe(true);
    markPopupSeen(daysPopup, now);
    const sixDaysLater = now + 6 * 24 * 60 * 60 * 1000;
    const eightDaysLater = now + 8 * 24 * 60 * 60 * 1000;
    expect(shouldShowPopup(daysPopup, sixDaysLater)).toBe(false);
    expect(shouldShowPopup(daysPopup, eightDaysLater)).toBe(true);
  });

  it("popups are independent per block id", () => {
    markPopupSeen(sessionPopup);
    expect(shouldShowPopup({ ...sessionPopup, id: 99 })).toBe(true);
  });
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `npx vitest run src/components/pages/home/blocks/__tests__`
Expected: FAIL — modules not found.

- [ ] **Step 3: Implement the three modules**

Create `narzin-main/src/components/pages/home/blocks/blockLink.js`:

```jsx
import React from "react";
import { Link } from "react-router-dom";

export function linkTarget(link) {
  if (!link || !link.type) return null;
  if (link.type === "product") return { kind: "internal", to: `/product/${link.value}` };
  if (link.type === "category")
    return { kind: "internal", to: `/store?category_id=${link.value}` };
  if (link.type === "url") return { kind: "external", href: link.value };
  return null;
}

export function SmartLink({ link, className, children, ...rest }) {
  const target = linkTarget(link);
  if (!target) {
    return (
      <div className={className} {...rest}>
        {children}
      </div>
    );
  }
  if (target.kind === "external") {
    return (
      <a
        href={target.href}
        target="_blank"
        rel="noopener noreferrer"
        className={className}
        {...rest}
      >
        {children}
      </a>
    );
  }
  return (
    <Link to={target.to} className={className} {...rest}>
      {children}
    </Link>
  );
}
```

Create `narzin-main/src/components/pages/home/blocks/useCountdown.js`:

```js
import { useEffect, useState } from "react";

function remaining(endsAtIso, nowMs) {
  const diff = Date.parse(endsAtIso) - nowMs;
  if (Number.isNaN(diff) || diff <= 0) {
    return { days: 0, hours: 0, minutes: 0, seconds: 0, expired: true };
  }
  const totalSeconds = Math.floor(diff / 1000);
  return {
    days: Math.floor(totalSeconds / 86400),
    hours: Math.floor((totalSeconds % 86400) / 3600),
    minutes: Math.floor((totalSeconds % 3600) / 60),
    seconds: totalSeconds % 60,
    expired: false,
  };
}

export function useCountdown(endsAtIso) {
  const [state, setState] = useState(() => remaining(endsAtIso, Date.now()));

  useEffect(() => {
    setState(remaining(endsAtIso, Date.now()));
    const timer = setInterval(() => {
      setState(remaining(endsAtIso, Date.now()));
    }, 1000);
    return () => clearInterval(timer);
  }, [endsAtIso]);

  return state;
}
```

Create `narzin-main/src/components/pages/home/blocks/popupFrequency.js`:

```js
const key = (content) => `home_popup_seen_${content.id}`;
const DAY_MS = 24 * 60 * 60 * 1000;

export function shouldShowPopup(content, now = Date.now()) {
  const mode = content?.frequency?.mode || "once_per_session";
  if (mode === "once_per_days") {
    const seenAt = Number(localStorage.getItem(key(content)) || 0);
    if (!seenAt) return true;
    const days = Number(content.frequency?.days || 0);
    return now - seenAt >= days * DAY_MS;
  }
  return sessionStorage.getItem(key(content)) === null;
}

export function markPopupSeen(content, now = Date.now()) {
  const mode = content?.frequency?.mode || "once_per_session";
  if (mode === "once_per_days") {
    localStorage.setItem(key(content), String(now));
  } else {
    sessionStorage.setItem(key(content), "1");
  }
}
```

Note: `HomePopup` (Task 9) passes a content object that includes the block `id` — the popup block component receives `block` (with `id`) and spreads it in, so `content.id` is always present here.

- [ ] **Step 4: Run tests to verify they pass**

Run: `npx vitest run src/components/pages/home/blocks/__tests__`
Expected: all pass (8 tests).

- [ ] **Step 5: Build + commit**

Run: `npm run build` (must pass), then:

```bash
git add narzin-main/src/components/pages/home/blocks
git commit -m "feat(web-home): block link helper, countdown hook, popup frequency capping"
```

---

### Task 4: BlockRenderer + skeletons + Home page rewrite with fallback

**Files:**
- Create: `narzin-main/src/components/pages/home/blocks/BlockRenderer.jsx`, `BlockSkeleton.jsx`, `narzin-main/src/pages/HomeFallback.jsx`
- Modify: `narzin-main/src/pages/Home.jsx`, `narzin-main/src/App.jsx`
- Test: `narzin-main/src/components/pages/home/blocks/__tests__/BlockRenderer.test.jsx`

**Interfaces:**
- Consumes: `selectPageBlocks`, `selectHomeStatus`, `fetchHome` (Task 2).
- Produces:
  - `<BlockRenderer blocks={[...]} />` — renders each block through the registry; unknown types render nothing. Registry starts with placeholder `null` entries and later tasks register real components by importing them in `BlockRenderer.jsx` — THE REGISTRY LIVES ONLY HERE; later tasks add one import + one map line each.
  - `<BlockSkeleton variant="bar"|"hero"|"circles"|"rail" />` pulse placeholders.
  - `Home.jsx` renders: loading → skeleton stack; failed → `<HomeFallback products categories />`; succeeded → `<BlockRenderer blocks={pageBlocks} />` (empty feed also falls back).
  - `App.jsx` dispatches `fetchHome(i18n.language)` on mount AND whenever `i18n.language` changes.

- [ ] **Step 1: Write the failing tests**

Create `narzin-main/src/components/pages/home/blocks/__tests__/BlockRenderer.test.jsx`:

```jsx
import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import BlockRenderer, { registerBlockForTests } from "../BlockRenderer";

const Stub = ({ content }) => <div>stub:{content.text}</div>;

describe("BlockRenderer", () => {
  it("renders known types in order and skips unknown types", () => {
    registerBlockForTests("announcement_bar", Stub);
    const blocks = [
      { id: 1, type: "announcement_bar", content: { text: "one" } },
      { id: 2, type: "from_the_future", content: {} },
      { id: 3, type: "announcement_bar", content: { text: "two" } },
    ];
    renderWithProviders(<BlockRenderer blocks={blocks} />);
    const rendered = screen.getAllByText(/stub:/).map((el) => el.textContent);
    expect(rendered).toEqual(["stub:one", "stub:two"]);
  });

  it("renders nothing for an empty list", () => {
    const { container } = renderWithProviders(<BlockRenderer blocks={[]} />);
    expect(container.textContent).toBe("");
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `npx vitest run src/components/pages/home/blocks/__tests__/BlockRenderer.test.jsx`
Expected: FAIL — module not found.

- [ ] **Step 3: Implement BlockRenderer and BlockSkeleton**

Create `narzin-main/src/components/pages/home/blocks/BlockSkeleton.jsx`:

```jsx
import React from "react";

const shapes = {
  bar: "h-9 w-full",
  hero: "h-56 sm:h-72 md:h-96 w-full",
  circles: "h-28 w-full",
  rail: "h-64 w-full",
};

const BlockSkeleton = ({ variant = "rail" }) => (
  <div className="px-0">
    <div
      className={`animate-pulse bg-gray-200 rounded-lg ${shapes[variant] || shapes.rail}`}
      data-testid={`skeleton-${variant}`}
    />
  </div>
);

export default BlockSkeleton;
```

Create `narzin-main/src/components/pages/home/blocks/BlockRenderer.jsx`:

```jsx
import React from "react";

// Later tasks import their block component here and add it to the registry.
// Types that render at Layout level (announcement_bar, popup) or are not yet
// built stay unregistered — unregistered/unknown types render nothing.
const registry = {};

// Test hook: lets tests inject a stub without depending on real block components.
export function registerBlockForTests(type, Component) {
  registry[type] = Component;
}

const BlockRenderer = ({ blocks = [] }) => (
  <>
    {blocks.map((block) => {
      const Component = registry[block.type];
      if (!Component) return null;
      return <Component key={block.id} content={block.content} block={block} />;
    })}
  </>
);

export default BlockRenderer;
```

- [ ] **Step 4: Run the renderer test — must pass**

Run: `npx vitest run src/components/pages/home/blocks/__tests__/BlockRenderer.test.jsx`
Expected: 2 tests pass.

- [ ] **Step 5: Extract the fallback view**

Create `narzin-main/src/pages/HomeFallback.jsx` (this is the old homepage minus the hardcoded hero — it reuses the existing components untouched):

```jsx
import React from "react";
import Categories from "../components/pages/home/Categories";
import ProductsSection from "../components/pages/home/ProductsSection";
import { useTranslation } from "react-i18next";

// Rendered when the home feed API fails or returns no blocks: a safe,
// data-driven version of the legacy homepage so customers never see a blank page.
const HomeFallback = ({ categories, products }) => {
  const { t } = useTranslation();
  return (
    <div className="pt-14">
      <Categories data={categories} />
      <ProductsSection data={products} title={t("home.recently_added")} />
    </div>
  );
};

export default HomeFallback;
```

- [ ] **Step 6: Rewrite Home.jsx**

Replace `narzin-main/src/pages/Home.jsx` with:

```jsx
import React from "react";
import { useSelector } from "react-redux";
import BlockRenderer from "../components/pages/home/blocks/BlockRenderer";
import BlockSkeleton from "../components/pages/home/blocks/BlockSkeleton";
import HomeFallback from "./HomeFallback";
import {
  selectHomeStatus,
  selectPageBlocks,
} from "../Store/slices/HomeSlice";

const Home = ({ categories, products }) => {
  const status = useSelector(selectHomeStatus);
  const pageBlocks = useSelector(selectPageBlocks);

  if (status === "loading" || status === "idle") {
    return (
      <div className="pt-14 space-y-4" data-testid="home-skeletons">
        <BlockSkeleton variant="hero" />
        <BlockSkeleton variant="circles" />
        <BlockSkeleton variant="rail" />
        <BlockSkeleton variant="rail" />
      </div>
    );
  }

  if (status === "failed" || pageBlocks.length === 0) {
    return <HomeFallback categories={categories} products={products} />;
  }

  return (
    <div className="pt-14 pb-8 bg-narzin-bg min-h-screen">
      <BlockRenderer blocks={pageBlocks} />
    </div>
  );
};

export default Home;
```

(The `pt-14` offsets the fixed navbar, same as the old hero's `mt-14`.)

- [ ] **Step 7: Wire fetching in App.jsx**

In `narzin-main/src/App.jsx`:
1. Add `import { fetchHome } from "./Store/slices/HomeSlice";`
2. Add a language-aware fetch effect after the existing mount effect:

```jsx
  useEffect(() => {
    dispatch(fetchHome(i18n.language));
  }, [dispatch, i18n.language]);
```

Keep `fetchBeforeNav`, `fetchCategories`, `fetchProducts` untouched for now (fallback and other pages still use them; beforeNav is removed in Task 10).

- [ ] **Step 8: Full test run + build**

Run: `npx vitest run` then `npm run build`
Expected: all tests pass; build succeeds. (Home.jsx renders skeletons/fallback/renderer — no visual hero yet; that's expected until Tasks 6–9 register components.)

- [ ] **Step 9: Commit**

```bash
git add narzin-main/src/components/pages/home/blocks narzin-main/src/pages/Home.jsx narzin-main/src/pages/HomeFallback.jsx narzin-main/src/App.jsx
git commit -m "feat(web-home): block renderer, skeletons, home rewrite with API fallback"
```

---

### Task 5: AnnouncementBar (replaces the legacy before-nav strip)

**Files:**
- Create: `narzin-main/src/components/pages/home/blocks/AnnouncementBar.jsx`
- Modify: `narzin-main/src/components/New/NavBar.jsx`
- Test: `narzin-main/src/components/pages/home/blocks/__tests__/AnnouncementBar.test.jsx`

**Interfaces:**
- Consumes: `selectLayoutBlocks` (Task 2), `SmartLink` (Task 3), `homeReducer` for tests.
- Produces: `<AnnouncementBar />` — self-contained: selects the first `announcement_bar` block from `state.home`, renders a slim dismissible strip (colors from content with defaults `#141923`/`#C5A880`), or null when there is none / it was dismissed this session (sessionStorage key `home_announcement_dismissed_{id}`).

- [ ] **Step 1: Write the failing tests**

Create `narzin-main/src/components/pages/home/blocks/__tests__/AnnouncementBar.test.jsx`:

```jsx
import { screen, fireEvent } from "@testing-library/react";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import homeReducer from "../../../../../Store/slices/HomeSlice";
import AnnouncementBar from "../AnnouncementBar";

const stateWith = (blocks) => ({
  home: { blocks, status: "succeeded", error: null },
});

const bar = {
  id: 11,
  type: "announcement_bar",
  content: { text: "حمل التطبيق", link: null, bg_color: "#141923", text_color: "#C5A880" },
};

describe("AnnouncementBar", () => {
  beforeEach(() => sessionStorage.clear());

  it("renders the announcement text with configured colors", () => {
    renderWithProviders(<AnnouncementBar />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([bar]),
    });
    const strip = screen.getByText("حمل التطبيق").closest("[data-testid='announcement-bar']");
    expect(strip).toHaveStyle({ backgroundColor: "#141923" });
  });

  it("renders nothing when there is no announcement block", () => {
    const { container } = renderWithProviders(<AnnouncementBar />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([]),
    });
    expect(container.textContent).toBe("");
  });

  it("dismisses for the session", () => {
    const first = renderWithProviders(<AnnouncementBar />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([bar]),
    });
    fireEvent.click(screen.getByRole("button", { name: /dismiss/i }));
    expect(screen.queryByText("حمل التطبيق")).toBeNull();
    first.unmount();

    renderWithProviders(<AnnouncementBar />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([bar]),
    });
    expect(screen.queryByText("حمل التطبيق")).toBeNull();
  });
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `npx vitest run src/components/pages/home/blocks/__tests__/AnnouncementBar.test.jsx`
Expected: FAIL — module not found.

- [ ] **Step 3: Implement**

Create `narzin-main/src/components/pages/home/blocks/AnnouncementBar.jsx`:

```jsx
import React, { useState } from "react";
import { useSelector } from "react-redux";
import { X } from "lucide-react";
import { selectLayoutBlocks } from "../../../../Store/slices/HomeSlice";
import { SmartLink } from "./blockLink";

const AnnouncementBar = () => {
  const block = useSelector(selectLayoutBlocks).find(
    (b) => b.type === "announcement_bar"
  );
  const dismissKey = block ? `home_announcement_dismissed_${block.id}` : null;
  const [dismissed, setDismissed] = useState(() =>
    dismissKey ? sessionStorage.getItem(dismissKey) === "1" : false
  );

  if (!block || dismissed) return null;
  const { text, link, bg_color, text_color } = block.content;

  const dismiss = () => {
    sessionStorage.setItem(dismissKey, "1");
    setDismissed(true);
  };

  return (
    <div
      data-testid="announcement-bar"
      className="w-full text-center text-xs sm:text-sm py-2 px-8 relative"
      style={{ backgroundColor: bg_color || "#141923", color: text_color || "#C5A880" }}
    >
      <SmartLink link={link} className="inline-block hover:underline">
        {text}
      </SmartLink>
      <button
        type="button"
        aria-label="dismiss"
        onClick={dismiss}
        className="absolute end-2 top-1/2 -translate-y-1/2 opacity-70 hover:opacity-100"
      >
        <X className="w-4 h-4" />
      </button>
    </div>
  );
};

export default AnnouncementBar;
```

- [ ] **Step 4: Replace the legacy strip in NavBar**

In `narzin-main/src/components/New/NavBar.jsx`: find the legacy before-nav strip (around line 113 — the JSX guarded by `{beforeNav && ...}` that renders `{beforeNav.text}`). Replace that whole conditional JSX element with `<AnnouncementBar />` **in the same DOM position** (so the fixed-navbar layout math is unchanged), and add `import AnnouncementBar from "../pages/home/blocks/AnnouncementBar";` at the top. Leave the `beforeNav` prop itself in the signature for now (Task 10 removes the plumbing).

- [ ] **Step 5: Run tests + build**

Run: `npx vitest run` then `npm run build`
Expected: all pass.

- [ ] **Step 6: Commit**

```bash
git add narzin-main/src/components/pages/home/blocks narzin-main/src/components/New/NavBar.jsx
git commit -m "feat(web-home): dismissible announcement bar block replacing legacy before-nav strip"
```

---

### Task 6: HeroSlider block

**Files:**
- Create: `narzin-main/src/components/pages/home/blocks/HeroSlider.jsx`
- Modify: `narzin-main/src/components/pages/home/blocks/BlockRenderer.jsx` (register), `narzin-main/src/test/setup.js` (scrollIntoView shim)
- Test: `narzin-main/src/components/pages/home/blocks/__tests__/HeroSlider.test.jsx`

**Interfaces:**
- Consumes: `SmartLink` (Task 3), registry (Task 4).
- Produces: `<HeroSlider content={{slides}} />` — full-bleed CSS scroll-snap carousel: native touch swipe, auto-advance every 4 s (paused while the pointer is over it), clickable dots, per-slide overlay title/subtitle, first image eager / rest lazy. Registered as `hero_slider`.

- [ ] **Step 1: Add the jsdom shim**

Append to `narzin-main/src/test/setup.js`:

```js
// jsdom has no scrollIntoView; hero/rail carousels call it for navigation.
if (!Element.prototype.scrollIntoView) {
  Element.prototype.scrollIntoView = () => {};
}
```

- [ ] **Step 2: Write the failing tests**

Create `narzin-main/src/components/pages/home/blocks/__tests__/HeroSlider.test.jsx`:

```jsx
import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import HeroSlider from "../HeroSlider";

const content = {
  slides: [
    { image: "https://cdn.test/a.jpg", title: "Summer", subtitle: "Sale", link: { type: "category", value: 4 } },
    { image: "https://cdn.test/b.jpg", title: null, subtitle: null, link: null },
  ],
};

describe("HeroSlider", () => {
  it("renders all slides with images and overlay text", () => {
    renderWithProviders(<HeroSlider content={content} />);
    const imgs = screen.getAllByRole("img");
    expect(imgs).toHaveLength(2);
    expect(imgs[0]).toHaveAttribute("src", "https://cdn.test/a.jpg");
    expect(screen.getByText("Summer")).toBeInTheDocument();
    expect(screen.getByText("Sale")).toBeInTheDocument();
  });

  it("renders one dot per slide", () => {
    renderWithProviders(<HeroSlider content={content} />);
    expect(screen.getAllByRole("button", { name: /go to slide/i })).toHaveLength(2);
  });

  it("renders nothing with no slides", () => {
    const { container } = renderWithProviders(<HeroSlider content={{ slides: [] }} />);
    expect(container.textContent).toBe("");
  });
});
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `npx vitest run src/components/pages/home/blocks/__tests__/HeroSlider.test.jsx`
Expected: FAIL — module not found.

- [ ] **Step 4: Implement**

Create `narzin-main/src/components/pages/home/blocks/HeroSlider.jsx`:

```jsx
import React, { useEffect, useRef, useState } from "react";
import { SmartLink } from "./blockLink";

const HeroSlider = ({ content }) => {
  const slides = content?.slides || [];
  const trackRef = useRef(null);
  const pausedRef = useRef(false);
  const [active, setActive] = useState(0);

  const goTo = (index) => {
    const track = trackRef.current;
    if (!track || !track.children[index]) return;
    track.children[index].scrollIntoView({
      behavior: "smooth",
      block: "nearest",
      inline: "start",
    });
    setActive(index);
  };

  useEffect(() => {
    if (slides.length < 2) return undefined;
    const timer = setInterval(() => {
      if (pausedRef.current) return;
      setActive((current) => {
        const next = (current + 1) % slides.length;
        const track = trackRef.current;
        track?.children[next]?.scrollIntoView({
          behavior: "smooth",
          block: "nearest",
          inline: "start",
        });
        return next;
      });
    }, 4000);
    return () => clearInterval(timer);
  }, [slides.length]);

  if (slides.length === 0) return null;

  return (
    <section
      className="relative"
      onPointerEnter={() => (pausedRef.current = true)}
      onPointerLeave={() => (pausedRef.current = false)}
      onTouchStart={() => (pausedRef.current = true)}
    >
      <div
        ref={trackRef}
        className="flex overflow-x-auto snap-x snap-mandatory scroll-smooth"
        style={{ scrollbarWidth: "none" }}
      >
        {slides.map((slide, index) => (
          <SmartLink
            key={index}
            link={slide.link}
            className="relative w-full flex-shrink-0 snap-start"
          >
            <img
              src={slide.image}
              alt={slide.title || ""}
              loading={index === 0 ? "eager" : "lazy"}
              className="w-full h-52 sm:h-72 md:h-96 object-cover"
            />
            {(slide.title || slide.subtitle) && (
              <div className="absolute inset-0 bg-gradient-to-t from-narzin-navy/70 via-transparent to-transparent flex flex-col justify-end items-start p-4 sm:p-8">
                {slide.title && (
                  <h2 className="text-white text-xl sm:text-3xl md:text-4xl font-bold drop-shadow">
                    {slide.title}
                  </h2>
                )}
                {slide.subtitle && (
                  <p className="text-narzin-sand text-sm sm:text-lg mt-1 drop-shadow">
                    {slide.subtitle}
                  </p>
                )}
              </div>
            )}
          </SmartLink>
        ))}
      </div>

      {slides.length > 1 && (
        <div className="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
          {slides.map((_, index) => (
            <button
              key={index}
              type="button"
              aria-label={`go to slide ${index + 1}`}
              onClick={() => goTo(index)}
              className={`h-1.5 rounded-full transition-all ${
                index === active ? "w-5 bg-white" : "w-1.5 bg-white/50"
              }`}
            />
          ))}
        </div>
      )}
    </section>
  );
};

export default HeroSlider;
```

- [ ] **Step 5: Register in BlockRenderer**

In `narzin-main/src/components/pages/home/blocks/BlockRenderer.jsx`, add at the top: `import HeroSlider from "./HeroSlider";` and change the registry declaration to include it:

```jsx
const registry = {
  hero_slider: HeroSlider,
};
```

- [ ] **Step 6: Run tests + build, commit**

Run: `npx vitest run` then `npm run build` — all pass. Then:

```bash
git add narzin-main/src/components/pages/home/blocks narzin-main/src/test/setup.js
git commit -m "feat(web-home): scroll-snap hero slider block"
```

---

### Task 7: CategoryCircles + InfoStrip blocks

**Files:**
- Create: `narzin-main/src/components/pages/home/blocks/CategoryCircles.jsx`, `InfoStrip.jsx`
- Modify: `narzin-main/src/components/pages/home/blocks/BlockRenderer.jsx` (register both)
- Test: `narzin-main/src/components/pages/home/blocks/__tests__/CategoryCircles.test.jsx`, `InfoStrip.test.jsx`

**Interfaces:**
- Consumes: `SmartLink`/`linkTarget` (Task 3), registry (Task 4).
- Produces: `<CategoryCircles content={{categories}} />` (registered `category_grid`) and `<InfoStrip content={{items}} />` (registered `info_strip`). Icon names map: truck→Truck, shield→ShieldCheck, star→Star, returns→RotateCcw, support→Headphones, tag→Tag (lucide-react); unknown icon falls back to Tag.

- [ ] **Step 1: Write the failing tests**

Create `narzin-main/src/components/pages/home/blocks/__tests__/CategoryCircles.test.jsx`:

```jsx
import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import CategoryCircles from "../CategoryCircles";

describe("CategoryCircles", () => {
  it("renders a linked circle per category", () => {
    renderWithProviders(
      <CategoryCircles
        content={{
          categories: [
            { id: 1, name: "Kleider", image: "https://cdn.test/c1.jpg" },
            { id: 2, name: "Schuhe", image: null },
          ],
        }}
      />
    );
    expect(screen.getByRole("link", { name: /Kleider/ })).toHaveAttribute(
      "href",
      "/store?category_id=1"
    );
    expect(screen.getByText("Schuhe")).toBeInTheDocument();
  });

  it("renders nothing when empty", () => {
    const { container } = renderWithProviders(
      <CategoryCircles content={{ categories: [] }} />
    );
    expect(container.textContent).toBe("");
  });
});
```

Create `narzin-main/src/components/pages/home/blocks/__tests__/InfoStrip.test.jsx`:

```jsx
import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import InfoStrip from "../InfoStrip";

describe("InfoStrip", () => {
  it("renders every item's text", () => {
    renderWithProviders(
      <InfoStrip
        content={{
          items: [
            { icon: "truck", text: "Free shipping over €49", link: null },
            { icon: "definitely_new_icon", text: "Easy returns", link: null },
          ],
        }}
      />
    );
    expect(screen.getByText("Free shipping over €49")).toBeInTheDocument();
    expect(screen.getByText("Easy returns")).toBeInTheDocument();
  });
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `npx vitest run src/components/pages/home/blocks/__tests__/CategoryCircles.test.jsx src/components/pages/home/blocks/__tests__/InfoStrip.test.jsx`
Expected: FAIL — modules not found.

- [ ] **Step 3: Implement CategoryCircles**

Create `narzin-main/src/components/pages/home/blocks/CategoryCircles.jsx`:

```jsx
import React from "react";
import { Link } from "react-router-dom";

const CategoryCircles = ({ content }) => {
  const categories = content?.categories || [];
  if (categories.length === 0) return null;

  return (
    <section className="py-4 bg-white">
      <div className="flex gap-4 overflow-x-auto snap-x px-4 sm:justify-center sm:flex-wrap sm:overflow-visible">
        {categories.map((category) => (
          <Link
            key={category.id}
            to={`/store?category_id=${category.id}`}
            className="flex flex-col items-center gap-1.5 snap-start flex-shrink-0 w-20 group"
          >
            <div className="w-16 h-16 sm:w-20 sm:h-20 rounded-full overflow-hidden bg-narzin-bg ring-1 ring-gray-200 group-hover:ring-2 group-hover:ring-narzin-sand transition-all">
              {category.image ? (
                <img
                  src={category.image}
                  alt={category.name}
                  loading="lazy"
                  className="w-full h-full object-cover"
                />
              ) : (
                <div className="w-full h-full flex items-center justify-center text-narzin-navy/40 text-lg font-semibold">
                  {String(category.name || "?").charAt(0)}
                </div>
              )}
            </div>
            <span className="text-[11px] sm:text-xs text-narzin-navy text-center leading-tight max-w-[72px] truncate">
              {category.name}
            </span>
          </Link>
        ))}
      </div>
    </section>
  );
};

export default CategoryCircles;
```

- [ ] **Step 4: Implement InfoStrip**

Create `narzin-main/src/components/pages/home/blocks/InfoStrip.jsx`:

```jsx
import React from "react";
import { Truck, ShieldCheck, Star, RotateCcw, Headphones, Tag } from "lucide-react";
import { SmartLink } from "./blockLink";

const icons = {
  truck: Truck,
  shield: ShieldCheck,
  star: Star,
  returns: RotateCcw,
  support: Headphones,
  tag: Tag,
};

const InfoStrip = ({ content }) => {
  const items = content?.items || [];
  if (items.length === 0) return null;

  return (
    <section className="bg-narzin-sand/10 border-y border-narzin-sand/30">
      <div className="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-2 px-4 py-2.5 max-w-5xl mx-auto">
        {items.map((item, index) => {
          const Icon = icons[item.icon] || Tag;
          return (
            <SmartLink
              key={index}
              link={item.link}
              className="flex items-center gap-2 justify-center text-narzin-navy"
            >
              <Icon className="w-4 h-4 text-narzin-sand flex-shrink-0" />
              <span className="text-[11px] sm:text-xs font-medium truncate">{item.text}</span>
            </SmartLink>
          );
        })}
      </div>
    </section>
  );
};

export default InfoStrip;
```

- [ ] **Step 5: Register both**

In `BlockRenderer.jsx` add imports and registry entries:

```jsx
import CategoryCircles from "./CategoryCircles";
import InfoStrip from "./InfoStrip";

const registry = {
  hero_slider: HeroSlider,
  category_grid: CategoryCircles,
  info_strip: InfoStrip,
};
```

- [ ] **Step 6: Run tests + build, commit**

Run: `npx vitest run` then `npm run build` — all pass. Then:

```bash
git add narzin-main/src/components/pages/home/blocks
git commit -m "feat(web-home): category circles and info strip blocks"
```

---

### Task 8: ProductRail + RailProductCard + PromoTiles blocks

**Files:**
- Create: `narzin-main/src/components/pages/home/blocks/RailProductCard.jsx`, `ProductRail.jsx`, `PromoTiles.jsx`
- Modify: `narzin-main/src/components/pages/home/blocks/BlockRenderer.jsx` (register both)
- Test: `narzin-main/src/components/pages/home/blocks/__tests__/ProductRail.test.jsx`

**Interfaces:**
- Consumes: `SmartLink` (Task 3), registry (Task 4). Rail product card shape from Global Constraints.
- Produces: `<ProductRail content={{title, products}} />` (registered `product_rail`) — Shein-style horizontal snap rail, ~2.4 cards visible on mobile, desktop arrows; `<RailProductCard product />` — compact card linking to `/product/{id}`, dual price `€{min_price}` + `{min_price_iqd} IQD`, locale-aware name; `<PromoTiles content={{tiles}} />` (registered `promo_tiles`).

- [ ] **Step 1: Write the failing tests**

Create `narzin-main/src/components/pages/home/blocks/__tests__/ProductRail.test.jsx`:

```jsx
import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import ProductRail from "../ProductRail";

const content = {
  title: "Super Deals",
  rule: "manual",
  products: [
    {
      id: 21,
      name_arabic: "فستان",
      name_german: "Kleid",
      image: "https://cdn.test/p21.jpg",
      min_price: 49.99,
      min_price_iqd: 72500,
      min_price_variant_id: 210,
    },
    {
      id: 22,
      name_arabic: "حذاء",
      name_german: null,
      image: null,
      min_price: 20,
      min_price_iqd: 29000,
      min_price_variant_id: 220,
    },
  ],
};

describe("ProductRail", () => {
  it("renders title, product cards, dual prices and product links", () => {
    renderWithProviders(<ProductRail content={content} />);
    expect(screen.getByText("Super Deals")).toBeInTheDocument();
    expect(screen.getByText("Kleid")).toBeInTheDocument();
    expect(screen.getByText("€49.99")).toBeInTheDocument();
    expect(screen.getByText("72,500 IQD")).toBeInTheDocument();
    expect(screen.getAllByRole("link")[0]).toHaveAttribute("href", "/product/21");
  });

  it("uses the arabic name and falls back when german is missing", () => {
    renderWithProviders(<ProductRail content={content} />, { language: "ar" });
    expect(screen.getByText("فستان")).toBeInTheDocument();
    expect(screen.getByText("حذاء")).toBeInTheDocument();
  });

  it("renders nothing without products", () => {
    const { container } = renderWithProviders(
      <ProductRail content={{ title: "x", products: [] }} />
    );
    expect(container.textContent).toBe("");
  });
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `npx vitest run src/components/pages/home/blocks/__tests__/ProductRail.test.jsx`
Expected: FAIL — module not found.

- [ ] **Step 3: Implement RailProductCard**

Create `narzin-main/src/components/pages/home/blocks/RailProductCard.jsx`:

```jsx
import React from "react";
import { Link } from "react-router-dom";
import { useTranslation } from "react-i18next";

const formatIqd = (value) =>
  value == null ? null : `${Number(value).toLocaleString("en-US")} IQD`;

const RailProductCard = ({ product }) => {
  const { i18n } = useTranslation();
  const name =
    i18n.language === "ar"
      ? product.name_arabic || product.name_german
      : product.name_german || product.name_arabic;

  return (
    <Link
      to={`/product/${product.id}`}
      className="block w-[41%] sm:w-44 md:w-48 flex-shrink-0 snap-start group"
    >
      <div className="aspect-[3/4] rounded-lg overflow-hidden bg-narzin-bg ring-1 ring-gray-100">
        {product.image ? (
          <img
            src={product.image}
            alt={name || ""}
            loading="lazy"
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center text-gray-300 text-xs">
            —
          </div>
        )}
      </div>
      <p className="mt-1.5 text-xs sm:text-sm text-narzin-navy truncate">{name}</p>
      <p className="text-sm sm:text-base font-semibold text-narzin-navy">
        {product.min_price != null ? `€${product.min_price}` : ""}
      </p>
      {product.min_price_iqd != null && (
        <p className="text-[10px] sm:text-xs text-gray-500">{formatIqd(product.min_price_iqd)}</p>
      )}
    </Link>
  );
};

export default RailProductCard;
```

- [ ] **Step 4: Implement ProductRail**

Create `narzin-main/src/components/pages/home/blocks/ProductRail.jsx`:

```jsx
import React, { useRef } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";
import RailProductCard from "./RailProductCard";

const ProductRail = ({ content }) => {
  const products = content?.products || [];
  const trackRef = useRef(null);
  if (products.length === 0) return null;

  const scrollByCards = (direction) => {
    const track = trackRef.current;
    if (!track) return;
    // Positive scrollBy 'left' respects RTL automatically in modern browsers
    track.scrollBy({ left: direction * track.clientWidth * 0.8, behavior: "smooth" });
  };

  return (
    <section className="py-4 bg-white mt-2">
      <div className="flex items-center justify-between px-4 mb-2.5">
        {content.title && (
          <h2 className="text-base sm:text-lg font-bold text-narzin-navy">{content.title}</h2>
        )}
        <div className="hidden md:flex gap-1">
          <button
            type="button"
            aria-label="scroll backward"
            onClick={() => scrollByCards(-1)}
            className="p-1.5 rounded-full ring-1 ring-gray-200 text-narzin-navy hover:bg-narzin-bg"
          >
            <ChevronLeft className="w-4 h-4 rtl:rotate-180" />
          </button>
          <button
            type="button"
            aria-label="scroll forward"
            onClick={() => scrollByCards(1)}
            className="p-1.5 rounded-full ring-1 ring-gray-200 text-narzin-navy hover:bg-narzin-bg"
          >
            <ChevronRight className="w-4 h-4 rtl:rotate-180" />
          </button>
        </div>
      </div>
      <div
        ref={trackRef}
        className="flex gap-3 overflow-x-auto snap-x px-4 pb-1"
        style={{ scrollbarWidth: "none" }}
      >
        {products.map((product) => (
          <RailProductCard key={product.id} product={product} />
        ))}
      </div>
    </section>
  );
};

export default ProductRail;
```

- [ ] **Step 5: Implement PromoTiles**

Create `narzin-main/src/components/pages/home/blocks/PromoTiles.jsx`:

```jsx
import React from "react";
import { SmartLink } from "./blockLink";

const PromoTiles = ({ content }) => {
  const tiles = content?.tiles || [];
  if (tiles.length === 0) return null;

  const desktopCols =
    tiles.length === 1 ? "sm:grid-cols-1" : tiles.length === 2 ? "sm:grid-cols-2" : "sm:grid-cols-3";
  const mobileCols = tiles.length === 1 ? "grid-cols-1" : "grid-cols-2";

  return (
    <section className={`grid ${mobileCols} ${desktopCols} gap-2 px-4 py-3`}>
      {tiles.map((tile, index) => (
        <SmartLink
          key={index}
          link={tile.link}
          className={`relative rounded-lg overflow-hidden group ${
            tiles.length === 3 && index === 2 ? "col-span-2 sm:col-span-1" : ""
          }`}
        >
          <img
            src={tile.image}
            alt={tile.label || ""}
            loading="lazy"
            className="w-full h-32 sm:h-44 object-cover group-hover:scale-105 transition-transform duration-300"
          />
          {tile.label && (
            <span className="absolute bottom-2 start-2 bg-narzin-navy/80 text-narzin-sand text-xs sm:text-sm px-2 py-1 rounded">
              {tile.label}
            </span>
          )}
        </SmartLink>
      ))}
    </section>
  );
};

export default PromoTiles;
```

- [ ] **Step 6: Register both**

In `BlockRenderer.jsx`:

```jsx
import ProductRail from "./ProductRail";
import PromoTiles from "./PromoTiles";

const registry = {
  hero_slider: HeroSlider,
  category_grid: CategoryCircles,
  info_strip: InfoStrip,
  product_rail: ProductRail,
  promo_tiles: PromoTiles,
};
```

- [ ] **Step 7: Run tests + build, commit**

Run: `npx vitest run` then `npm run build` — all pass. Then:

```bash
git add narzin-main/src/components/pages/home/blocks
git commit -m "feat(web-home): product rail with dual-currency cards and promo tiles"
```

---

### Task 9: CountdownBanner + HomePopup blocks

**Files:**
- Create: `narzin-main/src/components/pages/home/blocks/CountdownBanner.jsx`, `HomePopup.jsx`
- Modify: `narzin-main/src/components/pages/home/blocks/BlockRenderer.jsx` (register countdown), `narzin-main/src/components/Layout.jsx` (mount HomePopup)
- Test: `narzin-main/src/components/pages/home/blocks/__tests__/HomePopup.test.jsx`

**Interfaces:**
- Consumes: `useCountdown`, `shouldShowPopup`/`markPopupSeen`, `SmartLink` (Task 3); `selectLayoutBlocks` (Task 2).
- Produces: `<CountdownBanner content />` (registered `countdown_banner`) — hides itself once expired; `<HomePopup />` — self-contained (selects first popup block), shows after `delay_seconds`, marks seen when shown, bottom-sheet on mobile / centered modal on desktop, close button; at most one popup per visit by construction (single component, first block only).

- [ ] **Step 1: Write the failing tests**

Create `narzin-main/src/components/pages/home/blocks/__tests__/HomePopup.test.jsx`:

```jsx
import { screen, fireEvent, act } from "@testing-library/react";
import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { renderWithProviders } from "../../../../../test/renderWithProviders";
import homeReducer from "../../../../../Store/slices/HomeSlice";
import HomePopup from "../HomePopup";

const popupBlock = {
  id: 31,
  type: "popup",
  content: {
    image: null,
    title: "Get the app",
    text: "Shop faster",
    button_label: "Download",
    link: { type: "url", value: "https://apps.test/narzin" },
    frequency: { mode: "once_per_session", days: 0 },
    delay_seconds: 2,
  },
};

const stateWith = (blocks) => ({
  home: { blocks, status: "succeeded", error: null },
});

describe("HomePopup", () => {
  beforeEach(() => {
    vi.useFakeTimers();
    sessionStorage.clear();
    localStorage.clear();
  });
  afterEach(() => vi.useRealTimers());

  it("appears after the configured delay and can be closed", () => {
    renderWithProviders(<HomePopup />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([popupBlock]),
    });
    expect(screen.queryByText("Get the app")).toBeNull();

    act(() => vi.advanceTimersByTime(2000));
    expect(screen.getByText("Get the app")).toBeInTheDocument();
    expect(screen.getByRole("link", { name: "Download" })).toHaveAttribute(
      "href",
      "https://apps.test/narzin"
    );

    fireEvent.click(screen.getByRole("button", { name: /close/i }));
    expect(screen.queryByText("Get the app")).toBeNull();
  });

  it("does not show again in the same session", () => {
    const first = renderWithProviders(<HomePopup />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([popupBlock]),
    });
    act(() => vi.advanceTimersByTime(2000));
    first.unmount();

    renderWithProviders(<HomePopup />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([popupBlock]),
    });
    act(() => vi.advanceTimersByTime(5000));
    expect(screen.queryByText("Get the app")).toBeNull();
  });

  it("renders nothing when there is no popup block", () => {
    const { container } = renderWithProviders(<HomePopup />, {
      reducers: { home: homeReducer },
      preloadedState: stateWith([]),
    });
    act(() => vi.advanceTimersByTime(5000));
    expect(container.textContent).toBe("");
  });
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `npx vitest run src/components/pages/home/blocks/__tests__/HomePopup.test.jsx`
Expected: FAIL — module not found.

- [ ] **Step 3: Implement CountdownBanner**

Create `narzin-main/src/components/pages/home/blocks/CountdownBanner.jsx`:

```jsx
import React from "react";
import { useCountdown } from "./useCountdown";
import { SmartLink } from "./blockLink";

const pad = (n) => String(n).padStart(2, "0");

const CountdownBanner = ({ content }) => {
  const { days, hours, minutes, seconds, expired } = useCountdown(content?.ends_at);
  if (!content?.ends_at || expired) return null;

  return (
    <SmartLink link={content.link} className="block">
      <section
        className="relative flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-6 px-4 py-4 my-2 overflow-hidden"
        style={{
          backgroundColor: content.bg_color || "#141923",
          color: content.text_color || "#D4AF37",
        }}
      >
        {content.image && (
          <img
            src={content.image}
            alt=""
            loading="lazy"
            className="absolute inset-0 w-full h-full object-cover opacity-25"
          />
        )}
        <p className="relative font-semibold text-sm sm:text-lg">{content.text}</p>
        <p
          className="relative font-mono text-lg sm:text-2xl font-bold tracking-wider"
          style={{ fontVariantNumeric: "tabular-nums" }}
          dir="ltr"
        >
          {pad(days)}:{pad(hours)}:{pad(minutes)}:{pad(seconds)}
        </p>
      </section>
    </SmartLink>
  );
};

export default CountdownBanner;
```

- [ ] **Step 4: Implement HomePopup**

Create `narzin-main/src/components/pages/home/blocks/HomePopup.jsx`:

```jsx
import React, { useEffect, useState } from "react";
import { useSelector } from "react-redux";
import { X } from "lucide-react";
import { selectLayoutBlocks } from "../../../../Store/slices/HomeSlice";
import { shouldShowPopup, markPopupSeen } from "./popupFrequency";
import { SmartLink } from "./blockLink";

const HomePopup = () => {
  const block = useSelector(selectLayoutBlocks).find((b) => b.type === "popup");
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    if (!block) return undefined;
    const content = { ...block.content, id: block.id };
    if (!shouldShowPopup(content)) return undefined;
    const timer = setTimeout(() => {
      markPopupSeen(content);
      setVisible(true);
    }, (Number(block.content.delay_seconds) || 0) * 1000);
    return () => clearTimeout(timer);
  }, [block]);

  if (!block || !visible) return null;
  const { image, title, text, button_label, link } = block.content;

  return (
    <div className="fixed inset-0 z-[60] flex items-end sm:items-center justify-center">
      <button
        type="button"
        aria-label="close overlay"
        className="absolute inset-0 bg-narzin-navy/50"
        onClick={() => setVisible(false)}
      />
      <div className="relative bg-white w-full sm:w-96 rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden">
        <button
          type="button"
          aria-label="close"
          onClick={() => setVisible(false)}
          className="absolute top-2 end-2 z-10 p-1.5 rounded-full bg-white/80 text-narzin-navy hover:bg-white"
        >
          <X className="w-4 h-4" />
        </button>
        {image && <img src={image} alt="" className="w-full h-44 sm:h-52 object-cover" />}
        <div className="p-5 text-center">
          <h3 className="text-lg font-bold text-narzin-navy">{title}</h3>
          {text && <p className="mt-1.5 text-sm text-gray-600">{text}</p>}
          {button_label && (
            <SmartLink
              link={link}
              className="inline-block mt-4 px-6 py-2.5 rounded-full bg-narzin-navy text-white text-sm font-semibold hover:bg-narzin-navy/90"
              onClick={() => setVisible(false)}
            >
              {button_label}
            </SmartLink>
          )}
        </div>
      </div>
    </div>
  );
};

export default HomePopup;
```

- [ ] **Step 5: Register countdown + mount popup**

In `BlockRenderer.jsx`:

```jsx
import CountdownBanner from "./CountdownBanner";

const registry = {
  hero_slider: HeroSlider,
  category_grid: CategoryCircles,
  info_strip: InfoStrip,
  product_rail: ProductRail,
  promo_tiles: PromoTiles,
  countdown_banner: CountdownBanner,
};
```

In `narzin-main/src/components/Layout.jsx`: add `import HomePopup from "./pages/home/blocks/HomePopup";` and render `<HomePopup />` as a sibling immediately after the NavBar element (it is position:fixed, so placement only needs to be inside Layout).

- [ ] **Step 6: Run tests + build, commit**

Run: `npx vitest run` then `npm run build` — all pass. Then:

```bash
git add narzin-main/src/components/pages/home/blocks narzin-main/src/components/Layout.jsx
git commit -m "feat(web-home): countdown banner and frequency-capped app popup"
```

---

### Task 10: Legacy cleanup + final verification

**Files:**
- Delete: `narzin-main/src/components/pages/home/Banners.jsx`, `narzin-main/src/Store/slices/BeforeNavSlice.js`
- Modify: `narzin-main/src/App.jsx`, `narzin-main/src/components/Layout.jsx`, `narzin-main/src/components/New/NavBar.jsx`, `narzin-main/src/Store/store.js`

- [ ] **Step 1: Remove beforeNav plumbing**

1. `src/App.jsx`: remove the `fetchBeforeNav` import + dispatch, the `state.beforeNav` selector block, its failure-toast entry, and stop passing `beforeNav` to `<Layout>`.
2. `src/components/Layout.jsx`: remove the `beforeNav` prop and stop passing it to `<NavBar>`.
3. `src/components/New/NavBar.jsx`: remove the now-unused `beforeNav` prop from the signature.
4. `src/Store/store.js`: remove the `beforeNav` reducer entry and its import.
5. Delete `src/Store/slices/BeforeNavSlice.js` and `src/components/pages/home/Banners.jsx` (`git rm`).

- [ ] **Step 2: Verify nothing references the deleted modules**

Run: `grep -rn "BeforeNav\|beforeNav" narzin-main/src --include="*.jsx" --include="*.js" | grep -v node_modules` (Git Bash) — expected: no results (or only comments you then delete). Same for `pages/home/Banners`.

- [ ] **Step 3: Full gates**

Run: `npx vitest run` (all tests pass) and `npm run build` (succeeds).

- [ ] **Step 4: Manual smoke checklist (document, human executes later)**

For the human after deploy: with the Laravel API running and blocks composed, `npm run dev` and check 360 px / 768 px / 1440 px widths in both `ar` (RTL) and German — announcement dismiss, hero swipe + dots, rail swipe + desktop arrows, countdown ticking, popup delay + once-per-session, category circle navigation, API-down fallback (stop the API, reload — old-style homepage appears).

- [ ] **Step 5: Commit**

```bash
git add -A narzin-main/src
git commit -m "feat(web-home): retire hardcoded hero and legacy before-nav slice"
```

---

## Out of scope for this plan

- Phase 3 (Flutter renderer) and Phase 4 (backend legacy retirement) — separate plans.
- Restyling `ProductCard`/`Shop` and other non-home pages to the Narzin palette (worthwhile, but not homepage-blocks work).
- The `preview=1` overlay ribbon for scheduled blocks (admin preview works by seeing upcoming blocks; a visual ribbon is polish for later).



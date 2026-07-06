# SHEIN-style Top Bar — Design

**Date:** 2026-07-06
**Status:** Approved (pending spec review)

## Goal

Rework the right-side actions of the storefront navigation into a compact,
SHEIN-style **icon cluster** — Account, Support (WhatsApp), Cart, Globe
(language) — each opening a small dropdown/popup. Add the supporting pieces:
an admin-configurable WhatsApp number and a lightweight "Recently Viewed" page.

Scope decisions locked during brainstorming:

- **Globe = language only.** Currency stays the backend's single fixed rate; the
  globe shows the selling currency as **read-only** text (`EUR (€)` — the
  storefront's primary display currency; IQD is only a secondary conversion line).
- **Only show account items that exist.** Drop *My Message*, *My Coupons*,
  *My Points*, and *More Services* (no features behind them yet).
- **WhatsApp number set via a new admin Settings page** backed by a reusable
  key-value settings store, exposed to the storefront through a public API.
- **Recently Viewed gets a small real page**, fed by the existing
  `/api/v1/home/for-you` "Recently Viewed" rail (no new backend endpoint).
- **Support icon:** popup showing the number + a "Chat on WhatsApp" button that
  opens `https://wa.me/<number>`.

## Current state (for reference)

- Active navbar: `narzin-main/src/components/New/NavBar.jsx` (~538 lines) — logo,
  category mega-menus, search, an inline flag language toggle, auth links, and a
  cart icon with count. Rendered by `src/components/Layout.jsx`.
- Account area: `src/pages/MyAccountLayout.jsx` uses internal `activeTab` state
  (no routing). Existing tabs: `my-account`, `orders`, `addresses`, `wishlist`,
  `wallet`, `about`, `vendor`.
- Language: i18next with `ar` and `du` (German). `changeLanguage("ar"|"du")`.
- Currency: `Modules/Admin/PriceExchange` — a single `price_rate` used to convert
  EUR base prices to IQD. No multi-currency selection.
- No general site-settings store exists. Admin panel is Blade; sidebar links live
  in `resources/views/components/admin/sidebar.blade.php`; the price-exchange
  controller/views are the pattern to mirror.
- Telemetry writes `user_product_views` (POST `/api/v1/telemetry/view` only).
  `GET /api/v1/home/for-you` already returns a "Recently Viewed" product rail
  plus a "Recommended" rail, keyed by `session_id` (guests) or the auth user.
- Frontend API base: `src/api/axios.js` (`api` axios instance). Redux store in
  `src/Store/store.js`. Existing `ForYouSlice` already calls `/v1/home/for-you`.

## Architecture

### A. Backend — reusable site settings (Admin module)

1. **Table** `site_settings`: `id`, `key` (unique), `value` (nullable text),
   `is_public` (bool, default false), `group` (nullable string), timestamps.
   Migration in `Modules/Admin/database/migrations`.
2. **Model** `Modules\Admin\Models\SiteSetting` with a static helper
   `SiteSetting::get(string $key, $default = null)` and `set($key, $value)`.
   `get()` is cached (single `remember` over all rows, flushed on save) to avoid
   per-request queries.
3. **Admin UI** `SiteSettingController` (web) with `edit()` (show form) and
   `update()` (validate + save). One Blade view `admin::settings.edit` mirroring
   `price-exchange/create.blade.php`. Fields for v1:
   - `whatsapp_number` — validated, digits/`+` only (E.164-ish), stored public.
   - `support_hours` — optional free-text label (e.g. "Sun–Thu, 9–18"), public.
   Route group under the existing admin prefix; add a **"Settings"** link to
   `resources/views/components/admin/sidebar.blade.php`.
4. **Public API** `GET /api/v1/settings/public` (Admin module `routes/api.php`)
   returning `{ data: { whatsapp_number, support_hours } }` — only `is_public`
   keys. No auth. Response is cached alongside the model cache.

### B. Frontend — settings slice

- New `src/Store/slices/SettingsSlice.js` with a `fetchPublicSettings` thunk
  hitting `/v1/settings/public`, storing `{ whatsapp_number, support_hours,
  status }`. Registered in `src/Store/store.js`.
- Dispatched once on app mount (in `App.jsx` or `Layout.jsx`, matching how other
  bootstrap fetches are done). Selectors: `selectWhatsappNumber`, etc.

### C. Frontend — navbar icon cluster

Refactor the desktop right cluster of `NavBar.jsx` into four icon buttons and
**extract the three dropdowns** into their own components under
`src/components/New/navbar/`:

- `AccountMenu.jsx` — `User` icon → dropdown:
  - Logged out: **Sign in / Register** button block (→ `/signin`, `/signup`),
    then guest-safe links (Recently Viewed).
  - Logged in: greeting + links **My Orders** (`/my-account?tab=orders`),
    **Recently Viewed** (`/recently-viewed`), **Wishlist**
    (`/my-account?tab=wishlist`), **My Wallet** (`/my-account?tab=wallet`),
    **Addresses** (`/my-account?tab=addresses`), **My Account**
    (`/my-account`), and **Logout**.
- `SupportMenu.jsx` — `Headphones` icon → popup with the WhatsApp number,
  optional support-hours line, and a **"Chat on WhatsApp"** button linking to
  `https://wa.me/<digits>`. **Hidden entirely when no number is configured.**
- `LanguageMenu.jsx` — `Globe` icon → dropdown: العربية / Deutsch (active one
  checked, calls `changeLanguage`), plus a read-only "Currency: EUR (€)" row.
- Cart stays as-is (own `ShoppingCart` link with count badge).

Use `@headlessui/react` `Menu`/`Popover` (already a dependency) for
accessibility, keyboard nav, and click-outside — removing the bespoke
outside-click/escape effects for these menus.

A small pure helper `src/components/New/navbar/waLink.js`:
`buildWhatsappUrl(number)` → strips all non-digits and returns
`https://wa.me/<digits>` (or `null` if empty). Unit-tested.

### D. Deep-linkable account tabs

`MyAccountLayout.jsx`: read `?tab=` from the URL (react-router
`useSearchParams`) to initialize `activeTab`, falling back to `my-account` for
unknown/absent values. Keep internal tab switching as-is (optionally reflect the
active tab back into the query string; not required for v1).

### E. Recently Viewed page

- New `src/pages/RecentlyViewed.jsx`, route `/recently-viewed` in `App.jsx`
  (inside the same layout as other storefront pages).
- Fetches `/v1/home/for-you` (reuse the pattern/thunk from `ForYouSlice`),
  finds the "Recently Viewed" rail in the response, and renders its products
  using the existing product card component (e.g. `RailProductCard`).
- Empty state: friendly "You haven't viewed anything yet" message with a link to
  the store. Loading skeleton consistent with existing rails.

### F. Mobile

Update the existing mobile menu in `NavBar.jsx` to mirror the new structure:
account links (incl. My Orders, Recently Viewed), a WhatsApp support row (same
`buildWhatsappUrl`, hidden if unset), and the already-present language section.
No new mobile-only components required.

### G. i18n

Add keys under a `topbar`/`account` namespace for all new labels (Sign in /
Register, My Orders, Recently Viewed, Wishlist, My Wallet, Addresses, My
Account, Logout, Support / Chat on WhatsApp, Currency) in both translation
files: `public/locales/ar/translation.json` and
`public/locales/du/translation.json` (the German folder is `du`, not `de`).
Follow the existing translation file layout.

## Data flow

1. App mounts → `fetchPublicSettings` → settings slice holds `whatsapp_number`.
2. `NavBar` renders `SupportMenu` only if `whatsapp_number` is present;
   `buildWhatsappUrl` produces the `wa.me` link.
3. `AccountMenu` links deep-link into `MyAccountLayout` via `?tab=`.
4. `RecentlyViewed` page reads the existing `/v1/home/for-you` rail.
5. Admin edits the number on the Settings page → cache flush → next storefront
   fetch reflects it.

## Error handling

- Public settings fetch failure → slice `status: 'failed'`, `whatsapp_number`
  stays null → Support icon simply hidden. No blocking of the navbar.
- `for-you` fetch failure on Recently Viewed → error/empty state with a store
  link.
- Admin form validation errors → re-render with Laravel validation messages
  (standard Blade pattern).

## Testing

- **Vitest (frontend):**
  - `waLink.js` — digits stripped, `+`/spaces/dashes handled, empty → `null`.
  - `SettingsSlice` — pending/fulfilled/rejected reducers set state correctly.
- **Manual:** icon menus open/close, keyboard + click-outside, RTL layout in
  Arabic, deep-linked account tabs, WhatsApp opens correct chat, Recently
  Viewed populated vs. empty, Support icon hidden when number unset, admin
  Settings save round-trip.

## Out of scope (explicit)

- Multi-currency selection / re-pricing.
- Messages, Coupons, Points features.
- "More Services" hub.
- Reflecting the active account tab back into the URL (nice-to-have only).

## Files touched (anticipated)

**Backend (`narzinapp-main`)**
- `Modules/Admin/database/migrations/*_create_site_settings_table.php` (new)
- `Modules/Admin/app/Models/SiteSetting.php` (new)
- `Modules/Admin/app/Http/Controllers/SiteSettingController.php` (new)
- `Modules/Admin/resources/views/settings/edit.blade.php` (new)
- `Modules/Admin/routes/web.php`, `Modules/Admin/routes/api.php` (edit)
- `resources/views/components/admin/sidebar.blade.php` (edit — nav link)

**Frontend (`narzin-main`)**
- `src/Store/slices/SettingsSlice.js` (new), `src/Store/store.js` (edit)
- `src/components/New/navbar/{AccountMenu,SupportMenu,LanguageMenu}.jsx` (new)
- `src/components/New/navbar/waLink.js` (+ test) (new)
- `src/components/New/NavBar.jsx` (edit — cluster + mobile)
- `src/pages/RecentlyViewed.jsx` (new), `src/App.jsx` (edit — route + bootstrap)
- `src/pages/MyAccountLayout.jsx` (edit — `?tab=`)
- i18n translation files `public/locales/ar/translation.json` and
  `public/locales/du/translation.json` (edit)

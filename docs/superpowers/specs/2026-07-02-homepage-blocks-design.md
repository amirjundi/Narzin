# Homepage Content Blocks — Design Spec

**Date:** 2026-07-02
**Status:** Approved by Amer (brainstorming session)
**Goal:** Make the Narzin storefront homepage feel like Shein — dense, mobile-first merchandising — and give admins full control to compose, schedule, and push content (announcements, popups, sales, product rails) to the homepage on **both web and the Flutter app** without developer involvement.

---

## 1. Background & current state

- **Backend** (`narzinapp-main`): modular Laravel. Existing `banners` table (image, title, description, `is_mobile`) with admin CRUD + public APIs, and `before_nav` announcement bar (text, start/end date) shown in the web navbar.
- **Web** (`narzin-main`): React + Vite + Tailwind + Redux. The homepage hero in `src/components/pages/home/Banners.jsx` is **hardcoded demo content**; the three product sections all render the same product list; the features strip is hardcoded.
- **Flutter app** (`Narzin-app/user/narzin`): consumes the banners API via `BannersCubit`.
- **i18n:** web supports ar (default, RTL) + de; app has ar/de/en ARB files.

**Decision context:**
- Platform scope: **web + Flutter together** (phased delivery, each phase shippable).
- Flexibility level: **composable ordered block list** (server-driven UI), not fixed slots, not a full page builder.
- Block types v1: all four groups (core merchandising, announcement + popup, countdown + info strips, promo tiles).
- Text content: **multilingual per field** (ar/de/en) with fallback.
- Brand: copy Shein's **layout patterns and density**, not its look. Narzin identity per `DESIGN.md`: navy `#141923` (primaryDark), sand `#C5A880` (accentSand), gold `#D4AF37`, Tajawal typography. Header uses the Concept 3 wordmark (`Logos/NARZIN Logo Concept 3.png`), exported as optimized SVG/PNG in a dark-on-light variant plus a white-on-navy variant for dark strips/footer. No Shein logos, names, fonts, colors, or imagery.
- Web must be fully responsive/adaptive, designed mobile-first, UX-first.

## 2. Architecture overview

**Server-driven homepage.** One new Laravel module `HomeContent` owns a `home_blocks` table. Admins compose the homepage as an ordered list of typed blocks in a drag-and-drop Blade admin page. One public endpoint returns the resolved, localized, ordered block list. React and Flutter render it through a `type → component/widget` registry and silently skip unknown types (forward compatibility — new block types never crash old app versions).

```
Admin (Blade builder) ──writes──> home_blocks ──resolves──> GET /api/v1/home
                                                            │
                                            ┌───────────────┴───────────────┐
                                        React web                      Flutter app
                                     (BlockRenderer)               (block widget list)
```

## 3. Data model

New module: `Modules/HomeContent` (follows the existing nwidart module pattern).

### `home_blocks` table

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `type` | string | one of the 8 types below |
| `name` | string | internal admin label, e.g. "Summer Sale Hero" |
| `sort_order` | int | homepage position; reorder endpoint rewrites |
| `is_active` | bool | master toggle |
| `platform` | enum `web` \| `app` \| `both` | |
| `starts_at` / `ends_at` | nullable datetime | schedule window; null = always |
| `content` | JSON | type-specific payload (below) |
| timestamps | | |

### Conventions inside `content`

- **Translatable text:** `{"ar": "…", "de": "…", "en": "…"}`. Resolution picks requested locale, falls back to the first non-empty value — a half-translated block never renders blank. Note: the web frontend's locale folder uses the code `du` for German; the API normalizes `du` → `de` (and any unknown code → fallback chain) so clients don't need renaming first.
- **Links:** structured object `{"type": "category" | "product" | "url" | "none", "value": <id or url>}` so Flutter can navigate natively.
- **Images:** stored as storage paths; API returns absolute URLs. Where aspect ratios differ per platform (hero), separate `image_web` and `image_app` uploads.

### The 8 block types and their payloads

1. **`announcement_bar`** — `text` (i18n), `link`, `bg_color`, `text_color`. Replaces `before_nav`.
2. **`popup`** — `image`, `title` (i18n), `text` (i18n), `button_label` (i18n), `link`, `frequency` (`once_per_session` | `once_per_days:N`), `delay_seconds`. Platform defaults to `web` (an app-download popup inside the app is pointless), but admins may target `app` for other campaigns.
3. **`hero_slider`** — `slides[]`: `image_web`, `image_app`, `title` (i18n), `subtitle` (i18n), `link`, optional per-slide `starts_at`/`ends_at`.
4. **`category_grid`** — `category_ids[]` (ordered).
5. **`product_rail`** — `title` (i18n), `rule` (`newest` = latest created active products | `best_sellers` = most units sold from order items, all-time | `category` with `category_id` | `manual` with `product_ids[]`), `limit` (default 12, max 24). *Amendment (2026-07-02, planning):* the originally specced `biggest_discount` rule is deferred — the catalog has no product-level discount concept (promotions/coupons are cart-level), so there is nothing to rank by. Admins cover "Super Deals" with `manual` rails until catalog-level discounts exist, at which point the rule slots in as one more `case` in the rail resolver.
6. **`countdown_banner`** — `text` (i18n), `link`, `ends_at_display` (countdown target), `bg_color`/`text_color` or `image`. Auto-hidden once expired.
7. **`info_strip`** — `items[]` (2–4): `icon` (named icon from a fixed set), `text` (i18n), optional `link`. Replaces the hardcoded features section.
8. **`promo_tiles`** — `tiles[]` (1–3): `image`, `link`, optional `label` (i18n).

### Migration of existing data

One-time migration/seeder: each `before_nav` row → an `announcement_bar` block (keeping dates); all `banners` rows → one `hero_slider` block (web images from `is_mobile=0`, app from `is_mobile=1`). Legacy tables retained until Phase 4 cleanup.

## 4. API

### Public

`GET /api/v1/home?platform=web|app&locale=ar|de|en`

Resolution steps:
1. Query blocks: `is_active`, platform matches (`both` or requested), `now` within schedule window; order by `sort_order`.
2. Per block: collapse i18n text to the locale (with fallback); expand image paths to URLs; **resolve `product_rail` rules server-side** into product arrays (id, name, image, EUR/IQD prices, discount %) reusing existing product query scopes. Expired countdown blocks and rails resolving to zero products are dropped.
3. Response: `{"data": [ {"id", "type", "content"}, … ]}`.

**Caching:** resolved response cached ~5 min per `platform+locale`; cache flushed on any admin save/reorder/toggle so admin changes are instant. Time-window transitions are at most 5 minutes stale — acceptable.

**Preview:** `?preview=1` (admin session required) additionally includes scheduled-but-not-started blocks, flagged `"preview_upcoming": true` so the web renders them with an overlay ribbon. Deactivated blocks stay hidden.

**Compatibility:** existing `/v1/banners` (mobile/web) and `/v1/before-nav/current` endpoints keep working during transition, re-reading from `home_blocks` (hero slides → banner shape; announcement bar → before-nav shape). Removed in Phase 4.

### Admin (Blade panel, `admin.auth` middleware)

- `GET /home-blocks` (builder page), `GET/POST/PUT/DELETE` CRUD per block
- `POST /home-blocks/reorder` — array of ids in new order
- `POST /home-blocks/{id}/toggle` — AJAX active flip

## 5. Admin "Homepage Builder" UX

Replaces the current *Banners* and *Before Nav* admin pages. Same Blade + Tailwind stack; SortableJS for drag-and-drop.

- **Block list page:** one row per block — drag handle, name, type badge, platform badge, AJAX on/off toggle, schedule status ("ends Jul 15" / red "expired"), Edit/Delete. Drag saves order immediately. "+ Add block" opens a type picker with a small illustration per type.
- **Edit forms:** shared fields (name, platform, schedule, active) + type-specific section.
  - **Language tabs** (العربية / Deutsch / English) with per-tab completeness dots instead of a wall of fields.
  - **Link picker:** dropdown None/Category/Product/URL revealing a searchable select — admins never hand-type internal URLs.
  - **Product rule picker:** radio list; "manual" reveals product search-and-add; "category" reveals category select. Live preview area renders the first ~6 currently-matching products before save.
  - **Image validation:** dimension checks with warnings when far from recommended aspect ratio (hero web ≈3:1, app ≈2:1).
- **Server-side validation per type:** at least one language filled for required texts, valid date ranges (end after start), slide/tile/item count limits, valid link targets.
- **Preview button** opens the storefront with `?preview=1`.

## 6. Web homepage (React)

**Renderer:** new `homeSlice` fetches `/v1/home?platform=web&locale=<current>`. `Home.jsx` becomes `BlockRenderer` walking the array with a `type → component` map; unknown types render nothing. One component per type under `src/components/pages/home/blocks/`. Hardcoded hero (`Banners.jsx` content), duplicate product sections, and features grid are deleted.

**Visual language — Shein's density, Narzin's skin (mobile-first):**
- Announcement bar: slim navy strip, sand-gold text, dismissible ✕, above navbar.
- Hero: full-bleed swipeable slider (touch swipe mobile / arrows desktop), overlaid text, auto-rotate pausing on interaction.
- Category circles: horizontal scroll row on mobile (2 rows if many), centered grid desktop; Tajawal labels, gold hover ring.
- Product rails: horizontal snap-scroll on mobile showing ~2.4 cards (affordance to scroll), 4–6 across desktop with arrows; navy discount badges; dual EUR/IQD prices. Urgency via the countdown block, not red-everywhere.
- Countdown banner: full-width navy band, live `DD:HH:MM:SS` gold ticker.
- Promo tiles / info strip: responsive grids; side-by-side desktop, stacked/2-up mobile.
- Popup: bottom-sheet on mobile (thumb-reachable close), centered modal desktop; shows after configured delay; frequency capping via localStorage; max one popup per visit.

**UX guardrails:** per-block skeleton placeholders (no layout jump); lazy-loaded below-fold images; full RTL mirroring for Arabic (scroll direction, arrows, alignment); if the home API fails entirely, fall back to a plain categories + newest-products view — never a blank page. Lighthouse mobile performance is a merge criterion.

## 7. Flutter app

- `HomeCubit` + repository call `/v1/home?platform=app&locale=<device>`; block models parse the array; unknown types skipped at parse time.
- Home screen becomes a `ListView` of block widgets via a type→widget map, replacing `BannersCubit` and hardcoded sections.
- Skeleton loaders, `Directionality`-driven RTL, cached network images, popup frequency capping via `shared_preferences`.

## 8. Error handling

- **API:** a block with malformed `content` is skipped and logged — never a 500 for the homepage.
- **Clients:** defensive per-block rendering — missing image hides the slide; empty rail hides the whole rail.
- **Admin:** validation per §5 prevents most bad states at write time.

## 9. Testing

- **Backend (PHPUnit feature tests):** schedule-window filtering, platform filtering, locale fallback, each product rule, empty-rail dropping, cache invalidation on save, malformed-content skipping, legacy `/banners` + `/before-nav` compatibility, admin CRUD/reorder/toggle authorization.
- **Web:** block renderer tests (unknown type skipped, RTL) if a JS runner is present; otherwise a manual checklist at 360 / 768 / 1440 px in ar + de.
- **Flutter:** model parsing tests (incl. unknown type) and cubit tests.

## 10. Rollout phases (each shippable alone)

1. **Backend + admin builder.** Deploy; legacy endpoints still serve existing clients — nothing customer-visible changes. Admins start composing. Migrate legacy banner/before-nav data.
2. **Web homepage redesign.** Block-driven Shein-style homepage goes live.
3. **Flutter release.** App renders blocks; store submission.
4. **Cleanup.** Retire legacy endpoints, tables, and old admin pages once app adoption allows.

## 11. Out of scope (v1)

- Per-user personalization ("For You" ranking) — the feed rail uses rules, not ML.
- Arbitrary landing pages / full page builder; nested layouts; custom HTML blocks.
- Draft/versioned homepage states beyond active/schedule + preview of upcoming blocks.
- Push notifications for announcements (separate feature).

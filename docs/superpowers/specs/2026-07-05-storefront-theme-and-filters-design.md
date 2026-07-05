# Storefront Theme Tokens + Shein-style Filters — Design

Date: 2026-07-05
Surface: `narzin-main` (React storefront), primarily `src/pages/Shop.jsx`

## Goals

1. Introduce a **global design-token theme layer** so the whole storefront's look is
   driven from one place and can be re-skinned later without touching components.
2. Redesign the **Shop filters** in a modern **Shein-style** UX on top of that theme.
3. Apply a **"Liquid Glass"** aesthetic (frosted translucent panels, depth, gold-on-navy).
4. Fix the **broken/missing translations** surfacing as raw keys and stray English.

Approved scope: **theme foundation + filters only** this pass. Rest of the site
(nav, product cards, product page) adopts the theme in a later pass.

## 1. Theme token architecture

- New `src/theme/tokens.css` defines CSS custom properties on `:root`, grouped:
  - **Color:** `--nz-bg`, `--nz-surface`, `--nz-text`, `--nz-muted`, `--nz-primary`
    (gold `#C5A880`), `--nz-primary-strong` (`#D4AF37`), `--nz-ink` (navy `#141923`),
    `--nz-border`, `--nz-danger`.
  - **Glass material:** `--nz-glass-bg` (e.g. `rgba(255,255,255,0.6)`),
    `--nz-glass-border`, `--nz-glass-blur` (e.g. `16px`), `--nz-glass-shadow`.
  - **Shape/space:** `--nz-radius` (14px), `--nz-radius-lg` (22px).
- `tailwind.config.js` `theme.extend` maps these vars to Tailwind names
  (`colors.nz.primary` → `var(--nz-primary)`, etc.) so components use utility classes
  (`bg-nz-surface`, `text-nz-primary`) that resolve to the tokens.
- A reusable `.nz-glass` utility (frosted bg + blur + border highlight + shadow),
  defined once, with a `@supports not (backdrop-filter: blur())` **solid fallback**.
- Swapping themes later = editing token values (or adding a `[data-theme="x"]` block).

## 2. Filter UX (Shein-style)

Reusable components under `src/components/shop/`:

- **`CategoryAccordion`** — parent categories collapsed by default; tap expands to show
  indented subcategories. Selecting sets `category_id`. (Fixes today's flat 61-item list.)
- **`ColorSwatches`** — round swatches, selected ring, accessible names via tooltip/aria.
- **`SizeChips`** — pill chips, multi-select, selected = filled gold.
- **`PriceRange`** — dual-handle slider synced with numeric from/to inputs; localized labels.
- **`ActiveChips`** — removable chips row for current filters + "clear all".
- **`FilterDrawer`** — layout shell:
  - **Mobile:** bottom "Sort / Filter" bar → full-height **bottom sheet** (glass), sections
    stacked, **sticky footer** `[ مسح (n) ] [ تطبيق ]`. Horizontal category pills at top.
  - **Desktop:** left **glass sidebar** with the same sections as accordions + sticky
    apply/clear.
- **Sort** becomes a small glass menu/segmented control (reuses existing sort options).

State/URL logic in `Shop.jsx` is **preserved** (query-param driven, `applyFilters`,
pagination). Only presentation is replaced. Note: fix the existing bug where
`handleCategoryChange` sends `{category}` but the reader expects `category_id` in the URL —
align the param name so category filtering actually round-trips.

## 3. Liquid Glass material

- Panels: `.nz-glass` (translucent + `backdrop-blur`), `rounded-2xl`, soft layered shadow,
  1px specular top-border highlight.
- Accents: gold primary on navy ink; product grid stays clean/light for legibility.
- Readability guardrails: text always on a surface with sufficient opacity/contrast;
  blur used on chrome (filter panels, bars), not behind long body text.

## 4. i18n fixes

- Add missing `shop.*` keys to `public/locales/ar/translation.json` and `.../du/...`:
  `active_filters, no_products, category, color, size, min, max, clear_filters, from, to,
  apply, filters, categories, colors, sizes, price, products, sort, filter, loading,
  previous, next, showing, of, results`.
- Replace the `t("key") || "fallback"` anti-pattern (i18next returns the key when missing,
  so the fallback never fires) with real keys + i18next `fallbackLng`/defaults.
- Remove hardcoded English `From ${min}` / `To ${max}` placeholders → localized.

## RTL / a11y / performance

- All new components RTL-correct (logical `start/end`, no hardcoded left/right).
- Swatches/chips keyboard-focusable with `aria-pressed`; sheet traps focus.
- `backdrop-blur` limited to small chrome surfaces; solid fallback via `@supports`.

## Out of scope (this pass)

Nav bar, product cards, product page, checkout restyle; dark mode; backend filter API changes.

## Acceptance criteria

- Categories show as parent → expandable subcategories (no flat wall).
- Mobile filter opens as a bottom sheet with sticky Apply/Clear; desktop glass sidebar.
- No raw `shop.*` keys or stray English anywhere in the shop UI (ar + du).
- Look uses gold-on-navy glass tokens, all sourced from `tokens.css`.
- Category/color/size/price filtering still round-trips through the URL and paginates.
- Builds clean (`npm run build`) and renders on the live storefront.

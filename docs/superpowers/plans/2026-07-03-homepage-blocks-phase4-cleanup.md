# Homepage Content Blocks — Phase 4 (Cleanup + Review Backlog) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove the now-unreachable legacy admin code, apply the accumulated final-review polish backlog across all three codebases, and document the post-adoption retirement of the legacy public endpoints.

**Architecture:** Pure cleanup/polish — no new features. IMPORTANT SCOPE DECISION: the legacy PUBLIC endpoints (`/api/v1/banners/*`, `/api/v1/before-nav/current`), the `banners`/`before_nav` tables, and the app's BannersCubit fallback are intentionally KEPT — installed app builds depend on them until the new app version reaches users. Their retirement is documented as a future step, not performed.

**Tech Stack:** as per phases 1–3 (Laravel/PHPUnit, React/vitest, Flutter).

## Global Constraints

- Repo root `C:\xampp\htdocs\Narzin`. Per-task working dirs as noted.
- Gates: backend `php artisan test` full suite green; web `npx vitest run` + `npm run build` green; app `flutter analyze` no new issues in touched files + `flutter test` unchanged totals (11 pass + 1 pre-existing template failure).
- Commits `chore(...)`/`fix(...)` style, trailer line `Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>`.
- DO NOT touch: `Modules/Banners` (serves legacy endpoints), `banners`/`before_nav` migrations/tables, BannersCubit, the app's legacy home body.

---

### Task 1: Backend — delete unreachable legacy admin code + scaffold noise

Working dir: `C:\xampp\htdocs\Narzin\narzinapp-main`.

**Files:**
- Delete: `Modules/Admin/app/Http/Controllers/BannerController.php`, `Modules/Admin/app/Http/Controllers/BeforeNavController.php`, `Modules/Admin/resources/views/banners/` (whole dir), `Modules/Admin/resources/views/before-nav/` (whole dir), `Modules/HomeContent/resources/views/layouts/master.blade.php` (unused generator scaffold)
- Modify: `Modules/HomeContent/vite.config.js` (remove the commented-out generator dead code: the unused `getFilePaths` helper block and duplicate commented `paths` export — keep the active config)

- [ ] **Step 1: Verify unreachability first.** Run `grep -rn "Admin\\\\Http\\\\Controllers\\\\BannerController\|Admin\\\\Http\\\\Controllers\\\\BeforeNavController" narzinapp-main --include="*.php" | grep -v "Modules/Admin/app/Http/Controllers"` (from repo root) — expected: no hits (the admin routes were converted to closures/redirects in Phase 1). Also `grep -rn "admin::banners\|admin::before-nav" narzinapp-main --include="*.php"` — expected: no hits. If any hit appears, STOP and report it instead of deleting.
- [ ] **Step 2: Delete the files/dirs listed above (`git rm -r`), trim vite.config.js dead comments.**
- [ ] **Step 3: Gates.** `php artisan test` → full suite green (expected 119 passed). `php artisan route:list --name=banners` still shows the redirect route (unchanged).
- [ ] **Step 4: Commit** `chore(home): remove unreachable legacy admin banner/before-nav code and scaffold noise`.

### Task 2: Web polish backlog

Working dir: `C:\xampp\htdocs\Narzin\narzin-main`.

**Files:**
- Modify: `src/components/pages/home/blocks/blockLink.jsx`, `src/components/pages/home/blocks/RailProductCard.jsx`, `src/test/setup.js`
- Test: extend `src/components/pages/home/blocks/__tests__/blockLink.test.jsx` and `__tests__/ProductRail.test.jsx`

- [ ] **Step 1 (TDD): add failing tests.** In `blockLink.test.jsx`, add to the `linkTarget` describe: `expect(linkTarget({ type: "url", value: "javascript:alert(1)" })).toBeNull();`. In `ProductRail.test.jsx`, change the second product's `min_price` to `20` and assert `screen.getByText("€20.00")` (update the existing `€49.99` assertion to stay as-is). Run vitest → these fail.
- [ ] **Step 2: implement.** In `blockLink.jsx` `linkTarget`, for `type === 'url'` return the external target only when `/^https?:\/\//i.test(String(link.value))`, else null. In `RailProductCard.jsx`, format the euro price with `Number(product.min_price).toFixed(2)` → `€{...}` (guard non-numeric → render nothing, preserving the existing `!= null` check). In `src/test/setup.js`, update the stale comment above the scrollIntoView shim to say it exists for legacy jsdom compatibility only (HeroSlider now uses track.scrollTo, which the shim does not cover — jsdom no-ops are handled in-component).
- [ ] **Step 3: Gates.** `npx vitest run` all green (33 tests expected), `npm run build` green.
- [ ] **Step 4: Commit** `fix(web-home): https-only url links and 2dp euro prices in rails`.

### Task 3: App polish backlog

Working dir: `C:\xampp\htdocs\Narzin\Narzin-app\user\narzin`.

**Files:**
- Modify: `lib/bussiness_logic/home_blocks_cubits/home_blocks_cubit.dart` (add `.timeout(const Duration(seconds: 15))` to the http.get future; on `TimeoutException` fall into the existing catch → HomeBlocksError; import `dart:async` if needed), `lib/presentation_layer/main_app_user/home_screens/blocks/promo_tiles_block.dart` (replace `Positioned(bottom: 6, left: 6, ...)` with `PositionedDirectional(bottom: 6, start: 6, ...)`), `lib/presentation_layer/main_app_user/home_screens/blocks/product_rail_block.dart` (euro price via `(product['min_price'] as num?)?.toStringAsFixed(2)` guarded — display `€{formatted}`), `lib/presentation_layer/main_app_user/home_screens/blocks/countdown_banner_block.dart` (render the optional `image` content field as a 25%-opacity background behind the text, matching the web block: wrap the Container's child in a Stack with a Positioned.fill CachedNetworkImage guarded by non-empty string, `errorWidget` → shrink)
- Test: extend `test/home_blocks_view_test.dart` — update the rail price assertion to `€49.99` (unchanged) and adjust if the formatting changes `€20` cases; add nothing else (image/timeout paths are integration-level).

- [ ] **Step 1: implement the four edits.**
- [ ] **Step 2: Gates.** `flutter analyze` on touched files → no new issues; `flutter test` → same totals (11 pass + 1 pre-existing).
- [ ] **Step 3: Commit** `fix(app-home): feed timeout, RTL-safe tile labels, 2dp euro, countdown background image`.

### Task 4: Documentation — retirement plan + phase notes

Working dir: repo root.

**Files:**
- Modify: `DEPLOYMENT.md`

- [ ] **Step 1:** Append to the "Homepage Builder (Phase 1)" section in `DEPLOYMENT.md`:

```markdown
### Legacy retirement (post-adoption — DO NOT do at launch)

The legacy endpoints `/api/v1/banners/mobile`, `/api/v1/banners/web`, `/api/v1/before-nav/current`
and the `banners` / `before_nav` tables are still served (from home_blocks) because installed app
builds older than the block-renderer release depend on them. Retire them only once app-store
analytics show the old versions are gone:

1. Remove the routes + controllers in `Modules/Banners`.
2. Drop the `banners` and `before_nav` tables (migration).
3. Remove the Flutter legacy home body + BannersCubit (the block view then becomes the only path).

Phase 3 notes: the app refetches blocks whenever the Home tab remounts (locale changes take effect
on next Home visit); the feed request has a 15s timeout; category/url block links are not yet
tappable in the app.
```

- [ ] **Step 2: Commit** `docs(home): legacy retirement plan and phase 3 notes`.

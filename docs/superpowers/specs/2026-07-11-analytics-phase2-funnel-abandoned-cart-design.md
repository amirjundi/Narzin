# Analytics Phase 2 — Conversion Funnel + Abandoned Cart — Design

**Date:** 2026-07-11
**Status:** Approved (design)

## Summary

Phase 1 captures the behavioral event streams; Phase 2 is the first phase that
*shows admins something*. It adds two read-only query services over the Phase 1
tables and a new admin **Funnel** statistics page:

- **Conversion funnel** — how many sessions progress through
  `sessions → product_view → cart_add → checkout_start → placed`, with
  stage-to-stage conversion and overall conversion rate.
- **Abandoned carts** — sessions that added to cart but never placed an order
  within a configurable window, with cart value, user (if known), and age.

It also introduces the **`DateRange` + `StatisticsQueryService`** foundation
(deferred from Phase 1): a date-range value object every reporting phase reuses,
replacing the hardcoded "last 30 days" windows.

No new tables. No client changes. Reporting is a new server-rendered Blade page,
matching the existing `admin::statistics.*` pages.

## Data-source reality (read this first)

Each funnel stage draws from a Phase 1 table. Their **current** population state
matters for what admins will actually see on day one:

| Stage | Source table | Populated now? |
|-------|-------------|----------------|
| sessions | `visit_sessions` | ⏳ awaits `/track/session` client hook |
| product_view | `user_product_views` | ✅ **yes** — web `ProductPage.jsx` already calls `/v1/telemetry/view` |
| cart_add | `cart_events` | ⏳ awaits `/track/cart` client hook |
| checkout_start | `checkout_events` | ✅ yes — server-side (CheckoutController) |
| placed | `checkout_events` | ✅ yes — server-side |

Consequences, stated honestly:
- The funnel's **`product_view → checkout_start → placed`** path has real web
  data immediately.
- **Abandoned cart depends entirely on `cart_events`**, so the report is
  correct but **empty until the `/track/cart` client hook is wired** (a small
  separate task, per the backend-first decision). The page must say so rather
  than look broken.
- The page renders every stage's real count and never fabricates numbers;
  stages awaiting client hooks simply read as 0 until those hooks land.

## Component 1 — DateRange value object

The reusable date-range foundation. Every reporting service in Phase 2+ accepts
a `DateRange` directly.

- **`DateRange`** (`Modules/Admin/app/Support/DateRange.php`, namespace
  `Modules\Admin\Support`): immutable, `from` (Carbon) + `to` (Carbon). Factory
  `DateRange::fromRequest(Request $r, int $defaultDays = 30)` reads optional
  `from`/`to` query params (`Y-m-d`), defaulting to `[now()->subDays(30), now()]`.
  `to` is treated as end-of-day. Guards: invalid/absent input → default;
  `from > to` → default.

> **Deviation from the roadmap spec (intentional):** the roadmap named a
> `StatisticsQueryService` seed here. Building it in Phase 2 would be an empty
> holder with no methods (dead code), so it's dropped — the funnel/abandoned
> services take `DateRange` directly. A shared query service can be extracted
> later if/when two phases actually share a helper (Phase 10's CSV/date-range
> work is the likely trigger).

## Component 2 — FunnelService

`Modules/Admin/app/Services/FunnelService.php`. One public method:

`funnel(DateRange $range): array` returning an ordered list of stages, each:
`['key' => 'product_view', 'label' => 'Product View', 'count' => 1234,
  'conversion_from_prev' => 0.42]` plus a top-level
`overall_conversion` = placed / sessions (0 when sessions is 0).

Stage counts = **distinct actor** within the range, where an actor key is
`session_id` when present, else `user:{user_id}`. This matters because
`checkout_events` currently carry a null `session_id` (the client does not yet
forward `session_id` to `placeOrder`, only `user_id` is set) — counting by raw
`session_id` alone would make the checkout stages read ~0 despite the events
being captured. Rows where both `session_id` and `user_id` are null are excluded.

- sessions: distinct actor in `visit_sessions` where `first_seen_at` in range
- product_view: distinct actor in `user_product_views` where `created_at` in range
- cart_add: distinct actor in `cart_events` where `action='add'` and `occurred_at` in range
- checkout_start: distinct actor in `checkout_events` where `step='checkout_start'` and `occurred_at` in range
- placed: distinct actor in `checkout_events` where `step='placed'` and `occurred_at` in range

Implement the actor key in SQL as
`COALESCE(session_id, CONCAT('user:', user_id))` and count distinct of that
(portable across MySQL/Postgres and the SQLite test DB). Each stage is one
indexed aggregate query (Phase 1's `occurred_at` / join-key indexes cover the
range/filter predicates).

`conversion_from_prev` = `count / previous_stage_count` (null for the first
stage, 0 when previous is 0).

Rationale for distinct-actor (not raw event) counts: a funnel measures *people
progressing*, so one actor that added to cart three times is one `cart_add`.
**Caveat (already noted above):** because `session_id` is not yet end-to-end,
the actor key changes identity across stages (a `session_id` for views, a
`user:` key for checkout), so stage-to-stage numbers are per-stage *volumes*,
not a single stitched actor journey. True stitched conversion arrives once the
client hooks forward `session_id` through cart and checkout. The page reports
the honest per-stage volumes today.

## Component 3 — AbandonedCartService

`Modules/Admin/app/Services/AbandonedCartService.php`.

`abandoned(DateRange $range, ?int $windowHours = null): Collection` where
`windowHours` defaults to `config('telemetry.abandoned_cart_hours', 24)`.

**Definition:** a `session_id` is abandoned when, within `$range`, it has ≥1
`cart_add` event AND has **no** `placed` `checkout_event` at any time AND its
last `cart_event` is older than `windowHours` ago (still "in limbo", not just
mid-session).

Each row: `session_id`, `user_id` + user name/email (if known via
`visit_sessions`/`users`), `cart_value`, `last_activity_at`, `age_hours`,
`item_count`.

**Cart value** = sum over the session's *current* cart state of
`unit_price × quantity`, where current state is the net of add/remove/update
events per `(product_id, variant_id)` (last-write-wins on quantity; removed
items excluded). Computed in the service from the session's `cart_events`.
Rows with a non-positive net cart are excluded (fully-removed carts aren't
abandoned).

Config: add `'abandoned_cart_hours' => env('ABANDONED_CART_HOURS', 24)` to
`Modules/Telemetry/config/config.php`.

## Component 4 — Reporting (new Blade Funnel page)

- Route: `Route::get('statistics/funnel', [StatisticsController::class,
  'funnelStatistics'])->name('statistics.funnel');` in
  `Modules/Admin/routes/web.php` (alongside the existing 4 statistics routes).
- Controller: add `funnelStatistics(Request $request)` to
  `Modules\Admin\Http\Controllers\StatisticsController` — builds a `DateRange`
  from the request, calls `FunnelService::funnel` and
  `AbandonedCartService::abandoned`, returns `view('admin::statistics.funnel', ...)`.
- View: `Modules/Admin/resources/views/statistics/funnel.blade.php`, using
  `<x-admin-layout>` like the existing statistics pages. Shows:
  - a date-range filter (from/to, submits as GET query params);
  - the funnel as a horizontal stage chart with count + stage-to-stage % and a
    headline overall-conversion figure;
  - a short "some stages populate once the mobile/web cart + session tracking
    hooks are live" note so partial data isn't mistaken for a bug;
  - the abandoned-cart table (session, user, cart value, item count, age),
    with an empty-state row: "No abandoned carts yet — cart tracking
    (`/track/cart`) is not wired into the apps yet."
- Nav: add a "Funnel" (or "Conversion") link wherever the existing
  statistics links live in the admin sidebar. (Plan locates the nav partial.)

## Out of scope / deferred

- **Abandoned-cart recovery emails** (the send) — a thin follow-on after the
  data is flowing.
- **The `/track/cart` and `/track/session` client hooks** — separate small
  client tasks; Phase 2 is backend-only and reads whatever they capture.
- **CSV export** — arrives with Phase 10's reporting-UX polish (the funnel page
  gets the date-range filter now; CSV later).

## Testing

- `DateRange`: defaults to last 30 days; parses valid `from`/`to`; falls back
  on invalid input; `from > to` → default.
- `FunnelService`: seeded events across the five tables produce the correct
  distinct-session stage counts and stage-to-stage conversions; a session that
  adds to cart twice counts once at `cart_add`; events outside the range are
  excluded.
- `AbandonedCartService`: a session with a `cart_add` and no `placed` older than
  the window IS abandoned; one that later placed is excluded; one whose last
  cart activity is within the window is excluded; cart value reflects
  add/remove/update net state; window is config-driven.
- Funnel page: `GET statistics/funnel` renders 200 for an admin, respects
  `from`/`to`, and shows the abandoned-cart empty-state when there are none.

## Cross-cutting notes

- **Performance:** every funnel/abandoned query is range-bounded and hits the
  Phase 1 indexes (`occurred_at`, join keys). Abandoned-cart value computation
  loads only the abandoned sessions' `cart_events`, not the whole table.
- **No data fabrication:** counts are whatever the tables hold; client-dependent
  stages read 0 until their hooks land, and the UI says so.

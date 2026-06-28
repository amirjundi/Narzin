# Role Model Cleanup — Design

**Date:** 2026-06-28
**Status:** Approved (design)

## Summary

The codebase has three half-built ways to express a user's role, only one of
which actually drives behavior. This cleanup makes the **role tables the single
source of truth**, exposes a small role API on the `User` model, and removes the
vestigial `user_type_id` column / `user_types` table / `UserType` model and the
dead references to them.

## Current state (the problem)

- **`users.user_type_id`** (bigint, FK `users_user_type_id_foreign` → `user_types`):
  set to `1` (Customer) on app registration, marked `prohibited` on profile
  update, and its only real query is **commented out** in `LowStockAlertCommand`.
  The vendor/admin creation flows never set it, so a vendor's `user_type_id` is
  actually `NULL` — the field is inconsistent and unused for access control.
- **`'role' => 'vendor'`** in `VendorController@store`: there is **no `role`
  column** on `users`, so this mass-assignment is a silent no-op.
- **The real enforcement** is the tables: `AdminMiddleware` checks
  `users_admins` (`user_id` + `is_active = 1`); `VendorAccountMiddleware` checks
  `vendors` (`user_id`). The role-separation guard (a user cannot be both vendor
  and admin) already lives on those models.

## Decision

Roles derive **only** from the tables. `user_type_id`, `user_types`, the
`UserType` model, `UserTypeSeeder`, and the dead `role` assignment are removed.
"Customer" is the implicit default: any user who is neither an admin nor a vendor.

## Goals

- A single, clear role API on `User`.
- Remove the misleading parallel `user_type_id` representation entirely.
- No change to existing access control (middleware already uses the tables).

## Non-goals

- Changing `AdminMiddleware` / `VendorAccountMiddleware` logic.
- Changing the vendor/admin creation flows beyond deleting the dead `role` line.
- A roles/permissions package — overkill for three fixed, table-backed roles.
- Re-introducing any cached/denormalized role field.

## Architecture

The role helpers read `users_admins` / `vendors` via `DB::table(...)` rather than
importing the feature-module models, keeping the core `User` model decoupled from
the modules. This matches the pattern the existing separation guard already uses.

## Components

**`User` role API** (new methods on `app/Models/User.php`):

- `isAdmin(): bool` — `DB::table('users_admins')->where('user_id', $this->id)->where('is_active', 1)->exists()`. Mirrors `AdminMiddleware` exactly.
- `isVendor(): bool` — `DB::table('vendors')->where('user_id', $this->id)->whereNull('deleted_at')->exists()`. A non-soft-deleted vendor record (vendors use SoftDeletes). This is the role check; vendor *account status* (`status = 'Active'`) remains a separate concern handled by `VendorAccountMiddleware`.
- `isCustomer(): bool` — `!$this->isAdmin() && !$this->isVendor()`.
- `primaryRole(): string` — returns `'admin'`, `'vendor'`, or `'customer'` with that precedence (admin > vendor > customer), for display/logging.

Each result is **memoized per model instance** (a private cache array) so multiple
checks within one request don't re-query; the cache is naturally short-lived
(per request / per loaded model).

## Data changes

A migration in `narzinapp-main/database/migrations` (core app):

**up():**
1. On `users`: `dropForeign(['user_type_id'])` (constraint `users_user_type_id_foreign`), then `dropColumn('user_type_id')`.
2. `Schema::dropIfExists('user_types')`.

**down()** (best-effort restore): recreate `user_types` (`id`, `name`,
timestamps), re-add a nullable `user_type_id` to `users`, and restore the FK.

## Code changes (remove the vestigial references)

- `app/Models/UserType.php` — delete.
- `database/seeders/UserTypeSeeder.php` — delete; remove its call from
  `database/seeders/DatabaseSeeder.php` if present.
- `database/seeders/TestUsersSeeder.php` — remove the `'user_type_id' => 3|2|1`
  lines (the seeded admin/vendor/customer are already distinguished by their
  `users_admins` / `vendors` records).
- `app/Models/User.php` — remove `'user_type_id'` from `$fillable`; add the four
  role methods above.
- `app/Http/Controllers/V1/Api/Auth/RegisterController.php` — remove
  `'user_type_id' => 1`.
- `app/Http/Controllers/V1/Api/Auth/UpdateProfileController.php` — remove the
  `'user_type_id' => ['prohibited']` rule.
- `Modules/Admin/app/Http/Controllers/VendorController.php@store` — remove the
  dead `'role' => 'vendor'` line from the `User::create([...])`.
- `app/Console/Commands/LowStockAlertCommand.php` — replace the commented-out
  `User::where('user_type_id', 3)->get()` with the real admin source:
  `User::whereIn('id', DB::table('users_admins')->where('is_active', 1)->pluck('user_id'))->get()`,
  so the "low stock → notify admins" intent is correctly wired.

## Edge cases

- **Migration order:** the FK must be dropped before the `user_type_id` column,
  and the column before the `user_types` table (it references it).
- **Registration:** after the column is gone, `RegisterController` must not pass
  `user_type_id` (handled above) or the insert would fail.
- A user with neither an admin nor a vendor record is a customer (the default) —
  `isCustomer()` is true.
- Memoization must key off the loaded instance; a freshly re-queried `User`
  re-evaluates (acceptable — role changes are rare and admin-driven).

## Testing

- **`User` role API** (feature, sqlite): a user with a `users_admins` (active)
  row → `isAdmin` true, `isVendor`/`isCustomer` false, `primaryRole` `'admin'`;
  a user with a `vendors` row → `isVendor` true, `primaryRole` `'vendor'`; a
  plain user → `isCustomer` true, `primaryRole` `'customer'`; an inactive
  `users_admins` row (`is_active = 0`) → `isAdmin` false.
- **Registration** (feature): registering a user still succeeds and the user is
  a customer (no `user_type_id` involved).
- **Regression:** the existing role-separation and admin/vendor middleware tests
  still pass; the full suite stays green.

## Rollout

The migration is destructive (drops a column + table) but pre-production with no
data depending on `user_type_id`. Code references are removed in the same change
so nothing writes the dropped column. Ships through the normal CI deploy; the
migration runs in `deploy-api.sh`.

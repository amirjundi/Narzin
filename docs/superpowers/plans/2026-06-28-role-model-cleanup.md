# Role Model Cleanup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the role tables (`users_admins`, `vendors`) the single source of truth for a user's role, expose a clean role API on `User`, and remove the vestigial `user_type_id` column / `user_types` table / `UserType` model and their dead references.

**Architecture:** Role helpers on the core `User` model read `users_admins` / `vendors` via `DB::table(...)` (decoupled from the feature modules, matching the existing separation guard). A destructive migration drops the unused `user_type_id` column + FK and the `user_types` table; the code that referenced them is removed in the same change.

**Tech Stack:** Laravel 11 (modular, nwidart), MySQL (prod) / sqlite `:memory:` (tests, FKs enforced), PHPUnit.

## Global Constraints

- Single source of truth: a user is an **admin** iff a row in `users_admins` has `user_id = id` AND `is_active = 1` (mirrors `AdminMiddleware` exactly); a **vendor** iff a non-soft-deleted row in `vendors` has `user_id = id`; a **customer** iff neither.
- Role helpers query via `DB::table(...)` — do NOT import `Vendor` / `UserAdmin` into the core `User` model.
- `isVendor()` is the *role* check (a vendor record exists). Vendor account *status* (`status = 'Active'`) stays a separate concern owned by `VendorAccountMiddleware` — do not fold it in.
- The migration order is FK → column → table: drop the FK before the `user_type_id` column, drop the column before the `user_types` table.
- No change to `AdminMiddleware` / `VendorAccountMiddleware` logic, nor to the role-separation guard.
- Migration uses the `2026_06_28_*` prefix so it sorts after existing migrations.
- Tests run on sqlite with foreign keys enforced — create required parent rows. Pre-production: no data depends on `user_type_id`.

---

### Task 1: `User` role API (additive)

**Files:**
- Modify: `narzinapp-main/app/Models/User.php` (add the `DB` import + four methods + a memo cache)
- Test: `narzinapp-main/tests/Feature/UserRoleApiTest.php`

**Interfaces:**
- Produces on `App\Models\User`: `isAdmin(): bool`, `isVendor(): bool`, `isCustomer(): bool`, `primaryRole(): string` (`'admin'|'vendor'|'customer'`).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/UserRoleApiTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Modules\Vendor\Models\Vendor;
use Tests\TestCase;

class UserRoleApiTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create([
            'name' => 'U', 'email' => 'u' . uniqid() . '@t.test',
            'password' => 'secret123', 'email_verified_at' => now(),
        ]);
    }

    public function test_active_admin_user(): void
    {
        $u = $this->user();
        UserAdmin::create(['user_id' => $u->id, 'is_active' => 1]);

        $this->assertTrue($u->isAdmin());
        $this->assertFalse($u->isVendor());
        $this->assertFalse($u->isCustomer());
        $this->assertSame('admin', $u->primaryRole());
    }

    public function test_vendor_user(): void
    {
        $u = $this->user();
        Vendor::create(['user_id' => $u->id, 'store_name_in_arabic' => 'م', 'store_name_in_german' => 'L', 'status' => 'Active']);

        $this->assertTrue($u->isVendor());
        $this->assertFalse($u->isAdmin());
        $this->assertFalse($u->isCustomer());
        $this->assertSame('vendor', $u->primaryRole());
    }

    public function test_plain_user_is_customer(): void
    {
        $u = $this->user();

        $this->assertTrue($u->isCustomer());
        $this->assertFalse($u->isAdmin());
        $this->assertFalse($u->isVendor());
        $this->assertSame('customer', $u->primaryRole());
    }

    public function test_inactive_admin_row_is_not_admin(): void
    {
        $u = $this->user();
        UserAdmin::create(['user_id' => $u->id, 'is_active' => 0]);

        $this->assertFalse($u->isAdmin());
        $this->assertTrue($u->isCustomer());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzinapp-main && php artisan test --filter=UserRoleApiTest`
Expected: FAIL — `isAdmin()` etc. are not defined.

- [ ] **Step 3: Add the `DB` import**

In `app/Models/User.php`, add to the imports at the top (after the existing `use` lines):

```php
use Illuminate\Support\Facades\DB;
```

- [ ] **Step 4: Add the role API to `User`**

Inside the `User` class body (e.g. after the existing properties/relationships), add:

```php
    /**
     * Per-instance memo so repeated role checks in one request don't re-query.
     * @var array<string,bool>
     */
    private array $roleCache = [];

    /** A user is an admin iff an active users_admins row exists (mirrors AdminMiddleware). */
    public function isAdmin(): bool
    {
        return $this->roleCache['admin'] ??= DB::table('users_admins')
            ->where('user_id', $this->id)
            ->where('is_active', 1)
            ->exists();
    }

    /** A user is a vendor iff a non-soft-deleted vendors row exists (the role; not the account status). */
    public function isVendor(): bool
    {
        return $this->roleCache['vendor'] ??= DB::table('vendors')
            ->where('user_id', $this->id)
            ->whereNull('deleted_at')
            ->exists();
    }

    /** Customer is the default: neither admin nor vendor. */
    public function isCustomer(): bool
    {
        return ! $this->isAdmin() && ! $this->isVendor();
    }

    /** Highest-precedence role for display/logging: admin > vendor > customer. */
    public function primaryRole(): string
    {
        if ($this->isAdmin()) {
            return 'admin';
        }
        if ($this->isVendor()) {
            return 'vendor';
        }
        return 'customer';
    }
```

(`??=` only assigns when the cached value is unset/null, so a cached `false` is preserved — no re-query.)

- [ ] **Step 5: Run test to verify it passes**

Run: `cd narzinapp-main && php artisan test --filter=UserRoleApiTest`
Expected: PASS (4 tests).

- [ ] **Step 6: Commit**

```bash
git add narzinapp-main/app/Models/User.php narzinapp-main/tests/Feature/UserRoleApiTest.php
git commit -m "feat(roles): add User::isAdmin/isVendor/isCustomer/primaryRole (table-derived)"
```

---

### Task 2: Remove the vestigial `user_type_id` / `user_types` mechanism

**Files:**
- Create: `narzinapp-main/database/migrations/2026_06_28_000020_drop_user_types.php`
- Delete: `narzinapp-main/app/Models/UserType.php`
- Delete: `narzinapp-main/database/seeders/UserTypeSeeder.php`
- Modify: `narzinapp-main/app/Models/User.php` (remove `'user_type_id'` from `$fillable`)
- Modify: `narzinapp-main/app/Http/Controllers/V1/Api/Auth/RegisterController.php` (remove `'user_type_id' => 1`)
- Modify: `narzinapp-main/app/Http/Controllers/V1/Api/Auth/UpdateProfileController.php` (remove `'user_type_id' => ['prohibited']`)
- Modify: `narzinapp-main/Modules/Admin/app/Http/Controllers/VendorController.php` (remove dead `'role' => 'vendor'`)
- Modify: `narzinapp-main/database/seeders/TestUsersSeeder.php` (remove `'user_type_id' => 3|2|1` lines)
- Modify: `narzinapp-main/database/seeders/DatabaseSeeder.php` (remove the `UserTypeSeeder` call if present)
- Modify: `narzinapp-main/app/Console/Commands/LowStockAlertCommand.php` (rewire the admin lookup)
- Test: `narzinapp-main/tests/Feature/UserTypeRemovedTest.php`

**Interfaces:**
- Consumes: the `User` role API from Task 1 (`isCustomer()`).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/UserTypeRemovedTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserTypeRemovedTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_type_column_and_table_are_gone(): void
    {
        $this->assertFalse(Schema::hasColumn('users', 'user_type_id'));
        $this->assertFalse(Schema::hasTable('user_types'));
    }

    public function test_user_can_be_created_without_user_type_and_is_customer(): void
    {
        $u = User::create([
            'name' => 'X', 'email' => 'x' . uniqid() . '@t.test',
            'password' => 'secret123', 'email_verified_at' => now(),
        ]);

        $this->assertTrue($u->isCustomer());
        $this->assertSame('customer', $u->primaryRole());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzinapp-main && php artisan test --filter=UserTypeRemovedTest`
Expected: FAIL — `user_type_id` column / `user_types` table still exist.

- [ ] **Step 3: Create the drop migration (cross-DB safe)**

`narzinapp-main/database/migrations/2026_06_28_000020_drop_user_types.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // MySQL requires dropping the FK before the column; sqlite rebuilds the
            // table on dropColumn and has no named FK to drop.
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->dropForeign(['user_type_id']);
            }
            $table->dropColumn('user_type_id');
        });

        Schema::dropIfExists('user_types');
    }

    public function down(): void
    {
        Schema::create('user_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_type_id')->nullable();
            $table->foreign('user_type_id')->references('id')->on('user_types');
        });
    }
};
```

- [ ] **Step 4: Remove `'user_type_id'` from `User::$fillable`**

In `app/Models/User.php`, delete the `'user_type_id',` entry from the `$fillable` array.

- [ ] **Step 5: Delete the `UserType` model and seeder**

```bash
rm narzinapp-main/app/Models/UserType.php
rm narzinapp-main/database/seeders/UserTypeSeeder.php
```

Then in `database/seeders/DatabaseSeeder.php`, if there is a `$this->call(UserTypeSeeder::class);` line (and/or a `use Database\Seeders\UserTypeSeeder;` import), remove it.

- [ ] **Step 6: Remove the dead references**

In `app/Http/Controllers/V1/Api/Auth/RegisterController.php`, delete the `'user_type_id' => 1,` line from the `User::create([...])` array.

In `app/Http/Controllers/V1/Api/Auth/UpdateProfileController.php`, delete the `'user_type_id' => ['prohibited'],` validation rule.

In `Modules/Admin/app/Http/Controllers/VendorController.php`, delete the dead `'role' => 'vendor',` line from the `User::create([...])` array in `store()`.

In `database/seeders/TestUsersSeeder.php`, delete the three `'user_type_id' => 3,` / `=> 2,` / `=> 1,` lines (the admin/vendor/customer are already distinguished by their `users_admins` / `vendors` records created in that seeder).

- [ ] **Step 7: Rewire the low-stock admin lookup**

In `app/Console/Commands/LowStockAlertCommand.php`, replace the commented-out line `// $admins = User::where('user_type_id', 3)->get();` with the real admin source. Add `use Illuminate\Support\Facades\DB;` at the top if not present, then:

```php
        $admins = User::whereIn(
            'id',
            DB::table('users_admins')->where('is_active', 1)->pluck('user_id')
        )->get();
```

(If `$admins` is used further down the command, leave that usage intact; this only changes how the collection is built.)

- [ ] **Step 8: Run the new test + full regression**

Run: `cd narzinapp-main && php artisan test --filter="UserTypeRemovedTest|UserRoleApiTest|RoleSeparationTest"`
Expected: PASS.

Then run the whole suite to confirm nothing referenced the dropped column:

Run: `cd narzinapp-main && php artisan test`
Expected: all PASS.

- [ ] **Step 9: Commit**

```bash
git add narzinapp-main/database/migrations/2026_06_28_000020_drop_user_types.php \
        narzinapp-main/app/Models/User.php \
        narzinapp-main/app/Http/Controllers/V1/Api/Auth/RegisterController.php \
        narzinapp-main/app/Http/Controllers/V1/Api/Auth/UpdateProfileController.php \
        narzinapp-main/Modules/Admin/app/Http/Controllers/VendorController.php \
        narzinapp-main/database/seeders/TestUsersSeeder.php \
        narzinapp-main/database/seeders/DatabaseSeeder.php \
        narzinapp-main/app/Console/Commands/LowStockAlertCommand.php \
        narzinapp-main/tests/Feature/UserTypeRemovedTest.php
git add -u narzinapp-main/app/Models/UserType.php narzinapp-main/database/seeders/UserTypeSeeder.php
git commit -m "refactor(roles): drop user_type_id/user_types; tables are the single source of truth"
```

---

## Deployment

After merge to `main`, CI runs the drop migration via `deploy-api.sh`. Because the code references are removed in the same change, nothing writes the dropped column during/after the migration. The deploy `migrate --force` runs the FK→column→table drop on MySQL.

## Self-review notes

- **Spec coverage:** role API (Task 1); column/FK/table drop + model/seeder deletion + all dead-reference removals + low-stock rewire (Task 2). Both spec testing requirements covered: role API values (Task 1) and "registration/user creation still works as a customer" (Task 2's `test_user_can_be_created_without_user_type_and_is_customer`, plus the full-suite regression which exercises the register-dependent tests).
- **Naming consistency:** `isAdmin`/`isVendor`/`isCustomer`/`primaryRole` are identical across Task 1's definition, the test, and Task 2's usage. The admin check (`users_admins` + `is_active = 1`) and vendor check (`vendors` + `deleted_at IS NULL`) match the Global Constraints verbatim.
- **Cross-DB migration:** the `sqlite` branch avoids `dropForeign` (sqlite rebuilds on `dropColumn`); MySQL drops the FK first. Verified by the test running on sqlite under `RefreshDatabase`.
- Tests create their own parent rows; the role-API tests give each user a single role so the separation guard never trips.

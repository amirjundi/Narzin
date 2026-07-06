# SHEIN-style Top Bar Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rework the storefront navbar's right side into a SHEIN-style 4-icon cluster (Account, Support/WhatsApp, Cart, Globe/language), backed by an admin-configurable WhatsApp number and a small Recently Viewed page.

**Architecture:** Backend gains a reusable key-value `site_settings` store (Admin module) with an admin Blade form and a public read API. The React storefront fetches public settings into a Redux slice; the navbar's actions become three extracted Headless-UI dropdown components plus the existing cart link. A new Recently Viewed page reuses the existing `/v1/home/for-you` rail (given a stable machine key).

**Tech Stack:** Laravel 11 + nwidart/laravel-modules + PHPUnit; React 18 + Redux Toolkit + react-i18next + @headlessui/react + lucide-react + Vitest.

## Global Constraints

- German locale code is **`du`** everywhere on the frontend (backend `Locale::normalize()` maps `du → de`). Do **not** rename it. Language compares use `i18n.language === "ar"` / `=== "du"`; `changeLanguage("ar" | "du")`.
- Frontend translation files: `public/locales/ar/translation.json` and `public/locales/du/translation.json`.
- Currency is display-only: show `EUR (€)` as read-only. No multi-currency.
- Account menu shows **only existing features**: Sign in/Register, My Orders, Recently Viewed, Wishlist, My Wallet, Addresses, My Account, Logout. Do NOT add Messages/Coupons/Points/More Services.
- Frontend API instance: `import api from "../../api/axios"` (base `/api/`). Session id via `getSessionId()` from `src/helpers/session.js`.
- Backend admin routes live under the `admin.auth` middleware group in `Modules/Admin/routes/web.php`; admin sidebar links in `resources/views/components/admin/sidebar.blade.php`; admin Blade pages wrap in `<x-admin-layout>`.
- Run frontend tests from `narzin-main/`: `npm test`. Run backend tests from `narzinapp-main/`: `php artisan test`.
- Commit after each task. Backend and frontend are separate git-tracked trees under the same repo root `C:\xampp\htdocs\Narzin`; use full paths in `git add`.

---

## File Structure

**Backend (`narzinapp-main/`)**
- `Modules/Admin/database/migrations/2026_07_06_000000_create_site_settings_table.php` — key-value table (no seed).
- `Modules/Admin/app/Models/SiteSetting.php` — model + cached `get`/`publicSettings`/`flushCache`.
- `Modules/Admin/app/Http/Controllers/SiteSettingController.php` — admin `edit`/`update`, public API `publicIndex`.
- `Modules/Admin/resources/views/settings/edit.blade.php` — admin form.
- `Modules/Admin/routes/web.php` — admin settings routes (edit).
- `Modules/Admin/routes/api.php` — public settings route.
- `resources/views/components/admin/sidebar.blade.php` — nav link.
- `Modules/HomeContent/app/Http/Controllers/HomeController.php` — add `key` to for-you blocks.
- `tests/Feature/SiteSettingsTest.php`, `tests/Feature/ForYouRailKeyTest.php` — tests.

**Frontend (`narzin-main/`)**
- `src/components/New/navbar/waLink.js` (+ `__tests__/waLink.test.js`)
- `src/Store/slices/SettingsSlice.js` (+ `__tests__/SettingsSlice.test.js`)
- `src/Store/store.js` — register reducer.
- `src/App.jsx` — dispatch `fetchPublicSettings`; add `/recently-viewed` route.
- `src/components/New/navbar/LanguageMenu.jsx`
- `src/components/New/navbar/SupportMenu.jsx`
- `src/components/New/navbar/AccountMenu.jsx`
- `src/components/New/NavBar.jsx` — desktop cluster + mobile menu.
- `src/pages/MyAccountLayout.jsx` — `?tab=` support.
- `src/pages/RecentlyViewed.jsx`
- `public/locales/{ar,du}/translation.json` — new keys.

---

## Task 1: `site_settings` table + model

**Files:**
- Create: `narzinapp-main/Modules/Admin/database/migrations/2026_07_06_000000_create_site_settings_table.php`
- Create: `narzinapp-main/Modules/Admin/app/Models/SiteSetting.php`
- Test: `narzinapp-main/tests/Feature/SiteSettingsTest.php`

**Interfaces:**
- Produces: `Modules\Admin\Models\SiteSetting` with static `get(string $key, $default = null)`, `publicSettings(): array` (key=>value of `is_public` rows), `flushCache(): void`. Cache keys `site_settings.all` and `site_settings.public`.

- [ ] **Step 1: Write the failing test**

Create `narzinapp-main/tests/Feature/SiteSettingsTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\SiteSetting;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_returns_value_and_default(): void
    {
        SiteSetting::create(['key' => 'whatsapp_number', 'value' => '+964770', 'is_public' => true]);

        $this->assertSame('+964770', SiteSetting::get('whatsapp_number'));
        $this->assertSame('fallback', SiteSetting::get('missing', 'fallback'));
    }

    public function test_public_settings_only_returns_public_rows(): void
    {
        SiteSetting::create(['key' => 'whatsapp_number', 'value' => '111', 'is_public' => true]);
        SiteSetting::create(['key' => 'secret', 'value' => 'shh', 'is_public' => false]);

        $public = SiteSetting::publicSettings();

        $this->assertSame(['whatsapp_number' => '111'], $public);
    }

    public function test_flush_cache_reflects_updates(): void
    {
        SiteSetting::create(['key' => 'whatsapp_number', 'value' => 'old', 'is_public' => true]);
        $this->assertSame('old', SiteSetting::get('whatsapp_number'));

        SiteSetting::where('key', 'whatsapp_number')->update(['value' => 'new']);
        SiteSetting::flushCache();

        $this->assertSame('new', SiteSetting::get('whatsapp_number'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzinapp-main && php artisan test --filter=SiteSettingsTest`
Expected: FAIL — class `Modules\Admin\Models\SiteSetting` / table not found.

- [ ] **Step 3: Create the migration**

Create `narzinapp-main/Modules/Admin/database/migrations/2026_07_06_000000_create_site_settings_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->boolean('is_public')->default(false);
            $table->string('group')->nullable();
            $table->timestamps();
        });
        // No seed rows: the admin Settings page creates the whatsapp_number /
        // support_hours rows on first save via updateOrCreate (is_public=true).
        // Seeding them here would collide with tests that create the same keys.
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
```

- [ ] **Step 4: Create the model**

Create `narzinapp-main/Modules/Admin/app/Models/SiteSetting.php`:

```php
<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $table = 'site_settings';

    protected $fillable = ['key', 'value', 'is_public', 'group'];

    protected $casts = ['is_public' => 'boolean'];

    public static function get(string $key, $default = null)
    {
        $all = Cache::rememberForever('site_settings.all', fn () => static::pluck('value', 'key')->all());

        return $all[$key] ?? $default;
    }

    public static function publicSettings(): array
    {
        return Cache::rememberForever(
            'site_settings.public',
            fn () => static::where('is_public', true)->pluck('value', 'key')->all()
        );
    }

    public static function flushCache(): void
    {
        Cache::forget('site_settings.all');
        Cache::forget('site_settings.public');
    }
}
```

- [ ] **Step 5: Run migration for the test DB config and re-run the test**

Run: `cd narzinapp-main && php artisan test --filter=SiteSettingsTest`
Expected: PASS (3 tests). `RefreshDatabase` runs migrations automatically.

- [ ] **Step 6: Commit**

```bash
git add narzinapp-main/Modules/Admin/database/migrations/2026_07_06_000000_create_site_settings_table.php \
        narzinapp-main/Modules/Admin/app/Models/SiteSetting.php \
        narzinapp-main/tests/Feature/SiteSettingsTest.php
git commit -m "feat(admin): reusable site_settings key-value store"
```

---

## Task 2: Public settings API

**Files:**
- Modify: `narzinapp-main/Modules/Admin/app/Http/Controllers/SiteSettingController.php` (create in this task)
- Modify: `narzinapp-main/Modules/Admin/routes/api.php`
- Test: `narzinapp-main/tests/Feature/SiteSettingsTest.php` (extend)

**Interfaces:**
- Consumes: `SiteSetting::publicSettings()` (Task 1).
- Produces: `GET /api/v1/settings/public` → `{ "data": { "whatsapp_number": ..., "support_hours": ... } }`, no auth. Controller method `SiteSettingController::publicIndex()`.

- [ ] **Step 1: Write the failing test (append to SiteSettingsTest)**

Add this method to `narzinapp-main/tests/Feature/SiteSettingsTest.php`:

```php
    public function test_public_api_returns_only_public_settings(): void
    {
        SiteSetting::create(['key' => 'whatsapp_number', 'value' => '+964770123', 'is_public' => true]);
        SiteSetting::create(['key' => 'secret', 'value' => 'shh', 'is_public' => false]);

        $this->getJson('/api/v1/settings/public')
            ->assertOk()
            ->assertJsonPath('data.whatsapp_number', '+964770123')
            ->assertJsonMissingPath('data.secret');
    }
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzinapp-main && php artisan test --filter=test_public_api_returns_only_public_settings`
Expected: FAIL — 404 (route missing).

- [ ] **Step 3: Create the controller**

Create `narzinapp-main/Modules/Admin/app/Http/Controllers/SiteSettingController.php`:

```php
<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Admin\Models\SiteSetting;

class SiteSettingController extends Controller
{
    /** Public storefront read of whitelisted settings. */
    public function publicIndex(): JsonResponse
    {
        return response()->json(['data' => SiteSetting::publicSettings()]);
    }
}
```

- [ ] **Step 4: Register the public route**

In `narzinapp-main/Modules/Admin/routes/api.php`, add at the bottom (OUTSIDE the `auth:sanctum` group), next to the existing public `v1/products/{product}/variants` route:

```php
Route::get('v1/settings/public', [\Modules\Admin\Http\Controllers\SiteSettingController::class, 'publicIndex']);
```

- [ ] **Step 5: Run test to verify it passes**

Run: `cd narzinapp-main && php artisan test --filter=SiteSettingsTest`
Expected: PASS (4 tests).

- [ ] **Step 6: Commit**

```bash
git add narzinapp-main/Modules/Admin/app/Http/Controllers/SiteSettingController.php \
        narzinapp-main/Modules/Admin/routes/api.php \
        narzinapp-main/tests/Feature/SiteSettingsTest.php
git commit -m "feat(admin): public settings API (GET /api/v1/settings/public)"
```

---

## Task 3: Admin Settings page (form + save + sidebar link)

**Files:**
- Modify: `narzinapp-main/Modules/Admin/app/Http/Controllers/SiteSettingController.php` (add `edit`, `update`)
- Create: `narzinapp-main/Modules/Admin/resources/views/settings/edit.blade.php`
- Modify: `narzinapp-main/Modules/Admin/routes/web.php`
- Modify: `narzinapp-main/resources/views/components/admin/sidebar.blade.php`
- Test: `narzinapp-main/tests/Feature/SiteSettingsTest.php` (extend)

**Interfaces:**
- Consumes: `SiteSetting`, `admin.auth` middleware, `<x-admin-layout>`.
- Produces: routes `settings.edit` (GET `settings`), `settings.update` (POST `settings`). On save: `updateOrCreate` + `SiteSetting::flushCache()`.

- [ ] **Step 1: Write the failing tests (append to SiteSettingsTest)**

Add an `admin()` helper (copy the pattern from `HomeBlockAdminTest`) and two tests to `narzinapp-main/tests/Feature/SiteSettingsTest.php`:

```php
    private function admin(): \App\Models\User
    {
        $user = \App\Models\User::create([
            'name' => 'A', 'email' => 'a' . uniqid() . '@t.test',
            'password' => 'x', 'email_verified_at' => now(),
        ]);
        \Modules\Admin\Models\UserAdmin::create(['user_id' => $user->id, 'is_active' => 1]);

        return $user;
    }

    public function test_guests_cannot_reach_settings_page(): void
    {
        $this->get(route('settings.edit'))->assertRedirect();
    }

    public function test_admin_can_save_whatsapp_number(): void
    {
        $this->actingAs($this->admin())
            ->post(route('settings.update'), [
                'whatsapp_number' => '+964 770-123-4567',
                'support_hours' => 'Sun-Thu 9-18',
            ])
            ->assertRedirect(route('settings.edit'));

        $this->assertSame('+964 770-123-4567', SiteSetting::get('whatsapp_number'));
        $this->assertSame('Sun-Thu 9-18', SiteSetting::get('support_hours'));
    }
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `cd narzinapp-main && php artisan test --filter=SiteSettingsTest`
Expected: FAIL — route `settings.edit` not defined.

- [ ] **Step 3: Add controller methods**

Append to `narzinapp-main/Modules/Admin/app/Http/Controllers/SiteSettingController.php` (add `use Illuminate\Http\Request;` and `use Illuminate\Http\RedirectResponse;` at top):

```php
    /** Admin form. */
    public function edit()
    {
        return view('admin::settings.edit', [
            'whatsapp_number' => SiteSetting::get('whatsapp_number'),
            'support_hours' => SiteSetting::get('support_hours'),
        ]);
    }

    /** Persist admin-editable public settings. */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'whatsapp_number' => ['nullable', 'string', 'max:32', 'regex:/^[0-9+\s\-()]*$/'],
            'support_hours' => ['nullable', 'string', 'max:120'],
        ]);

        foreach (['whatsapp_number', 'support_hours'] as $key) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $validated[$key] ?? null, 'is_public' => true, 'group' => 'contact']
            );
        }
        SiteSetting::flushCache();

        return redirect()->route('settings.edit')->with('success', 'Settings saved.');
    }
```

- [ ] **Step 4: Create the Blade view**

Create `narzinapp-main/Modules/Admin/resources/views/settings/edit.blade.php`:

```blade
<x-admin-layout>
    <div class="max-w-lg mx-auto mt-8 bg-white shadow p-6 rounded">
        <h1 class="text-2xl font-bold mb-6">Site Settings</h1>

        @if (session('success'))
            <div class="mb-4 bg-green-100 text-green-700 p-3 rounded">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block mb-1 font-semibold" for="whatsapp_number">WhatsApp Number</label>
                <input type="text" name="whatsapp_number" id="whatsapp_number"
                       value="{{ old('whatsapp_number', $whatsapp_number) }}"
                       class="w-full border-gray-300 rounded p-2 focus:ring focus:ring-blue-200"
                       placeholder="e.g. +964 770 123 4567">
                <p class="mt-1 text-sm text-gray-500">Shown behind the storefront support icon. Leave empty to hide it.</p>
            </div>

            <div class="mb-4">
                <label class="block mb-1 font-semibold" for="support_hours">Support Hours (optional)</label>
                <input type="text" name="support_hours" id="support_hours"
                       value="{{ old('support_hours', $support_hours) }}"
                       class="w-full border-gray-300 rounded p-2 focus:ring focus:ring-blue-200"
                       placeholder="e.g. Sun–Thu, 9:00–18:00">
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
        </form>
    </div>
</x-admin-layout>
```

- [ ] **Step 5: Register the admin routes**

In `narzinapp-main/Modules/Admin/routes/web.php`, inside the `Route::middleware(['admin.auth'])->group(...)` block (near the `price-exchange` group), add:

```php
    Route::get('settings', [\Modules\Admin\Http\Controllers\SiteSettingController::class, 'edit'])->name('settings.edit');
    Route::post('settings', [\Modules\Admin\Http\Controllers\SiteSettingController::class, 'update'])->name('settings.update');
```

- [ ] **Step 6: Add the sidebar link**

In `narzinapp-main/resources/views/components/admin/sidebar.blade.php`, add a `<li>` next to the price-exchange / platform-markup links (mirror the existing block markup exactly):

```blade
                    <!-- Site Settings -->
                    <li>
                        <a href="{{ route('settings.edit') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ request()->routeIs('settings.*')
                                     ? 'bg-gradient-to-r from-slate-600 to-slate-500 text-white shadow-lg shadow-slate-500/30'
                                     : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg
                                        {{ request()->routeIs('settings.*') ? 'bg-white/20' : 'bg-slate-700/50' }}">
                                <span class="inline-flex items-center justify-center w-5 h-5"><i class="fa-solid fa-gear"></i></span>
                            </div>
                            <span x-show="sidebarOpen" class="font-medium">Settings</span>
                        </a>
                    </li>
```

- [ ] **Step 7: Run tests to verify they pass**

Run: `cd narzinapp-main && php artisan test --filter=SiteSettingsTest`
Expected: PASS (6 tests).

- [ ] **Step 8: Commit**

```bash
git add narzinapp-main/Modules/Admin/app/Http/Controllers/SiteSettingController.php \
        narzinapp-main/Modules/Admin/resources/views/settings/edit.blade.php \
        narzinapp-main/Modules/Admin/routes/web.php \
        narzinapp-main/resources/views/components/admin/sidebar.blade.php \
        narzinapp-main/tests/Feature/SiteSettingsTest.php
git commit -m "feat(admin): site settings page for WhatsApp number"
```

---

## Task 4: Stable `key` on for-you rails

**Files:**
- Modify: `narzinapp-main/Modules/HomeContent/app/Http/Controllers/HomeController.php` (~lines 70-101)
- Test: `narzinapp-main/tests/Feature/ForYouRailKeyTest.php`

**Interfaces:**
- Produces: each for-you block's `content` gains `key` — `'recently_viewed'` for the recently-viewed rail, `'recommended'` for the recommendations rail. Frontend selects by `content.key === 'recently_viewed'`.

- [ ] **Step 1: Write the failing test**

Create `narzinapp-main/tests/Feature/ForYouRailKeyTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\Telemetry\Models\UserProductView;
use Tests\TestCase;

class ForYouRailKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_recently_viewed_rail_has_stable_key(): void
    {
        // Minimal purchasable product (mirrors ProductRailResolverTest setup).
        $categoryId = Category::create([
            'name_arabic' => 'فئة', 'name_german' => 'Kategorie',
            'slug_arabic' => 'cat-ar-' . uniqid(), 'slug_german' => 'cat-de-' . uniqid(),
        ])->id;

        $product = Product::create([
            'name_arabic' => 'منتج', 'name_german' => 'Produkt',
            'slug_arabic' => 'p-ar-' . uniqid(), 'slug_german' => 'p-de-' . uniqid(),
            'category_id' => $categoryId, 'is_active' => true,
        ]);
        ProductVariant::create([
            'product_id' => $product->id, 'price' => 50, 'stock' => 10,
            'sku' => 'sku-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false,
        ]);

        $user = User::create([
            'name' => 'V', 'email' => 'v' . uniqid() . '@t.test',
            'password' => 'x', 'email_verified_at' => now(),
        ]);
        UserProductView::create([
            'user_id' => $user->id, 'product_id' => $product->id,
            'session_id' => 's-' . uniqid(), 'dwell_time_seconds' => 5,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/home/for-you?locale=du')
            ->assertOk();

        $keys = collect($response->json('data'))->pluck('content.key');
        $this->assertTrue($keys->contains('recently_viewed'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzinapp-main && php artisan test --filter=ForYouRailKeyTest`
Expected: FAIL — `content.key` is null/absent.

- [ ] **Step 3: Add the keys in HomeController::forYou**

In `narzinapp-main/Modules/HomeContent/app/Http/Controllers/HomeController.php`, add `'key' => 'recently_viewed',` to the recently-viewed block content and `'key' => 'recommended',` to the recommended block content:

```php
        // Recently viewed
        if (! empty($recent)) {
            $out[] = ['id' => $id++, 'type' => 'product_rail', 'content' => [
                'key'      => 'recently_viewed',
                'title'    => $locale === 'ar' ? 'شاهدت مؤخرًا' : ($locale === 'de' ? 'Kürzlich angesehen' : 'Recently Viewed'),
                'rule'     => 'manual',
                'products' => $recent,
            ]];
        }
```

and for the recommended block:

```php
                    $out[] = ['id' => $id++, 'type' => 'product_rail', 'content' => [
                        'key'      => 'recommended',
                        'title'    => $locale === 'ar' ? 'مختار لك' : ($locale === 'de' ? 'Für dich' : 'Recommended for You'),
                        'rule'     => 'manual',
                        'products' => $rec,
                    ]];
```

- [ ] **Step 4: Run test to verify it passes**

Run: `cd narzinapp-main && php artisan test --filter=ForYouRailKeyTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add narzinapp-main/Modules/HomeContent/app/Http/Controllers/HomeController.php \
        narzinapp-main/tests/Feature/ForYouRailKeyTest.php
git commit -m "feat(home): stable key on for-you rails for reuse"
```

---

## Task 5: `waLink` helper (frontend)

**Files:**
- Create: `narzin-main/src/components/New/navbar/waLink.js`
- Test: `narzin-main/src/components/New/navbar/__tests__/waLink.test.js`

**Interfaces:**
- Produces: `buildWhatsappUrl(number: string | null | undefined): string | null` — strips all non-digits; returns `https://wa.me/<digits>` or `null` when empty.

- [ ] **Step 1: Write the failing test**

Create `narzin-main/src/components/New/navbar/__tests__/waLink.test.js`:

```js
import { describe, it, expect } from "vitest";
import { buildWhatsappUrl } from "../waLink";

describe("buildWhatsappUrl", () => {
  it("strips non-digits and builds a wa.me link", () => {
    expect(buildWhatsappUrl("+964 770-123-4567")).toBe("https://wa.me/9647701234567");
  });

  it("returns null for empty / nullish input", () => {
    expect(buildWhatsappUrl("")).toBeNull();
    expect(buildWhatsappUrl(null)).toBeNull();
    expect(buildWhatsappUrl(undefined)).toBeNull();
  });

  it("returns null when there are no digits", () => {
    expect(buildWhatsappUrl("abc")).toBeNull();
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzin-main && npm test -- waLink`
Expected: FAIL — cannot resolve `../waLink`.

- [ ] **Step 3: Implement the helper**

Create `narzin-main/src/components/New/navbar/waLink.js`:

```js
// Build a wa.me chat URL from an admin-entered phone number.
// Strips everything except digits; returns null when there is nothing dialable.
export function buildWhatsappUrl(number) {
  if (!number) return null;
  const digits = String(number).replace(/\D/g, "");
  return digits ? `https://wa.me/${digits}` : null;
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `cd narzin-main && npm test -- waLink`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
git add narzin-main/src/components/New/navbar/waLink.js \
        narzin-main/src/components/New/navbar/__tests__/waLink.test.js
git commit -m "feat(navbar): waLink helper for WhatsApp chat URLs"
```

---

## Task 6: Settings Redux slice

**Files:**
- Create: `narzin-main/src/Store/slices/SettingsSlice.js`
- Modify: `narzin-main/src/Store/store.js`
- Test: `narzin-main/src/Store/slices/__tests__/SettingsSlice.test.js`

**Interfaces:**
- Produces: default export reducer; `fetchPublicSettings` thunk (GET `/v1/settings/public`); selectors `selectWhatsappNumber(state)`, `selectSupportHours(state)`. State shape `{ whatsapp_number: string|null, support_hours: string|null, status: 'idle'|'loading'|'succeeded'|'failed' }`. Registered in store under key `settings`.

- [ ] **Step 1: Write the failing test**

Create `narzin-main/src/Store/slices/__tests__/SettingsSlice.test.js`:

```js
import { describe, it, expect } from "vitest";
import reducer, { fetchPublicSettings, selectWhatsappNumber } from "../SettingsSlice";

const initial = { whatsapp_number: null, support_hours: null, status: "idle" };

describe("SettingsSlice", () => {
  it("sets loading on pending", () => {
    const state = reducer(initial, { type: fetchPublicSettings.pending.type });
    expect(state.status).toBe("loading");
  });

  it("stores settings on fulfilled", () => {
    const state = reducer(initial, {
      type: fetchPublicSettings.fulfilled.type,
      payload: { whatsapp_number: "+964770", support_hours: "9-18" },
    });
    expect(state.status).toBe("succeeded");
    expect(selectWhatsappNumber({ settings: state })).toBe("+964770");
    expect(state.support_hours).toBe("9-18");
  });

  it("marks failed and keeps number null on rejected", () => {
    const state = reducer(initial, { type: fetchPublicSettings.rejected.type });
    expect(state.status).toBe("failed");
    expect(state.whatsapp_number).toBeNull();
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzin-main && npm test -- SettingsSlice`
Expected: FAIL — cannot resolve `../SettingsSlice`.

- [ ] **Step 3: Implement the slice**

Create `narzin-main/src/Store/slices/SettingsSlice.js`:

```js
import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import api from "../../api/axios";

// Public, admin-configurable storefront settings (WhatsApp number, etc.).
export const fetchPublicSettings = createAsyncThunk(
  "settings/fetchPublic",
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get("/v1/settings/public");
      return response.data?.data || {};
    } catch (error) {
      return rejectWithValue(error.response?.data?.message || error.message);
    }
  }
);

const initialState = {
  whatsapp_number: null,
  support_hours: null,
  status: "idle",
};

const SettingsSlice = createSlice({
  name: "settings",
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchPublicSettings.pending, (state) => {
        state.status = "loading";
      })
      .addCase(fetchPublicSettings.fulfilled, (state, action) => {
        state.status = "succeeded";
        state.whatsapp_number = action.payload.whatsapp_number ?? null;
        state.support_hours = action.payload.support_hours ?? null;
      })
      .addCase(fetchPublicSettings.rejected, (state) => {
        state.status = "failed";
      });
  },
});

export const selectWhatsappNumber = (state) => state.settings.whatsapp_number;
export const selectSupportHours = (state) => state.settings.support_hours;

export default SettingsSlice.reducer;
```

- [ ] **Step 4: Register the reducer**

In `narzin-main/src/Store/store.js`, add the import and register it:

```js
import settingsReducer from "./slices/SettingsSlice";
```

and inside `reducer: { ... }` add:

```js
    settings: settingsReducer,
```

- [ ] **Step 5: Run test to verify it passes**

Run: `cd narzin-main && npm test -- SettingsSlice`
Expected: PASS (3 tests).

- [ ] **Step 6: Commit**

```bash
git add narzin-main/src/Store/slices/SettingsSlice.js \
        narzin-main/src/Store/slices/__tests__/SettingsSlice.test.js \
        narzin-main/src/Store/store.js
git commit -m "feat(store): settings slice for public storefront settings"
```

---

## Task 7: Bootstrap settings fetch on app mount

**Files:**
- Modify: `narzin-main/src/App.jsx`

**Interfaces:**
- Consumes: `fetchPublicSettings` (Task 6).

- [ ] **Step 1: Add the dispatch**

In `narzin-main/src/App.jsx`, import the thunk:

```js
import { fetchPublicSettings } from "./Store/slices/SettingsSlice";
```

and in the existing bootstrap `useEffect` (the one that dispatches `verifyToken`, `fetchCategories`, `fetchProducts`), add:

```js
    dispatch(fetchPublicSettings());
```

- [ ] **Step 2: Verify build/tests still pass**

Run: `cd narzin-main && npm test`
Expected: PASS (existing + new suites). No test targets App bootstrap directly; this is a smoke check that nothing broke.

- [ ] **Step 3: Commit**

```bash
git add narzin-main/src/App.jsx
git commit -m "feat(app): fetch public settings on mount"
```

---

## Task 8: LanguageMenu component

**Files:**
- Create: `narzin-main/src/components/New/navbar/LanguageMenu.jsx`
- Test: `narzin-main/src/components/New/navbar/__tests__/LanguageMenu.test.jsx`

**Interfaces:**
- Produces: default export `<LanguageMenu />`. Renders a `Globe` trigger; menu offers العربية / Deutsch (calls `i18n.changeLanguage("ar" | "du")` and sets `document.documentElement.dir/lang`) and a read-only "Currency: EUR (€)" row.

- [ ] **Step 1: Write the failing test**

Create `narzin-main/src/components/New/navbar/__tests__/LanguageMenu.test.jsx`:

```jsx
import { describe, it, expect } from "vitest";
import { screen, fireEvent } from "@testing-library/react";
import { renderWithProviders } from "../../../../test/renderWithProviders";
import LanguageMenu from "../LanguageMenu";

describe("LanguageMenu", () => {
  it("opens and shows language options and the currency read-out", () => {
    renderWithProviders(<LanguageMenu />, { language: "du" });
    fireEvent.click(screen.getByRole("button", { name: /language/i }));
    expect(screen.getByText(/Deutsch/)).toBeInTheDocument();
    expect(screen.getByText(/العربية/)).toBeInTheDocument();
    expect(screen.getByText(/EUR/)).toBeInTheDocument();
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzin-main && npm test -- LanguageMenu`
Expected: FAIL — cannot resolve `../LanguageMenu`.

- [ ] **Step 3: Implement the component**

Create `narzin-main/src/components/New/navbar/LanguageMenu.jsx`:

```jsx
import { Menu } from "@headlessui/react";
import { Globe, Check } from "lucide-react";
import { useTranslation } from "react-i18next";

const LANGS = [
  { code: "ar", label: "العربية", flag: "🇸🇦" },
  { code: "du", label: "Deutsch", flag: "🇩🇪" },
];

const LanguageMenu = () => {
  const { i18n } = useTranslation();

  const changeLanguage = (lng) => {
    i18n.changeLanguage(lng);
    document.documentElement.dir = i18n.dir();
    document.documentElement.lang = lng;
  };

  return (
    <Menu as="div" className="relative">
      <Menu.Button
        aria-label="Language"
        className="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
      >
        <Globe className="w-5 h-5" />
      </Menu.Button>
      <Menu.Items className="absolute end-0 mt-2 w-44 origin-top-end rounded-xl bg-white shadow-lg ring-1 ring-gray-200/70 focus:outline-none z-50 p-1.5">
        {LANGS.map((lng) => (
          <Menu.Item key={lng.code}>
            {({ active }) => (
              <button
                onClick={() => changeLanguage(lng.code)}
                className={`flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm ${
                  active ? "bg-blue-50 text-blue-700" : "text-gray-700"
                }`}
              >
                <span className="flex items-center gap-2">
                  <span className="text-base">{lng.flag}</span>
                  {lng.label}
                </span>
                {i18n.language === lng.code && <Check className="w-4 h-4" />}
              </button>
            )}
          </Menu.Item>
        ))}
        <div className="mt-1 border-t border-gray-100 px-3 py-2 text-xs text-gray-500">
          Currency: EUR (€)
        </div>
      </Menu.Items>
    </Menu>
  );
};

export default LanguageMenu;
```

- [ ] **Step 4: Run test to verify it passes**

Run: `cd narzin-main && npm test -- LanguageMenu`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add narzin-main/src/components/New/navbar/LanguageMenu.jsx \
        narzin-main/src/components/New/navbar/__tests__/LanguageMenu.test.jsx
git commit -m "feat(navbar): LanguageMenu dropdown (globe)"
```

---

## Task 9: SupportMenu component

**Files:**
- Create: `narzin-main/src/components/New/navbar/SupportMenu.jsx`
- Test: `narzin-main/src/components/New/navbar/__tests__/SupportMenu.test.jsx`

**Interfaces:**
- Consumes: `selectWhatsappNumber`, `selectSupportHours` (Task 6); `buildWhatsappUrl` (Task 5).
- Produces: default export `<SupportMenu />`. Renders **nothing** when no number is configured. Otherwise a `Headphones` trigger opening a popup with the number, optional hours, and a "Chat on WhatsApp" link (`buildWhatsappUrl(number)`, `target="_blank"`, `rel="noopener noreferrer"`).

- [ ] **Step 1: Write the failing test**

Create `narzin-main/src/components/New/navbar/__tests__/SupportMenu.test.jsx`:

```jsx
import { describe, it, expect } from "vitest";
import { screen, fireEvent } from "@testing-library/react";
import { renderWithProviders } from "../../../../test/renderWithProviders";
import settingsReducer from "../../../../Store/slices/SettingsSlice";
import SupportMenu from "../SupportMenu";

describe("SupportMenu", () => {
  it("renders nothing when no whatsapp number is set", () => {
    const { container } = renderWithProviders(<SupportMenu />, {
      reducers: { settings: settingsReducer },
      preloadedState: { settings: { whatsapp_number: null, support_hours: null, status: "succeeded" } },
    });
    expect(container).toBeEmptyDOMElement();
  });

  it("shows the number and a wa.me chat link when set", () => {
    renderWithProviders(<SupportMenu />, {
      reducers: { settings: settingsReducer },
      preloadedState: { settings: { whatsapp_number: "+964 770-123-4567", support_hours: "9-18", status: "succeeded" } },
    });
    fireEvent.click(screen.getByRole("button", { name: /support/i }));
    expect(screen.getByText(/\+964 770-123-4567/)).toBeInTheDocument();
    const link = screen.getByRole("link", { name: /whatsapp/i });
    expect(link).toHaveAttribute("href", "https://wa.me/9647701234567");
  });
});
```

> **Why real reducers:** `renderWithProviders` builds its store from `_` + whatever `reducers` you pass, so `preloadedState.settings` is only kept if a `settings` reducer exists (matches the `AnnouncementBar.test.jsx` convention).

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzin-main && npm test -- SupportMenu`
Expected: FAIL — cannot resolve `../SupportMenu`.

- [ ] **Step 3: Implement the component**

Create `narzin-main/src/components/New/navbar/SupportMenu.jsx`:

```jsx
import { Popover } from "@headlessui/react";
import { Headphones, MessageCircle } from "lucide-react";
import { useSelector } from "react-redux";
import { useTranslation } from "react-i18next";
import { selectWhatsappNumber, selectSupportHours } from "../../../Store/slices/SettingsSlice";
import { buildWhatsappUrl } from "./waLink";

const SupportMenu = () => {
  const { t } = useTranslation();
  const number = useSelector(selectWhatsappNumber);
  const hours = useSelector(selectSupportHours);
  const waUrl = buildWhatsappUrl(number);

  if (!waUrl) return null;

  return (
    <Popover as="div" className="relative">
      <Popover.Button
        aria-label={t("topbar.support", "Support")}
        className="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
      >
        <Headphones className="w-5 h-5" />
      </Popover.Button>
      <Popover.Panel className="absolute end-0 mt-2 w-64 origin-top-end rounded-xl bg-white shadow-lg ring-1 ring-gray-200/70 focus:outline-none z-50 p-4">
        <p className="text-sm font-semibold text-gray-900 mb-1">
          {t("topbar.customer_support", "Customer Support")}
        </p>
        <p className="text-sm text-gray-700">{number}</p>
        {hours && <p className="text-xs text-gray-500 mt-0.5">{hours}</p>}
        <a
          href={waUrl}
          target="_blank"
          rel="noopener noreferrer"
          className="mt-3 flex items-center justify-center gap-2 w-full py-2 px-3 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
        >
          <MessageCircle className="w-4 h-4" />
          {t("topbar.chat_whatsapp", "Chat on WhatsApp")}
        </a>
      </Popover.Panel>
    </Popover>
  );
};

export default SupportMenu;
```

- [ ] **Step 4: Run test to verify it passes**

Run: `cd narzin-main && npm test -- SupportMenu`
Expected: PASS (2 tests).

- [ ] **Step 5: Commit**

```bash
git add narzin-main/src/components/New/navbar/SupportMenu.jsx \
        narzin-main/src/components/New/navbar/__tests__/SupportMenu.test.jsx
git commit -m "feat(navbar): SupportMenu popup with WhatsApp chat"
```

---

## Task 10: AccountMenu component

**Files:**
- Create: `narzin-main/src/components/New/navbar/AccountMenu.jsx`
- Test: `narzin-main/src/components/New/navbar/__tests__/AccountMenu.test.jsx`

**Interfaces:**
- Consumes: `state.auth.isAuthenticated`; `logout` from `../../../Store/slices/Auth/AuthSlice`; react-router `Link`.
- Produces: default export `<AccountMenu />`. Logged out → Sign in / Register buttons + Recently Viewed link. Logged in → links My Orders (`/my-account?tab=orders`), Recently Viewed (`/recently-viewed`), Wishlist (`/my-account?tab=wishlist`), My Wallet (`/my-account?tab=wallet`), Addresses (`/my-account?tab=addresses`), My Account (`/my-account`), and Logout.

- [ ] **Step 1: Write the failing test**

Create `narzin-main/src/components/New/navbar/__tests__/AccountMenu.test.jsx`:

```jsx
import { describe, it, expect } from "vitest";
import { screen, fireEvent } from "@testing-library/react";
import { renderWithProviders } from "../../../../test/renderWithProviders";
import authReducer from "../../../../Store/slices/Auth/AuthSlice";
import AccountMenu from "../AccountMenu";

describe("AccountMenu", () => {
  it("shows sign in / register when logged out", () => {
    renderWithProviders(<AccountMenu />, {
      reducers: { auth: authReducer },
      preloadedState: { auth: { isAuthenticated: false } },
    });
    fireEvent.click(screen.getByRole("button", { name: /account/i }));
    expect(screen.getByRole("link", { name: /sign in/i })).toBeInTheDocument();
    expect(screen.getByRole("link", { name: /register/i })).toBeInTheDocument();
  });

  it("shows orders and recently viewed when logged in", () => {
    renderWithProviders(<AccountMenu />, {
      reducers: { auth: authReducer },
      preloadedState: { auth: { isAuthenticated: true } },
    });
    fireEvent.click(screen.getByRole("button", { name: /account/i }));
    const orders = screen.getByRole("link", { name: /orders/i });
    expect(orders).toHaveAttribute("href", "/my-account?tab=orders");
    expect(screen.getByRole("link", { name: /recently viewed/i })).toHaveAttribute("href", "/recently-viewed");
  });
});
```

> Uses the real `auth` reducer so `preloadedState.auth` survives; `AccountMenu` only reads `state.auth.isAuthenticated` on render (logout dispatches only on click).

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzin-main && npm test -- AccountMenu`
Expected: FAIL — cannot resolve `../AccountMenu`.

- [ ] **Step 3: Implement the component**

Create `narzin-main/src/components/New/navbar/AccountMenu.jsx`:

```jsx
import { Menu } from "@headlessui/react";
import { User, Package, Clock, Heart, Wallet, MapPin, LogOut } from "lucide-react";
import { Link, useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { useTranslation } from "react-i18next";
import { logout } from "../../../Store/slices/Auth/AuthSlice";

const AccountMenu = () => {
  const { t } = useTranslation();
  const isAuthenticated = useSelector((state) => state.auth.isAuthenticated);
  const dispatch = useDispatch();
  const navigate = useNavigate();

  const handleLogout = () => {
    dispatch(logout());
    setTimeout(() => navigate("/signin"), 300);
  };

  const item = (to, Icon, label) => (
    <Menu.Item>
      {({ active }) => (
        <Link
          to={to}
          className={`flex items-center gap-3 rounded-lg px-3 py-2 text-sm ${
            active ? "bg-blue-50 text-blue-700" : "text-gray-700"
          }`}
        >
          <Icon className="w-4 h-4" />
          {label}
        </Link>
      )}
    </Menu.Item>
  );

  return (
    <Menu as="div" className="relative">
      <Menu.Button
        aria-label={t("topbar.account", "Account")}
        className="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
      >
        <User className="w-5 h-5" />
      </Menu.Button>
      <Menu.Items className="absolute end-0 mt-2 w-60 origin-top-end rounded-xl bg-white shadow-lg ring-1 ring-gray-200/70 focus:outline-none z-50 p-1.5">
        {!isAuthenticated && (
          <div className="flex gap-2 p-1.5">
            <Link
              to="/signin"
              className="flex-1 text-center text-sm font-medium text-gray-700 border border-gray-200 rounded-lg py-2 hover:bg-gray-50"
            >
              {t("auth.login", "Sign In")}
            </Link>
            <Link
              to="/signup"
              className="flex-1 text-center text-sm font-medium text-white bg-blue-600 rounded-lg py-2 hover:bg-blue-700"
            >
              {t("auth.register", "Register")}
            </Link>
          </div>
        )}

        <div className="mt-1">
          {isAuthenticated && item("/my-account?tab=orders", Package, t("topbar.my_orders", "My Orders"))}
          {item("/recently-viewed", Clock, t("topbar.recently_viewed", "Recently Viewed"))}
          {isAuthenticated && item("/my-account?tab=wishlist", Heart, t("topbar.wishlist", "Wishlist"))}
          {isAuthenticated && item("/my-account?tab=wallet", Wallet, t("topbar.wallet", "My Wallet"))}
          {isAuthenticated && item("/my-account?tab=addresses", MapPin, t("topbar.addresses", "Addresses"))}
          {isAuthenticated && item("/my-account", User, t("topbar.my_account", "My Account"))}
        </div>

        {isAuthenticated && (
          <div className="mt-1 border-t border-gray-100 pt-1">
            <Menu.Item>
              {({ active }) => (
                <button
                  onClick={handleLogout}
                  className={`flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-red-600 ${
                    active ? "bg-red-50" : ""
                  }`}
                >
                  <LogOut className="w-4 h-4" />
                  {t("auth.logout", "Logout")}
                </button>
              )}
            </Menu.Item>
          </div>
        )}
      </Menu.Items>
    </Menu>
  );
};

export default AccountMenu;
```

- [ ] **Step 4: Run test to verify it passes**

Run: `cd narzin-main && npm test -- AccountMenu`
Expected: PASS (2 tests). If the auth slice requires more than `isAuthenticated` in preloaded state, extend the preloaded `auth` object to match `AuthSlice` initial state.

- [ ] **Step 5: Commit**

```bash
git add narzin-main/src/components/New/navbar/AccountMenu.jsx \
        narzin-main/src/components/New/navbar/__tests__/AccountMenu.test.jsx
git commit -m "feat(navbar): AccountMenu dropdown (existing features only)"
```

---

## Task 11: Wire the icon cluster into NavBar (desktop + mobile)

**Files:**
- Modify: `narzin-main/src/components/New/NavBar.jsx`
- Test: `narzin-main/src/components/New/navbar/__tests__/NavBar.smoke.test.jsx`

**Interfaces:**
- Consumes: `AccountMenu`, `SupportMenu`, `LanguageMenu` (Tasks 8-10); keeps the existing search form and cart link.

- [ ] **Step 1: Write the failing smoke test**

Create `narzin-main/src/components/New/navbar/__tests__/NavBar.smoke.test.jsx`:

```jsx
import { describe, it, expect } from "vitest";
import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../../../test/renderWithProviders";
import authReducer from "../../../../Store/slices/Auth/AuthSlice";
import cartReducer from "../../../../Store/slices/CardSlice";
import settingsReducer from "../../../../Store/slices/SettingsSlice";
import homeReducer from "../../../../Store/slices/HomeSlice";
import NavBar from "../../NavBar";

describe("NavBar icon cluster", () => {
  it("renders account, cart and language icons", () => {
    renderWithProviders(<NavBar data={[]} />, {
      reducers: {
        auth: authReducer,
        cart: cartReducer,
        settings: settingsReducer,
        home: homeReducer,
      },
      preloadedState: {
        auth: { isAuthenticated: false },
        cart: { items: [], totalItems: 0 },
        settings: { whatsapp_number: null, support_hours: null, status: "succeeded" },
        home: { blocks: [], status: "succeeded", error: null },
      },
    });
    expect(screen.getByRole("button", { name: /account/i })).toBeInTheDocument();
    expect(screen.getByRole("button", { name: /language/i })).toBeInTheDocument();
    // Cart is a link to /cart
    expect(screen.getAllByRole("link").some((a) => a.getAttribute("href") === "/cart")).toBe(true);
  });
});
```

> `NavBar` renders `AnnouncementBar`, which selects `state.home` — hence the `home` reducer + empty `blocks` (so the bar renders nothing). Support icon is absent here because no number is set; that's expected.

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzin-main && npm test -- NavBar.smoke`
Expected: FAIL — no "account" button (old inline auth links still in place).

- [ ] **Step 3: Refactor the desktop actions block**

In `narzin-main/src/components/New/NavBar.jsx`:

Add imports near the top:

```jsx
import AccountMenu from "./navbar/AccountMenu";
import SupportMenu from "./navbar/SupportMenu";
import LanguageMenu from "./navbar/LanguageMenu";
```

Replace the **"Desktop Auth & Actions"** block (the `<div className="hidden lg:flex items-center space-x-3 flex-shrink-0">` … `</div>` that currently holds the search form, inline language button, auth links, and cart) with:

```jsx
            {/* Desktop Actions */}
            <div className="hidden lg:flex items-center space-x-2 flex-shrink-0">
              {/* Search bar (unchanged) */}
              <form onSubmit={handleSearch} className="relative">
                <input
                  type="text"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  placeholder={t("shop.search_placeholder", isRTL ? "ابحث عن المنتجات" : "Produkte suchen")}
                  aria-label={t("shop.search", isRTL ? "بحث" : "Suche")}
                  className="w-40 xl:w-52 rounded-full bg-nz-surface/70 border border-nz-border py-2 ps-4 pe-9 text-sm text-nz-ink placeholder:text-nz-muted focus:outline-none focus:ring-2 focus:ring-narzin-gold/60 focus:border-narzin-gold transition-all duration-200"
                />
                <button
                  type="submit"
                  aria-label={t("shop.search", isRTL ? "بحث" : "Suche")}
                  className="absolute inset-y-0 end-1.5 flex items-center justify-center w-7 text-nz-muted hover:text-narzin-gold transition-colors"
                >
                  <Search className="w-4 h-4" />
                </button>
              </form>

              {/* Icon cluster (SHEIN-style) */}
              <AccountMenu />
              <SupportMenu />
              <Link
                to="/cart"
                aria-label="Cart"
                className="relative p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 group"
              >
                <ShoppingCart className="w-5 h-5 group-hover:scale-110 transition-transform duration-200" />
                {isAuthenticated && totalItems > 0 && (
                  <span className="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center shadow-lg">
                    {totalItems > 99 ? "99+" : totalItems}
                  </span>
                )}
              </Link>
              <LanguageMenu />
            </div>
```

Leave the mobile actions, mobile search, mega-menu, and the `handleLogout`/`changeLanguage` functions in place for now (mobile menu is updated in Step 4). The desktop-only inline `changeLanguage` toggle and auth links are now gone; `changeLanguage`/`handleLogout` are still used by the mobile menu, so keep them.

- [ ] **Step 4: Update the mobile menu account section**

In the mobile menu, in the logged-in branch (the `<div className="space-y-2">` after `{t("auth.my_account")}`), add Orders + Recently Viewed links so mobile mirrors desktop. Immediately after the existing "My Account" `<Link>` (the one to `/my-account`), insert:

```jsx
                  <Link
                    to="/my-account?tab=orders"
                    onClick={() => setIsMenuOpen(false)}
                    className="flex items-center space-x-3 w-full text-left py-3 px-4 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
                  >
                    <User className="w-5 h-5" />
                    <span className="font-medium">{t("topbar.my_orders", "My Orders")}</span>
                  </Link>
                  <Link
                    to="/recently-viewed"
                    onClick={() => setIsMenuOpen(false)}
                    className="flex items-center space-x-3 w-full text-left py-3 px-4 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
                  >
                    <User className="w-5 h-5" />
                    <span className="font-medium">{t("topbar.recently_viewed", "Recently Viewed")}</span>
                  </Link>
```

Also add a Recently Viewed link into the logged-out mobile branch (after the Register button) so guests can reach it too:

```jsx
                  <Link
                    to="/recently-viewed"
                    onClick={() => setIsMenuOpen(false)}
                    className="flex items-center space-x-3 w-full text-left py-3 px-4 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
                  >
                    <User className="w-5 h-5" />
                    <span className="font-medium">{t("topbar.recently_viewed", "Recently Viewed")}</span>
                  </Link>
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `cd narzin-main && npm test -- NavBar.smoke`
Expected: PASS. Then run the full suite: `npm test` — all green.

- [ ] **Step 6: Commit**

```bash
git add narzin-main/src/components/New/NavBar.jsx \
        narzin-main/src/components/New/navbar/__tests__/NavBar.smoke.test.jsx
git commit -m "feat(navbar): SHEIN-style icon cluster (account/support/cart/globe)"
```

---

## Task 12: Deep-linkable account tabs

**Files:**
- Modify: `narzin-main/src/pages/MyAccountLayout.jsx`
- Test: `narzin-main/src/pages/__tests__/MyAccountLayout.tab.test.jsx`

**Interfaces:**
- Produces: exported pure helper `getInitialTab(param: string | null): string` in `MyAccountLayout.jsx` — returns `param` if it is one of the valid tab ids, else `"my-account"`. Component uses `useSearchParams` + this helper to seed `activeTab`.

Rationale: `MyAccountLayout` mounts heavy data-fetching tab children, so we test the tab-resolution logic as a pure function rather than rendering the whole layout.

- [ ] **Step 1: Write the failing test**

Create `narzin-main/src/pages/__tests__/MyAccountLayout.tab.test.js`:

```js
import { describe, it, expect } from "vitest";
import { getInitialTab } from "../MyAccountLayout";

describe("getInitialTab", () => {
  it("returns the requested tab when valid", () => {
    expect(getInitialTab("orders")).toBe("orders");
    expect(getInitialTab("wallet")).toBe("wallet");
  });

  it("falls back to my-account for unknown or missing tabs", () => {
    expect(getInitialTab("bogus")).toBe("my-account");
    expect(getInitialTab(null)).toBe("my-account");
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzin-main && npm test -- MyAccountLayout.tab`
Expected: FAIL — `getInitialTab` is not exported.

- [ ] **Step 3: Add the helper and use the query param**

In `narzin-main/src/pages/MyAccountLayout.jsx`, add `useSearchParams` to the router import:

```jsx
import { useNavigate, useSearchParams } from "react-router-dom";
```

Add the exported helper near the top of the file (module scope, above the component):

```jsx
export const ACCOUNT_TABS = ["my-account", "orders", "addresses", "wishlist", "wallet", "about", "vendor"];

export function getInitialTab(param) {
  return ACCOUNT_TABS.includes(param) ? param : "my-account";
}
```

Then replace the `activeTab` initializer inside the component:

```jsx
  const [searchParams] = useSearchParams();
  const [activeTab, setActiveTab] = useState(getInitialTab(searchParams.get("tab")));
```

(Keep the rest of the component as-is; internal tab clicks still call `setActiveTab`.)

- [ ] **Step 4: Run test to verify it passes**

Run: `cd narzin-main && npm test -- MyAccountLayout.tab`
Expected: PASS (2 assertions).

- [ ] **Step 5: Commit**

```bash
git add narzin-main/src/pages/MyAccountLayout.jsx \
        narzin-main/src/pages/__tests__/MyAccountLayout.tab.test.js
git commit -m "feat(account): deep-link account tabs via ?tab="
```

---

## Task 13: Recently Viewed page + route

**Files:**
- Create: `narzin-main/src/pages/RecentlyViewed.jsx`
- Modify: `narzin-main/src/App.jsx`
- Test: `narzin-main/src/pages/__tests__/RecentlyViewed.test.jsx`

**Interfaces:**
- Consumes: `fetchForYou`, `selectForYouBlocks` from `../Store/slices/ForYouSlice`; picks the block where `content.key === "recently_viewed"` (Task 4); renders products with `RailProductCard`.

- [ ] **Step 1: Write the failing test**

Create `narzin-main/src/pages/__tests__/RecentlyViewed.test.jsx`:

```jsx
import { describe, it, expect } from "vitest";
import { screen } from "@testing-library/react";
import { renderWithProviders } from "../../test/renderWithProviders";
import RecentlyViewed from "../RecentlyViewed";

// Identity reducer: RecentlyViewed dispatches fetchForYou on mount, which would
// (with the real reducer + no test server) reject and wipe `blocks`. An identity
// reducer keeps the preloaded state authoritative so the render is deterministic.
const forYouIdentity = (state = { blocks: [], status: "idle" }) => state;

describe("RecentlyViewed", () => {
  it("renders products from the recently_viewed rail", () => {
    renderWithProviders(<RecentlyViewed />, {
      route: "/recently-viewed",
      reducers: { forYou: forYouIdentity },
      preloadedState: {
        forYou: {
          status: "succeeded",
          blocks: [
            {
              id: 2000,
              type: "product_rail",
              content: {
                key: "recently_viewed",
                title: "Recently Viewed",
                products: [
                  { id: 7, name_german: "Testschuh", name_arabic: "حذاء", min_price: 42, image: null },
                ],
              },
            },
          ],
        },
      },
    });
    expect(screen.getByText("Testschuh")).toBeInTheDocument();
  });

  it("shows an empty state when there is no recently_viewed rail", () => {
    renderWithProviders(<RecentlyViewed />, {
      route: "/recently-viewed",
      reducers: { forYou: forYouIdentity },
      preloadedState: { forYou: { status: "succeeded", blocks: [] } },
    });
    expect(screen.getByText(/haven't viewed|no recently viewed|nothing/i)).toBeInTheDocument();
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzin-main && npm test -- RecentlyViewed`
Expected: FAIL — cannot resolve `../RecentlyViewed`.

- [ ] **Step 3: Implement the page**

Create `narzin-main/src/pages/RecentlyViewed.jsx`:

```jsx
import { useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import { useTranslation } from "react-i18next";
import { Link } from "react-router-dom";
import { fetchForYou, selectForYouBlocks } from "../Store/slices/ForYouSlice";
import RailProductCard from "../components/pages/home/blocks/RailProductCard";

const RecentlyViewed = () => {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const blocks = useSelector(selectForYouBlocks);

  useEffect(() => {
    dispatch(fetchForYou(i18n.language));
  }, [dispatch, i18n.language]);

  const rail = blocks.find((b) => b?.content?.key === "recently_viewed");
  const products = rail?.content?.products || [];

  return (
    <div className="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 pt-24 pb-12">
      <h1 className="text-xl sm:text-2xl font-bold text-narzin-navy mb-6">
        {t("topbar.recently_viewed", "Recently Viewed")}
      </h1>

      {products.length > 0 ? (
        <div className="flex flex-wrap gap-3 sm:gap-4">
          {products.map((product) => (
            <RailProductCard key={product.id} product={product} />
          ))}
        </div>
      ) : (
        <div className="text-center py-16">
          <p className="text-gray-500 mb-4">
            {t("topbar.recently_viewed_empty", "You haven't viewed any products yet.")}
          </p>
          <Link to="/store" className="text-blue-600 font-medium hover:underline">
            {t("topbar.browse_store", "Browse the store")}
          </Link>
        </div>
      )}
    </div>
  );
};

export default RecentlyViewed;
```

- [ ] **Step 4: Register the route**

In `narzin-main/src/App.jsx`, import and add the route inside the layout `<Route path="/">`:

```jsx
import RecentlyViewed from "./pages/RecentlyViewed";
```

```jsx
            <Route path="recently-viewed" element={<RecentlyViewed />} />
```

- [ ] **Step 5: Run test to verify it passes**

Run: `cd narzin-main && npm test -- RecentlyViewed`
Expected: PASS (2 tests).

- [ ] **Step 6: Commit**

```bash
git add narzin-main/src/pages/RecentlyViewed.jsx \
        narzin-main/src/pages/__tests__/RecentlyViewed.test.jsx \
        narzin-main/src/App.jsx
git commit -m "feat(storefront): Recently Viewed page"
```

---

## Task 14: i18n keys (ar + du)

**Files:**
- Modify: `narzin-main/public/locales/ar/translation.json`
- Modify: `narzin-main/public/locales/du/translation.json`

**Interfaces:**
- Produces: `topbar.*` keys used by AccountMenu, SupportMenu, RecentlyViewed. (Components already pass English defaults to `t()`, so this task localizes them; no test.)

- [ ] **Step 1: Add keys to the German file**

In `narzin-main/public/locales/du/translation.json`, add a `topbar` object (merge into the existing root object; do not duplicate an existing top-level key):

```json
"topbar": {
  "account": "Konto",
  "support": "Support",
  "customer_support": "Kundenservice",
  "chat_whatsapp": "Per WhatsApp chatten",
  "my_orders": "Meine Bestellungen",
  "recently_viewed": "Kürzlich angesehen",
  "recently_viewed_empty": "Sie haben noch keine Produkte angesehen.",
  "browse_store": "Zum Shop",
  "wishlist": "Merkliste",
  "wallet": "Meine Brieftasche",
  "addresses": "Adressen",
  "my_account": "Mein Konto"
}
```

- [ ] **Step 2: Add keys to the Arabic file**

In `narzin-main/public/locales/ar/translation.json`, add:

```json
"topbar": {
  "account": "الحساب",
  "support": "الدعم",
  "customer_support": "خدمة العملاء",
  "chat_whatsapp": "الدردشة عبر واتساب",
  "my_orders": "طلباتي",
  "recently_viewed": "شوهدت مؤخرًا",
  "recently_viewed_empty": "لم تشاهد أي منتجات بعد.",
  "browse_store": "تصفح المتجر",
  "wishlist": "قائمة الرغبات",
  "wallet": "محفظتي",
  "addresses": "العناوين",
  "my_account": "حسابي"
}
```

- [ ] **Step 3: Validate JSON**

Run: `cd narzin-main && node -e "require('./public/locales/ar/translation.json'); require('./public/locales/du/translation.json'); console.log('ok')"`
Expected: prints `ok` (no JSON parse error).

- [ ] **Step 4: Commit**

```bash
git add narzin-main/public/locales/ar/translation.json \
        narzin-main/public/locales/du/translation.json
git commit -m "i18n(topbar): ar + du strings for the new top bar"
```

---

## Final verification

- [ ] Run backend suite: `cd narzinapp-main && php artisan test` — all green.
- [ ] Run frontend suite: `cd narzin-main && npm test` — all green.
- [ ] Manual smoke (dev): open the storefront, confirm the four icons render; Account dropdown shows the right items logged out vs in; Support icon is hidden until an admin saves a WhatsApp number, then opens `wa.me/<digits>`; Globe switches ar⇄du and shows EUR (€); cart badge intact; RTL layout in Arabic; `/recently-viewed` populated vs empty; admin Settings page saves and round-trips.

# Homepage Content Blocks — Phase 1 (Backend + Admin Builder) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the server-driven homepage system: a `home_blocks` table + resolved `GET /api/v1/home` endpoint + drag-and-drop Blade admin "Homepage Builder", with legacy `/v1/banners/*` and `/v1/before-nav/current` endpoints kept alive on top of the new data.

**Architecture:** New nwidart module `HomeContent` inside the Laravel app (`narzinapp-main/`). Admins CRUD typed blocks; `HomeFeedService` resolves visible blocks per platform+locale (text collapse, image URLs, product-rule resolution) behind a 5-minute cache flushed on every admin write. Old public banner endpoints are rewritten to read from the new blocks so already-installed clients keep working.

**Tech Stack:** Laravel modular monolith (nwidart/laravel-modules), PHPUnit with sqlite `:memory:` + `RefreshDatabase`, Blade + Tailwind (Vite `resources/css/app.css`), Alpine.js (already in the admin layout), SortableJS via CDN, existing `admin.auth` middleware (`AdminMiddleware`, alias in `bootstrap/app.php`).

**Spec:** `docs/superpowers/specs/2026-07-02-homepage-blocks-design.md` (see the 2026-07-02 amendment: rail rule `biggest_discount` is deferred).

## Global Constraints

- All work happens under `narzinapp-main/`; run every command from `C:\xampp\htdocs\Narzin\narzinapp-main`.
- Block types (8, exact strings): `announcement_bar`, `popup`, `hero_slider`, `category_grid`, `product_rail`, `countdown_banner`, `info_strip`, `promo_tiles`.
- Product-rail rules (4, exact strings): `newest`, `best_sellers`, `category`, `manual`. (`biggest_discount` deferred per spec amendment.)
- Locales: `ar`, `de`, `en`. Normalize `du` → `de`; unknown → `ar`. Text fallback order: requested locale, then `ar`, `de`, `en` (first non-empty wins).
- i18n text fields are stored as `{"ar": "...", "de": "...", "en": "..."}`; links as `{"type": "category"|"product"|"url"|"none", "value": ...}`.
- Uploaded images: `$file->store('homeBlocks', 'public')`; public URLs built as `config('app.url') . '/storage/' . $path`.
- Public API envelope (house style): `{"status": true, "data": ...}`.
- Cache key `home:v1:{platform}:{locale}`, TTL 300 s, flushed on every HomeBlock save/delete and on reorder.
- Legacy response shapes must not change: `/api/v1/banners/mobile`, `/api/v1/banners/web` (`{"status": true, "data": [{id, image, title, description, is_mobile}]}`), `/api/v1/before-nav/current` (`{"success": true, "message": ..., "data": {text, ...}}`, 404 + `data: null` when none).
- Module namespaces follow the existing pattern: files in `Modules/HomeContent/app/Models/` → namespace `Modules\HomeContent\Models`, etc. Views render as `homecontent::...`.
- Tests: `php artisan test --filter=<ClassName>`. New feature tests live flat in `tests/Feature/`, unit tests in `tests/Unit/` (repo convention).
- Commits: conventional style (`feat(home): ...`), each ending with the trailer line `Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>`.

## File Structure

**Created (module `narzinapp-main/Modules/HomeContent/`):**
- `database/migrations/2026_07_02_000001_create_home_blocks_table.php` — schema
- `app/Models/HomeBlock.php` — model, `TYPES`, `visible()` scope, cache-flush events
- `app/Support/Locale.php`, `app/Support/Translatable.php`, `app/Support/Link.php`, `app/Support/ImageUrl.php` — pure helpers
- `app/Support/BlockContentRules.php` — per-type validation rules
- `app/Services/ProductRailResolver.php` — rule → product cards
- `app/Services/HomeFeedService.php` — block list → resolved feed + cache
- `app/Http/Controllers/HomeController.php` — public API
- `app/Http/Controllers/HomeBlockAdminController.php` — admin CRUD/reorder/toggle/search/preview
- `app/Console/MigrateLegacyHomeContent.php` — `home:migrate-legacy`
- `routes/api.php`, `routes/web.php`, `config/config.php`
- `resources/views/index.blade.php`, `resources/views/form.blade.php`
- `resources/views/partials/shared.blade.php`, `partials/i18n-input.blade.php`, `partials/link-picker.blade.php`
- `resources/views/forms/{announcement_bar,popup,hero_slider,category_grid,product_rail,countdown_banner,info_strip,promo_tiles}.blade.php`

**Created (tests, `narzinapp-main/tests/`):**
- `Unit/HomeContentSupportTest.php`, `Unit/BlockContentRulesTest.php`
- `Feature/HomeBlockModelTest.php`, `Feature/ProductRailResolverTest.php`, `Feature/HomeFeedServiceTest.php`, `Feature/HomeFeedApiTest.php`, `Feature/LegacyBannerEndpointsTest.php`, `Feature/MigrateLegacyHomeContentTest.php`, `Feature/HomeBlockAdminTest.php`, `Feature/HomeBlockAdminUiTest.php`

**Modified:**
- `Modules/Banners/app/Http/Controllers/BannerController.php`, `.../BeforeNavController.php` — re-point to blocks
- `Modules/Admin/routes/web.php` — legacy admin routes become redirects
- `resources/views/components/admin/sidebar.blade.php` — Banners nav item → Homepage
- `modules_statuses.json`, `.env.example` — module registration, `HOME_PREVIEW_TOKEN`, `STOREFRONT_URL`

---

### Task 1: HomeContent module, `home_blocks` migration, `HomeBlock` model

**Files:**
- Create: `Modules/HomeContent/` (scaffold), `Modules/HomeContent/database/migrations/2026_07_02_000001_create_home_blocks_table.php`, `Modules/HomeContent/app/Models/HomeBlock.php`
- Test: `tests/Feature/HomeBlockModelTest.php`

**Interfaces:**
- Produces: `Modules\HomeContent\Models\HomeBlock` with `public const TYPES` (8 strings above), `public const PLATFORMS = ['web','app','both']`, fillable `type,name,sort_order,is_active,platform,starts_at,ends_at,content`, casts (`content` array, `is_active` bool, `starts_at`/`ends_at` datetime), and scope `visible(string $platform)` returning active blocks whose platform is `both` or `$platform`, inside their schedule window, ordered by `sort_order`.

- [ ] **Step 1: Scaffold the module**

Run: `php artisan module:make HomeContent`
Expected: "Module [HomeContent] created successfully" and `Modules/HomeContent/` exists with `app/`, `routes/`, `config/`, `resources/`, providers. Verify `modules_statuses.json` now contains `"HomeContent": true` (add it manually if the generator didn't).

Delete generator noise we won't use: `Modules/HomeContent/app/Http/Controllers/HomeContentController.php`, `Modules/HomeContent/database/seeders/` content can stay as-is. Remove any generated resource route lines from `Modules/HomeContent/routes/web.php` and `routes/api.php`, leaving empty `<?php use Illuminate\Support\Facades\Route;` files for now.

- [ ] **Step 2: Write the failing test**

Create `tests/Feature/HomeBlockModelTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HomeContent\Models\HomeBlock;
use Tests\TestCase;

class HomeBlockModelTest extends TestCase
{
    use RefreshDatabase;

    private function block(array $overrides = []): HomeBlock
    {
        return HomeBlock::create(array_merge([
            'type' => 'announcement_bar',
            'name' => 'Test block',
            'sort_order' => 0,
            'is_active' => true,
            'platform' => 'both',
            'content' => ['text' => ['ar' => 'مرحبا']],
        ], $overrides));
    }

    public function test_content_is_cast_to_array(): void
    {
        $block = $this->block();
        $this->assertSame('مرحبا', HomeBlock::find($block->id)->content['text']['ar']);
    }

    public function test_visible_filters_inactive_blocks(): void
    {
        $this->block(['name' => 'on']);
        $this->block(['name' => 'off', 'is_active' => false]);

        $this->assertSame(['on'], HomeBlock::visible('web')->pluck('name')->all());
    }

    public function test_visible_filters_by_platform(): void
    {
        $this->block(['name' => 'both', 'platform' => 'both']);
        $this->block(['name' => 'web-only', 'platform' => 'web']);
        $this->block(['name' => 'app-only', 'platform' => 'app']);

        $this->assertSame(['both', 'app-only'], HomeBlock::visible('app')->pluck('name')->all());
    }

    public function test_visible_respects_schedule_window(): void
    {
        $this->block(['name' => 'current', 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
        $this->block(['name' => 'future', 'starts_at' => now()->addDay()]);
        $this->block(['name' => 'past', 'ends_at' => now()->subDay()]);
        $this->block(['name' => 'open-ended']);

        $this->assertSame(['current', 'open-ended'], HomeBlock::visible('web')->pluck('name')->all());
    }

    public function test_visible_orders_by_sort_order(): void
    {
        $this->block(['name' => 'second', 'sort_order' => 5]);
        $this->block(['name' => 'first', 'sort_order' => 1]);

        $this->assertSame(['first', 'second'], HomeBlock::visible('web')->pluck('name')->all());
    }
}
```

- [ ] **Step 3: Run test to verify it fails**

Run: `php artisan test --filter=HomeBlockModelTest`
Expected: FAIL — class `Modules\HomeContent\Models\HomeBlock` not found (or table missing).

- [ ] **Step 4: Write the migration**

Create `Modules/HomeContent/database/migrations/2026_07_02_000001_create_home_blocks_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40);
            $table->string('name', 100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(false);
            $table->string('platform', 10)->default('both');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->json('content')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'platform', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_blocks');
    }
};
```

- [ ] **Step 5: Write the model**

Create `Modules/HomeContent/app/Models/HomeBlock.php`:

```php
<?php

namespace Modules\HomeContent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HomeBlock extends Model
{
    public const TYPES = [
        'announcement_bar',
        'popup',
        'hero_slider',
        'category_grid',
        'product_rail',
        'countdown_banner',
        'info_strip',
        'promo_tiles',
    ];

    public const PLATFORMS = ['web', 'app', 'both'];

    protected $fillable = [
        'type', 'name', 'sort_order', 'is_active', 'platform', 'starts_at', 'ends_at', 'content',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeVisible(Builder $query, string $platform): Builder
    {
        $now = now();

        return $query->where('is_active', true)
            ->whereIn('platform', ['both', $platform])
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('sort_order');
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=HomeBlockModelTest`
Expected: PASS (5 tests). If migrations from the module don't run, confirm `HomeContentServiceProvider::boot()` calls `$this->loadMigrationsFrom(module_path($this->name, 'database/migrations'))` (the generator includes it).

- [ ] **Step 7: Commit**

```bash
git add Modules/HomeContent modules_statuses.json tests/Feature/HomeBlockModelTest.php
git commit -m "feat(home): HomeContent module with home_blocks table and visibility scope"
```

---

### Task 2: Support helpers — Locale, Translatable, Link, ImageUrl

**Files:**
- Create: `Modules/HomeContent/app/Support/Locale.php`, `Translatable.php`, `Link.php`, `ImageUrl.php`
- Test: `tests/Unit/HomeContentSupportTest.php`

**Interfaces:**
- Produces:
  - `Locale::normalize(?string $code): string` — lowercases, maps `du`→`de`, returns `ar` for unknown; `Locale::SUPPORTED = ['ar','de','en']`.
  - `Translatable::resolve(mixed $value, string $locale): ?string` — string passthrough; array → requested locale then ar/de/en fallback; null/empty → null.
  - `Link::resolve(mixed $link): ?array` — returns `['type' => ..., 'value' => ...]` for valid category/product (int value) or url (valid URL); anything else → null.
  - `ImageUrl::make(?string $path): ?string` — null-safe; passes through `http(s)://` values; otherwise prefixes `config('app.url').'/storage/'`.

- [ ] **Step 1: Write the failing tests**

Create `tests/Unit/HomeContentSupportTest.php`:

```php
<?php

namespace Tests\Unit;

use Modules\HomeContent\Support\ImageUrl;
use Modules\HomeContent\Support\Link;
use Modules\HomeContent\Support\Locale;
use Modules\HomeContent\Support\Translatable;
use Tests\TestCase;

class HomeContentSupportTest extends TestCase
{
    public function test_locale_normalization(): void
    {
        $this->assertSame('de', Locale::normalize('du'));
        $this->assertSame('de', Locale::normalize('DE'));
        $this->assertSame('en', Locale::normalize('en'));
        $this->assertSame('ar', Locale::normalize('fr'));
        $this->assertSame('ar', Locale::normalize(null));
    }

    public function test_translatable_picks_requested_locale(): void
    {
        $value = ['ar' => 'نص', 'de' => 'Text', 'en' => 'text'];
        $this->assertSame('Text', Translatable::resolve($value, 'de'));
    }

    public function test_translatable_falls_back_in_order(): void
    {
        $this->assertSame('نص', Translatable::resolve(['ar' => 'نص'], 'en'));
        $this->assertSame('Text', Translatable::resolve(['de' => 'Text'], 'en'));
        $this->assertNull(Translatable::resolve(['ar' => '', 'de' => ''], 'ar'));
        $this->assertNull(Translatable::resolve(null, 'ar'));
        $this->assertSame('plain', Translatable::resolve('plain', 'de'));
    }

    public function test_link_resolution(): void
    {
        $this->assertSame(['type' => 'category', 'value' => 7], Link::resolve(['type' => 'category', 'value' => '7']));
        $this->assertSame(['type' => 'url', 'value' => 'https://x.test/a'], Link::resolve(['type' => 'url', 'value' => 'https://x.test/a']));
        $this->assertNull(Link::resolve(['type' => 'none']));
        $this->assertNull(Link::resolve(['type' => 'url', 'value' => 'not a url']));
        $this->assertNull(Link::resolve(['type' => 'product', 'value' => 'abc']));
        $this->assertNull(Link::resolve('nope'));
    }

    public function test_image_url(): void
    {
        config(['app.url' => 'https://api.test']);
        $this->assertSame('https://api.test/storage/homeBlocks/a.jpg', ImageUrl::make('homeBlocks/a.jpg'));
        $this->assertSame('https://cdn.test/b.jpg', ImageUrl::make('https://cdn.test/b.jpg'));
        $this->assertNull(ImageUrl::make(null));
        $this->assertNull(ImageUrl::make(''));
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=HomeContentSupportTest`
Expected: FAIL — classes not found.

- [ ] **Step 3: Implement the helpers**

Create `Modules/HomeContent/app/Support/Locale.php`:

```php
<?php

namespace Modules\HomeContent\Support;

class Locale
{
    public const SUPPORTED = ['ar', 'de', 'en'];

    public static function normalize(?string $code): string
    {
        $code = strtolower((string) $code);
        if ($code === 'du') {
            $code = 'de';
        }

        return in_array($code, self::SUPPORTED, true) ? $code : 'ar';
    }
}
```

Create `Modules/HomeContent/app/Support/Translatable.php`:

```php
<?php

namespace Modules\HomeContent\Support;

class Translatable
{
    public static function resolve(mixed $value, string $locale): ?string
    {
        if (is_string($value)) {
            return $value === '' ? null : $value;
        }
        if (!is_array($value)) {
            return null;
        }
        foreach (array_unique(array_merge([$locale], Locale::SUPPORTED)) as $candidate) {
            if (!empty($value[$candidate]) && is_string($value[$candidate])) {
                return $value[$candidate];
            }
        }

        return null;
    }
}
```

Create `Modules/HomeContent/app/Support/Link.php`:

```php
<?php

namespace Modules\HomeContent\Support;

class Link
{
    public static function resolve(mixed $link): ?array
    {
        if (!is_array($link)) {
            return null;
        }
        $type = $link['type'] ?? 'none';
        $value = $link['value'] ?? null;

        if ($type === 'url') {
            return filter_var($value, FILTER_VALIDATE_URL) ? ['type' => 'url', 'value' => $value] : null;
        }
        if (in_array($type, ['category', 'product'], true)) {
            return is_numeric($value) ? ['type' => $type, 'value' => (int) $value] : null;
        }

        return null;
    }
}
```

Create `Modules/HomeContent/app/Support/ImageUrl.php`:

```php
<?php

namespace Modules\HomeContent\Support;

use Illuminate\Support\Str;

class ImageUrl
{
    public static function make(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return rtrim(config('app.url'), '/') . '/storage/' . ltrim($path, '/');
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter=HomeContentSupportTest`
Expected: PASS (5 tests).

- [ ] **Step 5: Commit**

```bash
git add Modules/HomeContent/app/Support tests/Unit/HomeContentSupportTest.php
git commit -m "feat(home): locale, translatable text, link and image-url helpers"
```

---

### Task 3: Per-type content validation — `BlockContentRules`

**Files:**
- Create: `Modules/HomeContent/app/Support/BlockContentRules.php`
- Test: `tests/Unit/BlockContentRulesTest.php`

**Interfaces:**
- Produces:
  - `BlockContentRules::for(string $type): array` — Laravel validation rules for the `content.*` input of that block type.
  - `BlockContentRules::files(string $type): array` — validation rules for the file inputs each admin form uses (`popup_image`, `countdown_image`, `slide_images_web.*`, `slide_images_app.*`, `tile_images.*`).
- Consumes: nothing (pure).

- [ ] **Step 1: Write the failing tests**

Create `tests/Unit/BlockContentRulesTest.php`:

```php
<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Validator;
use Modules\HomeContent\Support\BlockContentRules;
use Tests\TestCase;

class BlockContentRulesTest extends TestCase
{
    private function passes(string $type, array $content): bool
    {
        return !Validator::make(['content' => $content], BlockContentRules::for($type))->fails();
    }

    public function test_announcement_bar_requires_at_least_one_language(): void
    {
        $this->assertFalse($this->passes('announcement_bar', ['text' => ['ar' => '', 'de' => '', 'en' => '']]));
        $this->assertTrue($this->passes('announcement_bar', ['text' => ['ar' => 'مرحبا']]));
    }

    public function test_announcement_bar_validates_colors(): void
    {
        $this->assertFalse($this->passes('announcement_bar', ['text' => ['ar' => 'x'], 'bg_color' => 'red']));
        $this->assertTrue($this->passes('announcement_bar', ['text' => ['ar' => 'x'], 'bg_color' => '#141923']));
    }

    public function test_popup_requires_title_and_valid_frequency(): void
    {
        $this->assertFalse($this->passes('popup', ['frequency' => ['mode' => 'once_per_session']]));
        $this->assertFalse($this->passes('popup', ['title' => ['en' => 'Get the app'], 'frequency' => ['mode' => 'hourly']]));
        $this->assertTrue($this->passes('popup', ['title' => ['en' => 'Get the app'], 'frequency' => ['mode' => 'once_per_days', 'days' => 7], 'delay_seconds' => 3]));
    }

    public function test_hero_slider_requires_slides(): void
    {
        $this->assertFalse($this->passes('hero_slider', ['slides' => []]));
        $this->assertTrue($this->passes('hero_slider', ['slides' => [['title' => ['ar' => 'صيف']]]]));
    }

    public function test_product_rail_rules(): void
    {
        $this->assertFalse($this->passes('product_rail', ['title' => ['en' => 'Deals'], 'rule' => 'biggest_discount']));
        $this->assertFalse($this->passes('product_rail', ['title' => ['en' => 'Deals'], 'rule' => 'manual', 'product_ids' => []]));
        $this->assertTrue($this->passes('product_rail', ['title' => ['en' => 'New In'], 'rule' => 'newest', 'limit' => 12]));
    }

    public function test_info_strip_requires_two_to_four_items(): void
    {
        $this->assertFalse($this->passes('info_strip', ['items' => [['icon' => 'truck', 'text' => ['en' => 'Free shipping']]]]));
        $this->assertFalse($this->passes('info_strip', ['items' => [
            ['icon' => 'rocket', 'text' => ['en' => 'a']],
            ['icon' => 'truck', 'text' => ['en' => 'b']],
        ]]));
        $this->assertTrue($this->passes('info_strip', ['items' => [
            ['icon' => 'truck', 'text' => ['en' => 'Free shipping']],
            ['icon' => 'shield', 'text' => ['en' => 'Secure pay']],
        ]]));
    }

    public function test_category_grid_and_promo_tiles_and_countdown(): void
    {
        $this->assertFalse($this->passes('category_grid', ['category_ids' => [1]]));
        $this->assertFalse($this->passes('promo_tiles', ['tiles' => []]));
        $this->assertFalse($this->passes('countdown_banner', ['text' => ['en' => 'Sale'], 'ends_at_display' => '2001-01-01 00:00:00']));
        $this->assertTrue($this->passes('countdown_banner', ['text' => ['en' => 'Sale'], 'ends_at_display' => now()->addDay()->toDateTimeString()]));
    }
}
```

Note: `category_grid`/`product_rail` `exists:` checks are intentionally omitted from `BlockContentRules` so the class stays DB-free and unit-testable; referential integrity comes from the admin pickers only offering real records, and the resolvers tolerate missing IDs (they drop them).

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=BlockContentRulesTest`
Expected: FAIL — class not found.

- [ ] **Step 3: Implement**

Create `Modules/HomeContent/app/Support/BlockContentRules.php`:

```php
<?php

namespace Modules\HomeContent\Support;

use Closure;

class BlockContentRules
{
    public const ICONS = ['truck', 'shield', 'star', 'returns', 'support', 'tag'];

    public static function for(string $type): array
    {
        $color = ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'];

        return match ($type) {
            'announcement_bar' => self::i18n('content.text')
                + self::link('content.link')
                + ['content.bg_color' => $color, 'content.text_color' => $color],

            'popup' => self::i18n('content.title')
                + self::i18nOptional('content.text')
                + self::i18nOptional('content.button_label')
                + self::link('content.link')
                + [
                    'content.image' => ['nullable', 'string'],
                    'content.frequency.mode' => ['required', 'in:once_per_session,once_per_days'],
                    'content.frequency.days' => ['required_if:content.frequency.mode,once_per_days', 'nullable', 'integer', 'min:1', 'max:90'],
                    'content.delay_seconds' => ['nullable', 'integer', 'min:0', 'max:60'],
                ],

            'hero_slider' => [
                'content.slides' => ['required', 'array', 'min:1', 'max:8'],
                'content.slides.*.image_web' => ['nullable', 'string'],
                'content.slides.*.image_app' => ['nullable', 'string'],
                'content.slides.*.starts_at' => ['nullable', 'date'],
                'content.slides.*.ends_at' => ['nullable', 'date'],
            ]
                + self::i18nOptional('content.slides.*.title')
                + self::i18nOptional('content.slides.*.subtitle')
                + self::link('content.slides.*.link'),

            'category_grid' => [
                'content.category_ids' => ['required', 'array', 'min:2', 'max:20'],
                'content.category_ids.*' => ['integer'],
            ],

            'product_rail' => self::i18n('content.title') + [
                'content.rule' => ['required', 'in:newest,best_sellers,category,manual'],
                'content.category_id' => ['required_if:content.rule,category', 'nullable', 'integer'],
                'content.product_ids' => ['required_if:content.rule,manual', 'array', 'max:24'],
                'content.product_ids.*' => ['integer'],
                'content.limit' => ['nullable', 'integer', 'min:2', 'max:24'],
            ],

            'countdown_banner' => self::i18n('content.text')
                + self::link('content.link')
                + [
                    'content.ends_at_display' => ['required', 'date', 'after:now'],
                    'content.image' => ['nullable', 'string'],
                    'content.bg_color' => $color,
                    'content.text_color' => $color,
                ],

            'info_strip' => [
                'content.items' => ['required', 'array', 'min:2', 'max:4'],
                'content.items.*.icon' => ['required', 'in:' . implode(',', self::ICONS)],
            ]
                + self::i18n('content.items.*.text')
                + self::link('content.items.*.link'),

            'promo_tiles' => [
                'content.tiles' => ['required', 'array', 'min:1', 'max:3'],
                'content.tiles.*.image' => ['nullable', 'string'],
            ]
                + self::i18nOptional('content.tiles.*.label')
                + self::link('content.tiles.*.link'),

            default => [],
        };
    }

    public static function files(string $type): array
    {
        $image = ['nullable', 'image', 'max:4096'];

        return match ($type) {
            'popup' => ['popup_image' => $image],
            'countdown_banner' => ['countdown_image' => $image],
            'hero_slider' => ['slide_images_web.*' => $image, 'slide_images_app.*' => $image],
            'promo_tiles' => ['tile_images.*' => $image],
            default => [],
        };
    }

    private static function i18n(string $field): array
    {
        return [
            $field => ['required', 'array', self::atLeastOneLocale()],
        ] + self::localeStrings($field);
    }

    private static function i18nOptional(string $field): array
    {
        return [$field => ['nullable', 'array']] + self::localeStrings($field);
    }

    private static function localeStrings(string $field): array
    {
        $rules = [];
        foreach (Locale::SUPPORTED as $code) {
            $rules["$field.$code"] = ['nullable', 'string', 'max:500'];
        }

        return $rules;
    }

    private static function link(string $field): array
    {
        return [
            $field => ['nullable', 'array'],
            "$field.type" => ['nullable', 'in:none,category,product,url'],
            "$field.value" => ['nullable'],
        ];
    }

    private static function atLeastOneLocale(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            if (!is_array($value)) {
                $fail("The $attribute field must be an array.");

                return;
            }
            foreach (Locale::SUPPORTED as $code) {
                if (!empty($value[$code])) {
                    return;
                }
            }
            $fail("At least one language must be filled for $attribute.");
        };
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter=BlockContentRulesTest`
Expected: PASS (7 tests).

- [ ] **Step 5: Commit**

```bash
git add Modules/HomeContent/app/Support/BlockContentRules.php tests/Unit/BlockContentRulesTest.php
git commit -m "feat(home): per-type content validation rules"
```

---

### Task 4: `ProductRailResolver`

**Files:**
- Create: `Modules/HomeContent/app/Services/ProductRailResolver.php`
- Test: `tests/Feature/ProductRailResolverTest.php`

**Interfaces:**
- Consumes: `Modules\ProductManagement\Models\{Product, ProductVariant, Category}`, `Modules\Checkout\Models\OrderItem`, `Modules\Admin\Models\{PriceExchange, PlatformMarkup}` — all existing.
- Produces: `ProductRailResolver::resolve(array $content): array` — list of product cards `['id', 'name_arabic', 'name_german', 'slug_arabic', 'slug_german', 'image', 'min_price' (EUR after markup, 2 dp), 'min_price_iqd' (rounded int), 'min_price_variant_id']`. Products without a purchasable variant are excluded. `manual` preserves the admin's ID order. Pricing mirrors `ProductController@index`: cheapest active in-stock variant price divided by the latest `PriceExchange::price_rate` (default 1), then vendor `markup_percentage` (fallback `PlatformMarkup::getLatest()`, default 0).

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/ProductRailResolverTest.php`. With no `PriceExchange`/`PlatformMarkup` rows the rate is 1 and markup 0, so `min_price` equals the raw variant price — keep tests on that basis. The best-sellers test inserts orders with `PRAGMA foreign_keys = OFF` to avoid building the whole user/address graph:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\HomeContent\Services\ProductRailResolver;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;
use Tests\TestCase;

class ProductRailResolverTest extends TestCase
{
    use RefreshDatabase;

    private ProductRailResolver $resolver;
    private int $categoryId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new ProductRailResolver();
        $this->categoryId = Category::create([
            'name_arabic' => 'فساتين', 'name_german' => 'Kleider',
            'slug_arabic' => 'cat-ar-' . uniqid(), 'slug_german' => 'cat-de-' . uniqid(),
        ])->id;
    }

    private function product(string $name, float $price, array $overrides = []): Product
    {
        $product = Product::create(array_merge([
            'name_arabic' => $name, 'name_german' => $name,
            'slug_arabic' => 'p-ar-' . uniqid(), 'slug_german' => 'p-de-' . uniqid(),
            'category_id' => $this->categoryId, 'is_active' => true,
        ], $overrides));
        ProductVariant::create([
            'product_id' => $product->id, 'price' => $price, 'stock' => 10,
            'sku' => 'sku-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false,
        ]);

        return $product;
    }

    public function test_newest_returns_latest_first_with_prices(): void
    {
        $old = $this->product('old', 100);
        $old->created_at = now()->subDays(2);
        $old->save();
        $new = $this->product('new', 200);

        $cards = $this->resolver->resolve(['rule' => 'newest', 'limit' => 12]);

        $this->assertSame([$new->id, $old->id], array_column($cards, 'id'));
        $this->assertEquals(200.0, $cards[0]['min_price']);
        $this->assertEquals(200, $cards[0]['min_price_iqd']);
    }

    public function test_products_without_purchasable_variant_are_excluded(): void
    {
        $this->product('sellable', 50);
        $ghost = Product::create([
            'name_arabic' => 'ghost', 'name_german' => 'ghost',
            'slug_arabic' => 'g-ar-' . uniqid(), 'slug_german' => 'g-de-' . uniqid(),
            'category_id' => $this->categoryId, 'is_active' => true,
        ]);

        $cards = $this->resolver->resolve(['rule' => 'newest']);

        $this->assertNotContains($ghost->id, array_column($cards, 'id'));
        $this->assertCount(1, $cards);
    }

    public function test_inactive_products_are_excluded_and_limit_applies(): void
    {
        $this->product('hidden', 10, ['is_active' => false]);
        foreach (range(1, 4) as $i) {
            $this->product("p$i", 10 * $i);
        }

        $cards = $this->resolver->resolve(['rule' => 'newest', 'limit' => 3]);

        $this->assertCount(3, $cards);
        $this->assertNotContains('hidden', array_column($cards, 'name_german'));
    }

    public function test_manual_preserves_admin_order(): void
    {
        $a = $this->product('a', 10);
        $b = $this->product('b', 20);
        $c = $this->product('c', 30);

        $cards = $this->resolver->resolve(['rule' => 'manual', 'product_ids' => [$c->id, $a->id, $b->id]]);

        $this->assertSame([$c->id, $a->id, $b->id], array_column($cards, 'id'));
    }

    public function test_category_rule_matches_category_and_child(): void
    {
        $inCat = $this->product('in', 10);
        $otherCat = Category::create([
            'name_arabic' => 'أخرى', 'name_german' => 'Andere',
            'slug_arabic' => 'o-ar-' . uniqid(), 'slug_german' => 'o-de-' . uniqid(),
        ]);
        $this->product('out', 20, ['category_id' => $otherCat->id]);

        $cards = $this->resolver->resolve(['rule' => 'category', 'category_id' => $this->categoryId]);

        $this->assertSame([$inCat->id], array_column($cards, 'id'));
    }

    public function test_best_sellers_orders_by_units_sold(): void
    {
        $slow = $this->product('slow', 10);
        $hit = $this->product('hit', 20);

        DB::statement('PRAGMA foreign_keys = OFF');
        DB::table('orders')->insert([
            'id' => 1, 'user_id' => 1, 'address_id' => 1, 'order_number' => 'T-1',
            'total_amount' => 100, 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ([[$slow->id, 1], [$hit->id, 9]] as [$productId, $qty]) {
            DB::table('order_items')->insert([
                'order_id' => 1, 'product_id' => $productId, 'product_variant_id' => 1,
                'quantity' => $qty, 'unit_price' => 10, 'subtotal' => 10 * $qty,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        DB::statement('PRAGMA foreign_keys = ON');

        $cards = $this->resolver->resolve(['rule' => 'best_sellers']);

        $this->assertSame([$hit->id, $slow->id], array_column($cards, 'id'));
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=ProductRailResolverTest`
Expected: FAIL — `ProductRailResolver` not found.

- [ ] **Step 3: Implement**

Create `Modules/HomeContent/app/Services/ProductRailResolver.php`:

```php
<?php

namespace Modules\HomeContent\Services;

use Modules\Admin\Models\PlatformMarkup;
use Modules\Admin\Models\PriceExchange;
use Modules\Checkout\Models\OrderItem;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;

class ProductRailResolver
{
    public const RULES = ['newest', 'best_sellers', 'category', 'manual'];

    public function resolve(array $content): array
    {
        $rule = $content['rule'] ?? 'newest';
        $limit = min(max((int) ($content['limit'] ?? 12), 1), 24);

        $rate = (float) (PriceExchange::latest('created_at')->first()->price_rate ?? 1);
        $rate = $rate > 0 ? $rate : 1.0;
        $globalMarkup = (float) PlatformMarkup::getLatest();

        $query = Product::with(['images', 'vendor'])
            ->where('products.is_active', true)
            ->select('products.*')
            ->addSelect([
                'min_price' => ProductVariant::selectRaw('price / ?', [$rate])
                    ->whereColumn('product_id', 'products.id')
                    ->where('is_active', true)
                    ->where('is_out_of_stock', false)
                    ->orderBy('price')
                    ->limit(1),
                'min_price_variant_id' => ProductVariant::select('id')
                    ->whereColumn('product_id', 'products.id')
                    ->where('is_active', true)
                    ->where('is_out_of_stock', false)
                    ->orderBy('price')
                    ->limit(1),
            ]);

        switch ($rule) {
            case 'best_sellers':
                $query->addSelect([
                    'units_sold' => OrderItem::selectRaw('COALESCE(SUM(quantity), 0)')
                        ->whereColumn('order_items.product_id', 'products.id'),
                ])->orderByDesc('units_sold');
                break;

            case 'category':
                $categoryId = (int) ($content['category_id'] ?? 0);
                $query->where(fn ($q) => $q
                    ->where('category_id', $categoryId)
                    ->orWhere('child_category_id', $categoryId))
                    ->latest();
                break;

            case 'manual':
                $ids = array_map('intval', $content['product_ids'] ?? []);
                if ($ids === []) {
                    return [];
                }
                $query->whereIn('products.id', $ids);
                break;

            default:
                $query->latest();
        }

        $products = $query->limit($limit)->get();

        if ($rule === 'manual') {
            $ids = array_map('intval', $content['product_ids']);
            $products = $products->sortBy(fn ($p) => array_search($p->id, $ids, true))->values();
        }

        return $products
            ->map(function (Product $product) use ($globalMarkup, $rate) {
                if ($product->min_price === null) {
                    return null;
                }
                $vendor = $product->vendor;
                $markup = ($vendor && $vendor->markup_percentage !== null)
                    ? (float) $vendor->markup_percentage
                    : $globalMarkup;
                $eur = round(((float) $product->min_price) * (1 + $markup / 100), 2);

                return [
                    'id' => $product->id,
                    'name_arabic' => $product->name_arabic,
                    'name_german' => $product->name_german,
                    'slug_arabic' => $product->slug_arabic,
                    'slug_german' => $product->slug_german,
                    'image' => optional($product->images->first())->image,
                    'min_price' => $eur,
                    'min_price_iqd' => (int) round($eur * $rate),
                    'min_price_variant_id' => $product->min_price_variant_id,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter=ProductRailResolverTest`
Expected: PASS (6 tests).

- [ ] **Step 5: Commit**

```bash
git add Modules/HomeContent/app/Services/ProductRailResolver.php tests/Feature/ProductRailResolverTest.php
git commit -m "feat(home): product rail resolver with newest/best-sellers/category/manual rules"
```

---

### Task 5: `HomeFeedService` — resolution + caching

**Files:**
- Create: `Modules/HomeContent/app/Services/HomeFeedService.php`
- Modify: `Modules/HomeContent/app/Models/HomeBlock.php` (add cache-flush model events)
- Test: `tests/Feature/HomeFeedServiceTest.php`

**Interfaces:**
- Consumes: `HomeBlock::visible()`, `ProductRailResolver::resolve()`, `Translatable::resolve()`, `Link::resolve()`, `ImageUrl::make()`, `Locale::SUPPORTED`, `Modules\ProductManagement\Models\Category`.
- Produces:
  - `HomeFeedService::feed(string $platform, string $locale, bool $preview = false): array` — ordered list of `['id' => int, 'type' => string, 'content' => array]` (+ `'preview_upcoming' => true` on not-yet-started blocks in preview). Cached 300 s per platform+locale; preview bypasses the cache.
  - `HomeFeedService::flushCache(): void` — forgets all 6 platform×locale keys. Called from `HomeBlock::saved`/`deleted` events.
- Skip semantics (resolution returning `null` drops the block): announcement/countdown with no resolvable text; countdown past `ends_at_display`; hero with zero renderable slides; category grid with no existing categories; rail with zero products; info strip / promo tiles with zero valid items; any block whose resolution throws (logged as warning).

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/HomeFeedServiceTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\HomeContent\Models\HomeBlock;
use Modules\HomeContent\Services\HomeFeedService;
use Modules\ProductManagement\Models\Category;
use Tests\TestCase;

class HomeFeedServiceTest extends TestCase
{
    use RefreshDatabase;

    private HomeFeedService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(HomeFeedService::class);
        config(['app.url' => 'https://api.test']);
    }

    private function announcement(array $overrides = []): HomeBlock
    {
        return HomeBlock::create(array_merge([
            'type' => 'announcement_bar', 'name' => 'Bar', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 1,
            'content' => ['text' => ['ar' => 'حمل التطبيق', 'de' => 'Hol dir die App']],
        ], $overrides));
    }

    public function test_feed_resolves_locale_with_fallback(): void
    {
        $this->announcement();

        $de = $this->service->feed('web', 'de');
        $en = $this->service->feed('web', 'en');

        $this->assertSame('Hol dir die App', $de[0]['content']['text']);
        $this->assertSame('حمل التطبيق', $en[0]['content']['text']); // en missing → ar fallback
        $this->assertSame('#141923', $de[0]['content']['bg_color']);
    }

    public function test_hero_slider_picks_platform_image_and_absolute_url(): void
    {
        HomeBlock::create([
            'type' => 'hero_slider', 'name' => 'Hero', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 2,
            'content' => ['slides' => [
                ['image_web' => 'homeBlocks/web.jpg', 'image_app' => 'homeBlocks/app.jpg', 'title' => ['ar' => 'صيف']],
                ['image_web' => null, 'image_app' => null], // unrenderable → dropped
            ]],
        ]);

        $web = $this->service->feed('web', 'ar');
        Cache::flush();
        $app = $this->service->feed('app', 'ar');

        $this->assertCount(1, $web[0]['content']['slides']);
        $this->assertSame('https://api.test/storage/homeBlocks/web.jpg', $web[0]['content']['slides'][0]['image']);
        $this->assertSame('https://api.test/storage/homeBlocks/app.jpg', $app[0]['content']['slides'][0]['image']);
    }

    public function test_empty_and_expired_blocks_are_dropped(): void
    {
        HomeBlock::create([
            'type' => 'product_rail', 'name' => 'Empty rail', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 1,
            'content' => ['title' => ['en' => 'Deals'], 'rule' => 'manual', 'product_ids' => [999999]],
        ]);
        HomeBlock::create([
            'type' => 'countdown_banner', 'name' => 'Over', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 2,
            'content' => ['text' => ['en' => 'Sale'], 'ends_at_display' => now()->subHour()->toDateTimeString()],
        ]);
        HomeBlock::create([
            'type' => 'category_grid', 'name' => 'Ghost cats', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 3, 'content' => ['category_ids' => [999999]],
        ]);

        $this->assertSame([], $this->service->feed('web', 'en'));
    }

    public function test_malformed_content_is_skipped_not_fatal(): void
    {
        HomeBlock::create([
            'type' => 'hero_slider', 'name' => 'Broken', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 1, 'content' => ['slides' => 'not-an-array'],
        ]);
        $this->announcement(['sort_order' => 2]);

        $feed = $this->service->feed('web', 'ar');

        $this->assertCount(1, $feed);
        $this->assertSame('announcement_bar', $feed[0]['type']);
    }

    public function test_category_grid_resolves_names_and_order(): void
    {
        $a = Category::create(['name_arabic' => 'أ', 'name_german' => 'A', 'slug_arabic' => 'a-' . uniqid(), 'slug_german' => 'a2-' . uniqid()]);
        $b = Category::create(['name_arabic' => 'ب', 'name_german' => 'B', 'slug_arabic' => 'b-' . uniqid(), 'slug_german' => 'b2-' . uniqid()]);
        HomeBlock::create([
            'type' => 'category_grid', 'name' => 'Cats', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 1, 'content' => ['category_ids' => [$b->id, $a->id]],
        ]);

        $feed = $this->service->feed('web', 'de');

        $this->assertSame(['B', 'A'], array_column($feed[0]['content']['categories'], 'name'));
    }

    public function test_feed_is_cached_and_flushed_on_save(): void
    {
        $block = $this->announcement();
        $this->assertCount(1, $this->service->feed('web', 'ar'));

        $block->update(['is_active' => false]); // model event must flush the cache

        $this->assertSame([], $this->service->feed('web', 'ar'));
    }

    public function test_preview_includes_upcoming_blocks_flagged(): void
    {
        $this->announcement(['name' => 'live']);
        $this->announcement(['name' => 'soon', 'sort_order' => 2, 'starts_at' => now()->addDay()]);

        $normal = $this->service->feed('web', 'ar');
        $preview = $this->service->feed('web', 'ar', true);

        $this->assertCount(1, $normal);
        $this->assertCount(2, $preview);
        $this->assertTrue($preview[1]['preview_upcoming']);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=HomeFeedServiceTest`
Expected: FAIL — `HomeFeedService` not found.

- [ ] **Step 3: Implement the service**

Create `Modules/HomeContent/app/Services/HomeFeedService.php`:

```php
<?php

namespace Modules\HomeContent\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\HomeContent\Models\HomeBlock;
use Modules\HomeContent\Support\ImageUrl;
use Modules\HomeContent\Support\Link;
use Modules\HomeContent\Support\Locale;
use Modules\HomeContent\Support\Translatable;
use Modules\ProductManagement\Models\Category;

class HomeFeedService
{
    public function __construct(private readonly ProductRailResolver $rails)
    {
    }

    public static function cacheKey(string $platform, string $locale): string
    {
        return "home:v1:{$platform}:{$locale}";
    }

    public static function flushCache(): void
    {
        foreach (['web', 'app'] as $platform) {
            foreach (Locale::SUPPORTED as $locale) {
                Cache::forget(self::cacheKey($platform, $locale));
            }
        }
    }

    public function feed(string $platform, string $locale, bool $preview = false): array
    {
        if ($preview) {
            return $this->build($platform, $locale, true);
        }

        return Cache::remember(
            self::cacheKey($platform, $locale),
            300,
            fn () => $this->build($platform, $locale, false)
        );
    }

    private function build(string $platform, string $locale, bool $preview): array
    {
        $now = now();
        $query = HomeBlock::query()
            ->where('is_active', true)
            ->whereIn('platform', ['both', $platform])
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('sort_order');

        if (!$preview) {
            $query->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now));
        }

        $out = [];
        foreach ($query->get() as $block) {
            try {
                $content = $this->resolveContent($block, $platform, $locale);
            } catch (\Throwable $e) {
                Log::warning("home_blocks: skipping block {$block->id} ({$block->type}): {$e->getMessage()}");
                continue;
            }
            if ($content === null) {
                continue;
            }
            $item = ['id' => $block->id, 'type' => $block->type, 'content' => $content];
            if ($preview && $block->starts_at && $block->starts_at->isFuture()) {
                $item['preview_upcoming'] = true;
            }
            $out[] = $item;
        }

        return $out;
    }

    private function resolveContent(HomeBlock $block, string $platform, string $locale): ?array
    {
        $c = $block->content ?? [];

        return match ($block->type) {
            'announcement_bar' => $this->announcementBar($c, $locale),
            'popup' => $this->popup($c, $locale),
            'hero_slider' => $this->heroSlider($c, $platform, $locale),
            'category_grid' => $this->categoryGrid($c, $locale),
            'product_rail' => $this->productRail($c, $locale),
            'countdown_banner' => $this->countdownBanner($c, $locale),
            'info_strip' => $this->infoStrip($c, $locale),
            'promo_tiles' => $this->promoTiles($c, $locale),
            default => null,
        };
    }

    private function announcementBar(array $c, string $locale): ?array
    {
        $text = Translatable::resolve($c['text'] ?? null, $locale);
        if ($text === null) {
            return null;
        }

        return [
            'text' => $text,
            'link' => Link::resolve($c['link'] ?? null),
            'bg_color' => $c['bg_color'] ?? '#141923',
            'text_color' => $c['text_color'] ?? '#C5A880',
        ];
    }

    private function popup(array $c, string $locale): ?array
    {
        $title = Translatable::resolve($c['title'] ?? null, $locale);
        if ($title === null) {
            return null;
        }

        return [
            'image' => ImageUrl::make($c['image'] ?? null),
            'title' => $title,
            'text' => Translatable::resolve($c['text'] ?? null, $locale),
            'button_label' => Translatable::resolve($c['button_label'] ?? null, $locale),
            'link' => Link::resolve($c['link'] ?? null),
            'frequency' => [
                'mode' => $c['frequency']['mode'] ?? 'once_per_session',
                'days' => (int) ($c['frequency']['days'] ?? 0),
            ],
            'delay_seconds' => (int) ($c['delay_seconds'] ?? 3),
        ];
    }

    private function heroSlider(array $c, string $platform, string $locale): ?array
    {
        $now = now();
        $slides = collect(is_array($c['slides'] ?? null) ? $c['slides'] : [])
            ->filter(function ($slide) use ($now) {
                if (!is_array($slide)) {
                    return false;
                }
                if (!empty($slide['starts_at']) && $now->lt(Carbon::parse($slide['starts_at']))) {
                    return false;
                }
                if (!empty($slide['ends_at']) && $now->gt(Carbon::parse($slide['ends_at']))) {
                    return false;
                }

                return true;
            })
            ->map(function (array $slide) use ($platform, $locale) {
                $image = $platform === 'app'
                    ? ($slide['image_app'] ?? $slide['image_web'] ?? null)
                    : ($slide['image_web'] ?? $slide['image_app'] ?? null);
                if (!$image) {
                    return null;
                }

                return [
                    'image' => ImageUrl::make($image),
                    'title' => Translatable::resolve($slide['title'] ?? null, $locale),
                    'subtitle' => Translatable::resolve($slide['subtitle'] ?? null, $locale),
                    'link' => Link::resolve($slide['link'] ?? null),
                ];
            })
            ->filter()
            ->values();

        return $slides->isEmpty() ? null : ['slides' => $slides->all()];
    }

    private function categoryGrid(array $c, string $locale): ?array
    {
        $ids = array_map('intval', is_array($c['category_ids'] ?? null) ? $c['category_ids'] : []);
        if ($ids === []) {
            return null;
        }
        $categories = Category::whereIn('id', $ids)->get()
            ->sortBy(fn ($cat) => array_search($cat->id, $ids, true))
            ->values();
        if ($categories->isEmpty()) {
            return null;
        }

        return [
            'categories' => $categories->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $locale === 'ar'
                    ? ($cat->name_arabic ?: $cat->name_german)
                    : ($cat->name_german ?: $cat->name_arabic),
                'image' => $cat->image,
            ])->all(),
        ];
    }

    private function productRail(array $c, string $locale): ?array
    {
        $products = $this->rails->resolve($c);
        if ($products === []) {
            return null;
        }

        return [
            'title' => Translatable::resolve($c['title'] ?? null, $locale),
            'rule' => $c['rule'] ?? 'newest',
            'products' => $products,
        ];
    }

    private function countdownBanner(array $c, string $locale): ?array
    {
        $ends = !empty($c['ends_at_display']) ? Carbon::parse($c['ends_at_display']) : null;
        if ($ends === null || $ends->isPast()) {
            return null;
        }
        $text = Translatable::resolve($c['text'] ?? null, $locale);
        if ($text === null) {
            return null;
        }

        return [
            'text' => $text,
            'ends_at' => $ends->toIso8601String(),
            'link' => Link::resolve($c['link'] ?? null),
            'image' => ImageUrl::make($c['image'] ?? null),
            'bg_color' => $c['bg_color'] ?? '#141923',
            'text_color' => $c['text_color'] ?? '#D4AF37',
        ];
    }

    private function infoStrip(array $c, string $locale): ?array
    {
        $items = collect(is_array($c['items'] ?? null) ? $c['items'] : [])
            ->map(function ($item) use ($locale) {
                if (!is_array($item)) {
                    return null;
                }
                $text = Translatable::resolve($item['text'] ?? null, $locale);
                if ($text === null) {
                    return null;
                }

                return [
                    'icon' => $item['icon'] ?? 'tag',
                    'text' => $text,
                    'link' => Link::resolve($item['link'] ?? null),
                ];
            })
            ->filter()
            ->values();

        return $items->isEmpty() ? null : ['items' => $items->all()];
    }

    private function promoTiles(array $c, string $locale): ?array
    {
        $tiles = collect(is_array($c['tiles'] ?? null) ? $c['tiles'] : [])
            ->map(function ($tile) use ($locale) {
                if (!is_array($tile) || empty($tile['image'])) {
                    return null;
                }

                return [
                    'image' => ImageUrl::make($tile['image']),
                    'label' => Translatable::resolve($tile['label'] ?? null, $locale),
                    'link' => Link::resolve($tile['link'] ?? null),
                ];
            })
            ->filter()
            ->values();

        return $tiles->isEmpty() ? null : ['tiles' => $tiles->all()];
    }
}
```

- [ ] **Step 4: Add cache-flush events to the model**

In `Modules/HomeContent/app/Models/HomeBlock.php`, add inside the class (and add `use Modules\HomeContent\Services\HomeFeedService;` at the top):

```php
    protected static function booted(): void
    {
        static::saved(fn () => HomeFeedService::flushCache());
        static::deleted(fn () => HomeFeedService::flushCache());
    }
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test --filter=HomeFeedServiceTest`
Expected: PASS (7 tests). Also re-run `php artisan test --filter=HomeBlockModelTest` — still PASS.

- [ ] **Step 6: Commit**

```bash
git add Modules/HomeContent/app/Services/HomeFeedService.php Modules/HomeContent/app/Models/HomeBlock.php tests/Feature/HomeFeedServiceTest.php
git commit -m "feat(home): feed service resolving blocks per platform/locale with caching"
```

---

### Task 6: Public API — `GET /api/v1/home`

**Files:**
- Create: `Modules/HomeContent/app/Http/Controllers/HomeController.php`
- Modify: `Modules/HomeContent/routes/api.php`, `Modules/HomeContent/config/config.php`, `.env.example`
- Test: `tests/Feature/HomeFeedApiTest.php`

**Interfaces:**
- Consumes: `HomeFeedService::feed()`, `Locale::normalize()`.
- Produces: `GET /api/v1/home?platform=web|app&locale=<any>&preview=1&preview_token=<token>` → `{"status": true, "data": [...]}`; invalid platform → 422. Preview only honored when `preview_token` matches `config('homecontent.preview_token')` (constant-time compare) and that config is non-empty.

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/HomeFeedApiTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HomeContent\Models\HomeBlock;
use Tests\TestCase;

class HomeFeedApiTest extends TestCase
{
    use RefreshDatabase;

    private function announcement(array $overrides = []): HomeBlock
    {
        return HomeBlock::create(array_merge([
            'type' => 'announcement_bar', 'name' => 'Bar', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 1,
            'content' => ['text' => ['ar' => 'أهلا', 'de' => 'Hallo']],
        ], $overrides));
    }

    public function test_returns_resolved_feed_for_platform_and_locale(): void
    {
        $this->announcement();
        $this->announcement(['name' => 'app only', 'platform' => 'app', 'sort_order' => 2]);

        $response = $this->getJson('/api/v1/home?platform=web&locale=de');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'announcement_bar')
            ->assertJsonPath('data.0.content.text', 'Hallo');
    }

    public function test_du_locale_is_normalized_to_de(): void
    {
        $this->announcement();

        $this->getJson('/api/v1/home?platform=web&locale=du')
            ->assertOk()
            ->assertJsonPath('data.0.content.text', 'Hallo');
    }

    public function test_platform_defaults_to_web_and_invalid_platform_is_422(): void
    {
        $this->announcement(['platform' => 'web']);

        $this->getJson('/api/v1/home')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/home?platform=tv')->assertStatus(422);
    }

    public function test_preview_requires_matching_token(): void
    {
        config(['homecontent.preview_token' => 'secret-token']);
        $this->announcement(['starts_at' => now()->addDay()]);

        $this->getJson('/api/v1/home?preview=1')->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/home?preview=1&preview_token=wrong')->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/home?preview=1&preview_token=secret-token')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.preview_upcoming', true);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=HomeFeedApiTest`
Expected: FAIL — 404 on `/api/v1/home`.

- [ ] **Step 3: Implement controller, route, config**

Create `Modules/HomeContent/app/Http/Controllers/HomeController.php`:

```php
<?php

namespace Modules\HomeContent\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\HomeContent\Services\HomeFeedService;
use Modules\HomeContent\Support\Locale;

class HomeController extends Controller
{
    public function index(Request $request, HomeFeedService $service): JsonResponse
    {
        $platform = $request->query('platform', 'web');
        if (!in_array($platform, ['web', 'app'], true)) {
            return response()->json(['status' => false, 'message' => 'platform must be web or app'], 422);
        }

        $locale = Locale::normalize($request->query('locale'));

        $token = (string) config('homecontent.preview_token');
        $preview = $request->boolean('preview')
            && $token !== ''
            && hash_equals($token, (string) $request->query('preview_token'));

        return response()->json([
            'status' => true,
            'data' => $service->feed($platform, $locale, $preview),
        ]);
    }
}
```

Replace `Modules/HomeContent/routes/api.php` content with:

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\HomeContent\Http\Controllers\HomeController;

Route::prefix('v1')->group(function () {
    Route::get('/home', [HomeController::class, 'index']);
});
```

In `Modules/HomeContent/config/config.php`, set:

```php
<?php

return [
    'name' => 'HomeContent',
    'preview_token' => env('HOME_PREVIEW_TOKEN'),
    'storefront_url' => env('STOREFRONT_URL', env('APP_URL', 'http://localhost')),
];
```

Append to `.env.example`:

```
HOME_PREVIEW_TOKEN=
STOREFRONT_URL=
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter=HomeFeedApiTest`
Expected: PASS (4 tests).

- [ ] **Step 5: Commit**

```bash
git add Modules/HomeContent/app/Http/Controllers/HomeController.php Modules/HomeContent/routes/api.php Modules/HomeContent/config/config.php .env.example tests/Feature/HomeFeedApiTest.php
git commit -m "feat(home): public /api/v1/home endpoint with locale normalization and preview token"
```

---

### Task 7: Legacy endpoints read from home blocks

**Files:**
- Modify: `Modules/Banners/app/Http/Controllers/BannerController.php`, `Modules/Banners/app/Http/Controllers/BeforeNavController.php`
- Test: `tests/Feature/LegacyBannerEndpointsTest.php`

**Interfaces:**
- Consumes: `HomeFeedService::feed()`.
- Produces: unchanged public routes `/api/v1/banners/mobile`, `/api/v1/banners/web`, `/api/v1/before-nav/current` with their historical response shapes, now sourced from `home_blocks` (locale `ar`, matching the old single-language data).

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/LegacyBannerEndpointsTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HomeContent\Models\HomeBlock;
use Tests\TestCase;

class LegacyBannerEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private function hero(): HomeBlock
    {
        return HomeBlock::create([
            'type' => 'hero_slider', 'name' => 'Hero', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 1,
            'content' => ['slides' => [[
                'image_web' => 'homeBlocks/w.jpg', 'image_app' => 'homeBlocks/a.jpg',
                'title' => ['ar' => 'تخفيضات'], 'subtitle' => ['ar' => 'حتى ٥٠٪'],
            ]]],
        ]);
    }

    public function test_legacy_mobile_banners_shape(): void
    {
        config(['app.url' => 'https://api.test']);
        $this->hero();

        $this->getJson('/api/v1/banners/mobile')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.image', 'https://api.test/storage/homeBlocks/a.jpg')
            ->assertJsonPath('data.0.title', 'تخفيضات')
            ->assertJsonPath('data.0.description', 'حتى ٥٠٪')
            ->assertJsonPath('data.0.is_mobile', 1);
    }

    public function test_legacy_web_banners_use_web_image(): void
    {
        config(['app.url' => 'https://api.test']);
        $this->hero();

        $this->getJson('/api/v1/banners/web')
            ->assertOk()
            ->assertJsonPath('data.0.image', 'https://api.test/storage/homeBlocks/w.jpg')
            ->assertJsonPath('data.0.is_mobile', 0);
    }

    public function test_legacy_before_nav_returns_current_announcement(): void
    {
        HomeBlock::create([
            'type' => 'announcement_bar', 'name' => 'Bar', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 0,
            'content' => ['text' => ['ar' => 'شحن مجاني']],
        ]);

        $this->getJson('/api/v1/before-nav/current')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.text', 'شحن مجاني');
    }

    public function test_legacy_before_nav_404_when_none(): void
    {
        $this->getJson('/api/v1/before-nav/current')
            ->assertStatus(404)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=LegacyBannerEndpointsTest`
Expected: FAIL — old controllers still read the `banners`/`before_nav` tables, so `data` is empty / shapes mismatch.

- [ ] **Step 3: Rewrite the two legacy controllers**

Replace `Modules/Banners/app/Http/Controllers/BannerController.php` with:

```php
<?php

namespace Modules\Banners\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\HomeContent\Services\HomeFeedService;

class BannerController extends Controller
{
    public function indexMobile()
    {
        return $this->legacyBanners('app', 1);
    }

    public function indexWeb()
    {
        return $this->legacyBanners('web', 0);
    }

    private function legacyBanners(string $platform, int $isMobile)
    {
        try {
            $feed = app(HomeFeedService::class)->feed($platform, 'ar');

            $banners = collect($feed)
                ->where('type', 'hero_slider')
                ->flatMap(fn ($block) => $block['content']['slides'])
                ->values()
                ->map(fn ($slide, $i) => [
                    'id' => $i + 1,
                    'image' => $slide['image'],
                    'title' => $slide['title'],
                    'description' => $slide['subtitle'],
                    'is_mobile' => $isMobile,
                ]);

            return response()->json(['status' => true, 'data' => $banners]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
```

Replace `Modules/Banners/app/Http/Controllers/BeforeNavController.php` with:

```php
<?php

namespace Modules\Banners\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\HomeContent\Services\HomeFeedService;

class BeforeNavController extends Controller
{
    public function index()
    {
    }

    public function getCurrent()
    {
        $feed = app(HomeFeedService::class)->feed('web', 'ar');
        $bar = collect($feed)->firstWhere('type', 'announcement_bar');

        if (!$bar) {
            return response()->json([
                'success' => true,
                'message' => 'No active banner found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Active banner retrieved successfully',
            'data' => [
                'id' => $bar['id'],
                'text' => $bar['content']['text'],
                'start_date' => null,
                'end_date' => null,
            ],
        ], 200);
    }
}
```

(The old shape exposed `start_date`/`end_date`; the web navbar only reads `.text`, so nulls keep the contract.)

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter=LegacyBannerEndpointsTest`
Expected: PASS (4 tests).

- [ ] **Step 5: Commit**

```bash
git add Modules/Banners/app/Http/Controllers tests/Feature/LegacyBannerEndpointsTest.php
git commit -m "feat(home): legacy banner and before-nav endpoints read from home blocks"
```

---

### Task 8: `home:migrate-legacy` data migration command

**Files:**
- Create: `Modules/HomeContent/app/Console/MigrateLegacyHomeContent.php`
- Modify: `Modules/HomeContent/app/Providers/HomeContentServiceProvider.php` (register command)
- Test: `tests/Feature/MigrateLegacyHomeContentTest.php`

**Interfaces:**
- Produces: artisan command `home:migrate-legacy` — converts every `before_nav` row to an `announcement_bar` block and all `banners` rows to one `hero_slider` block named `Legacy hero slider`; idempotent (re-running does nothing).

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/MigrateLegacyHomeContentTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\HomeContent\Models\HomeBlock;
use Tests\TestCase;

class MigrateLegacyHomeContentTest extends TestCase
{
    use RefreshDatabase;

    private function seedLegacy(): void
    {
        DB::table('before_nav')->insert([
            'text' => 'شحن مجاني فوق ٥٠ يورو',
            'start_date' => now()->subDay(), 'end_date' => now()->addMonth(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('banners')->insert([
            ['image' => 'bannersImages/web1.jpg', 'title' => 'صيف', 'description' => 'تخفيضات', 'is_mobile' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['image' => 'bannersImages/app1.jpg', 'title' => null, 'description' => null, 'is_mobile' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function test_converts_legacy_rows_to_blocks(): void
    {
        $this->seedLegacy();

        $this->artisan('home:migrate-legacy')->assertExitCode(0);

        $announcement = HomeBlock::where('type', 'announcement_bar')->firstOrFail();
        $this->assertSame('شحن مجاني فوق ٥٠ يورو', $announcement->content['text']['ar']);
        $this->assertTrue($announcement->is_active);

        $hero = HomeBlock::where('type', 'hero_slider')->firstOrFail();
        $this->assertSame('Legacy hero slider', $hero->name);
        $this->assertCount(2, $hero->content['slides']);
        $this->assertSame('bannersImages/web1.jpg', $hero->content['slides'][0]['image_web']);
        $this->assertSame('bannersImages/app1.jpg', $hero->content['slides'][1]['image_app']);
    }

    public function test_command_is_idempotent(): void
    {
        $this->seedLegacy();

        $this->artisan('home:migrate-legacy')->assertExitCode(0);
        $this->artisan('home:migrate-legacy')->assertExitCode(0);

        $this->assertSame(2, HomeBlock::count());
    }

    public function test_no_legacy_data_is_a_noop(): void
    {
        $this->artisan('home:migrate-legacy')->assertExitCode(0);
        $this->assertSame(0, HomeBlock::count());
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=MigrateLegacyHomeContentTest`
Expected: FAIL — command not found.

- [ ] **Step 3: Implement the command**

Create `Modules/HomeContent/app/Console/MigrateLegacyHomeContent.php`:

```php
<?php

namespace Modules\HomeContent\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\HomeContent\Models\HomeBlock;

class MigrateLegacyHomeContent extends Command
{
    protected $signature = 'home:migrate-legacy';

    protected $description = 'Convert legacy before_nav and banners rows into home_blocks';

    public function handle(): int
    {
        if (HomeBlock::where('name', 'like', 'Legacy%')->exists()) {
            $this->info('Legacy content already migrated; nothing to do.');

            return self::SUCCESS;
        }

        $sort = (int) HomeBlock::max('sort_order');
        $created = 0;

        foreach (DB::table('before_nav')->orderBy('created_at')->get() as $row) {
            HomeBlock::create([
                'type' => 'announcement_bar',
                'name' => 'Legacy announcement #' . $row->id,
                'platform' => 'both',
                'is_active' => true,
                'starts_at' => $row->start_date,
                'ends_at' => $row->end_date,
                'sort_order' => ++$sort,
                'content' => [
                    'text' => ['ar' => $row->text],
                    'bg_color' => '#141923',
                    'text_color' => '#C5A880',
                ],
            ]);
            $created++;
        }

        $banners = DB::table('banners')->orderBy('created_at')->get();
        if ($banners->isNotEmpty()) {
            $slides = $banners->map(fn ($banner) => [
                'image_web' => $banner->is_mobile ? null : $banner->image,
                'image_app' => $banner->is_mobile ? $banner->image : null,
                'title' => $banner->title ? ['ar' => $banner->title] : null,
                'subtitle' => $banner->description ? ['ar' => $banner->description] : null,
                'link' => null,
            ])->values()->all();

            HomeBlock::create([
                'type' => 'hero_slider',
                'name' => 'Legacy hero slider',
                'platform' => 'both',
                'is_active' => true,
                'sort_order' => ++$sort,
                'content' => ['slides' => $slides],
            ]);
            $created++;
        }

        $this->info("Created {$created} home blocks from legacy data.");

        return self::SUCCESS;
    }
}
```

Register it in `Modules/HomeContent/app/Providers/HomeContentServiceProvider.php` — inside `register()` (or `boot()`), add:

```php
        $this->commands([\Modules\HomeContent\Console\MigrateLegacyHomeContent::class]);
```

(If the generated provider already has a command-registration mechanism, use that — the requirement is only that `php artisan home:migrate-legacy` resolves.)

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter=MigrateLegacyHomeContentTest`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
git add Modules/HomeContent/app/Console Modules/HomeContent/app/Providers/HomeContentServiceProvider.php tests/Feature/MigrateLegacyHomeContentTest.php
git commit -m "feat(home): home:migrate-legacy command converting banners and before_nav"
```

---

### Task 9: Admin controller — CRUD, reorder, toggle, search, rail preview

**Files:**
- Create: `Modules/HomeContent/app/Http/Controllers/HomeBlockAdminController.php`
- Modify: `Modules/HomeContent/routes/web.php`
- Test: `tests/Feature/HomeBlockAdminTest.php`

**Interfaces:**
- Consumes: `BlockContentRules::for()/files()`, `ProductRailResolver::resolve()`, `HomeFeedService::flushCache()`, `HomeBlock`, existing `admin.auth` middleware alias.
- Produces (route names): `home-blocks.index/create/store/edit/update/destroy` (resource, no `show`), `home-blocks.reorder` (`POST /home-blocks/reorder`, body `{ids: [..]}`), `home-blocks.toggle` (`POST /home-blocks/{id}/toggle` → JSON `{status, is_active}`), `home-blocks.search.products` and `home-blocks.search.categories` (`GET ...?q=` → `{status, data: [{id, name_arabic, name_german}]}`), `home-blocks.rail-preview` (`POST`, body `{rule, category_id?, product_ids?}` → `{status, data: [up to 6 product cards]}`).
- Store/update behavior: validates shared fields + type rules + file rules; stores uploaded images into `content` (popup/countdown → `content.image`; `slide_images_web[i]`/`slide_images_app[i]` → `content.slides[i].image_web/image_app`; `tile_images[i]` → `content.tiles[i].image`); rejects hero slides / promo tiles that end up with no image.

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/HomeBlockAdminTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Admin\Models\UserAdmin;
use Modules\HomeContent\Models\HomeBlock;
use Tests\TestCase;

class HomeBlockAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::create([
            'name' => 'A', 'email' => 'a' . uniqid() . '@t.test',
            'password' => 'x', 'email_verified_at' => now(),
        ]);
        UserAdmin::create(['user_id' => $user->id, 'is_active' => 1]);

        return $user;
    }

    private function block(array $overrides = []): HomeBlock
    {
        return HomeBlock::create(array_merge([
            'type' => 'announcement_bar', 'name' => 'Bar', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 1,
            'content' => ['text' => ['ar' => 'أهلا']],
        ], $overrides));
    }

    public function test_guests_cannot_reach_the_builder(): void
    {
        $this->get(route('home-blocks.index'))->assertRedirect();
    }

    public function test_admin_can_create_an_announcement_bar(): void
    {
        $this->actingAs($this->admin())
            ->post(route('home-blocks.store'), [
                'type' => 'announcement_bar', 'name' => 'App promo', 'platform' => 'web',
                'is_active' => 1,
                'content' => ['text' => ['en' => 'Download our app'], 'bg_color' => '#141923'],
            ])
            ->assertRedirect(route('home-blocks.index'));

        $block = HomeBlock::firstOrFail();
        $this->assertSame('announcement_bar', $block->type);
        $this->assertSame('Download our app', $block->content['text']['en']);
    }

    public function test_validation_rejects_empty_translations(): void
    {
        $this->actingAs($this->admin())
            ->from(route('home-blocks.create', ['type' => 'announcement_bar']))
            ->post(route('home-blocks.store'), [
                'type' => 'announcement_bar', 'name' => 'Bad', 'platform' => 'web',
                'content' => ['text' => ['ar' => '', 'de' => '', 'en' => '']],
            ])
            ->assertSessionHasErrors();

        $this->assertSame(0, HomeBlock::count());
    }

    public function test_admin_can_create_popup_with_uploaded_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('home-blocks.store'), [
                'type' => 'popup', 'name' => 'Get the app', 'platform' => 'web', 'is_active' => 1,
                'content' => [
                    'title' => ['en' => 'Get the app'],
                    'frequency' => ['mode' => 'once_per_days', 'days' => 7],
                    'delay_seconds' => 3,
                ],
                'popup_image' => UploadedFile::fake()->image('popup.jpg', 600, 800),
            ])
            ->assertRedirect(route('home-blocks.index'));

        $block = HomeBlock::firstOrFail();
        $this->assertStringStartsWith('homeBlocks/', $block->content['image']);
        Storage::disk('public')->assertExists($block->content['image']);
    }

    public function test_hero_slide_without_any_image_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->from(route('home-blocks.create', ['type' => 'hero_slider']))
            ->post(route('home-blocks.store'), [
                'type' => 'hero_slider', 'name' => 'Hero', 'platform' => 'both',
                'content' => ['slides' => [['title' => ['ar' => 'صيف']]]],
            ])
            ->assertSessionHasErrors();
    }

    public function test_admin_can_update_and_delete(): void
    {
        $block = $this->block();

        $this->actingAs($this->admin())
            ->put(route('home-blocks.update', $block), [
                'name' => 'Renamed', 'platform' => 'app', 'is_active' => 0,
                'content' => ['text' => ['de' => 'Hallo']],
            ])
            ->assertRedirect(route('home-blocks.index'));
        $this->assertSame('Renamed', $block->fresh()->name);

        $this->actingAs($this->admin())->delete(route('home-blocks.destroy', $block));
        $this->assertSame(0, HomeBlock::count());
    }

    public function test_reorder_rewrites_sort_order(): void
    {
        $first = $this->block(['name' => 'first', 'sort_order' => 0]);
        $second = $this->block(['name' => 'second', 'sort_order' => 1]);

        $this->actingAs($this->admin())
            ->postJson(route('home-blocks.reorder'), ['ids' => [$second->id, $first->id]])
            ->assertOk();

        $this->assertSame(0, $second->fresh()->sort_order);
        $this->assertSame(1, $first->fresh()->sort_order);
    }

    public function test_toggle_flips_active_flag(): void
    {
        $block = $this->block(['is_active' => true]);

        $this->actingAs($this->admin())
            ->postJson(route('home-blocks.toggle', $block))
            ->assertOk()
            ->assertJsonPath('is_active', false);

        $this->assertFalse($block->fresh()->is_active);
    }

    public function test_search_endpoints_return_matches(): void
    {
        $category = \Modules\ProductManagement\Models\Category::create([
            'name_arabic' => 'فساتين', 'name_german' => 'Kleider',
            'slug_arabic' => 'k-ar-' . uniqid(), 'slug_german' => 'k-de-' . uniqid(),
        ]);

        $this->actingAs($this->admin())
            ->getJson(route('home-blocks.search.categories', ['q' => 'Klei']))
            ->assertOk()
            ->assertJsonPath('data.0.id', $category->id);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=HomeBlockAdminTest`
Expected: FAIL — routes not defined.

- [ ] **Step 3: Define admin routes**

Replace `Modules/HomeContent/routes/web.php` with (note: `reorder` MUST be declared before the resource so it isn't captured as `{home_block}`):

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\HomeContent\Http\Controllers\HomeBlockAdminController;

Route::middleware(['admin.auth'])->group(function () {
    Route::post('home-blocks/reorder', [HomeBlockAdminController::class, 'reorder'])->name('home-blocks.reorder');
    Route::post('home-blocks/{home_block}/toggle', [HomeBlockAdminController::class, 'toggle'])->name('home-blocks.toggle');
    Route::get('home-blocks-search/products', [HomeBlockAdminController::class, 'searchProducts'])->name('home-blocks.search.products');
    Route::get('home-blocks-search/categories', [HomeBlockAdminController::class, 'searchCategories'])->name('home-blocks.search.categories');
    Route::post('home-blocks-rail-preview', [HomeBlockAdminController::class, 'railPreview'])->name('home-blocks.rail-preview');
    Route::resource('home-blocks', HomeBlockAdminController::class)->except(['show'])->names('home-blocks');
});
```

- [ ] **Step 4: Implement the controller**

Create `Modules/HomeContent/app/Http/Controllers/HomeBlockAdminController.php`:

```php
<?php

namespace Modules\HomeContent\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Modules\HomeContent\Models\HomeBlock;
use Modules\HomeContent\Services\HomeFeedService;
use Modules\HomeContent\Services\ProductRailResolver;
use Modules\HomeContent\Support\BlockContentRules;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;

class HomeBlockAdminController extends Controller
{
    public function index()
    {
        $blocks = HomeBlock::orderBy('sort_order')->get();

        return view('homecontent::index', compact('blocks'));
    }

    public function create(Request $request)
    {
        $type = (string) $request->query('type');
        abort_unless(in_array($type, HomeBlock::TYPES, true), 404);

        $block = new HomeBlock(['type' => $type, 'platform' => 'both', 'is_active' => true, 'content' => []]);

        return view('homecontent::form', compact('block', 'type'));
    }

    public function store(Request $request)
    {
        $type = (string) $request->input('type');
        abort_unless(in_array($type, HomeBlock::TYPES, true), 422);

        $data = $this->validated($request, $type);
        $content = $this->mergeUploadedImages($request, $type, $data['content'] ?? []);
        $this->assertImagesPresent($type, $content);

        HomeBlock::create([
            'type' => $type,
            'name' => $data['name'],
            'platform' => $data['platform'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'sort_order' => (int) HomeBlock::max('sort_order') + 1,
            'content' => $content,
        ]);

        return redirect()->route('home-blocks.index')->with('success', 'Block created successfully');
    }

    public function edit($id)
    {
        $block = HomeBlock::findOrFail($id);
        $type = $block->type;

        return view('homecontent::form', compact('block', 'type'));
    }

    public function update(Request $request, $id)
    {
        $block = HomeBlock::findOrFail($id);
        $data = $this->validated($request, $block->type);
        $content = $this->mergeUploadedImages($request, $block->type, $data['content'] ?? []);
        $this->assertImagesPresent($block->type, $content);

        $block->update([
            'name' => $data['name'],
            'platform' => $data['platform'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'content' => $content,
        ]);

        return redirect()->route('home-blocks.index')->with('success', 'Block updated successfully');
    }

    public function destroy($id)
    {
        HomeBlock::findOrFail($id)->delete();

        return redirect()->route('home-blocks.index')->with('success', 'Block deleted successfully');
    }

    public function reorder(Request $request)
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];

        foreach (array_values($ids) as $position => $id) {
            HomeBlock::where('id', $id)->update(['sort_order' => $position]);
        }
        HomeFeedService::flushCache(); // query-builder updates bypass model events

        return response()->json(['status' => true]);
    }

    public function toggle($id)
    {
        $block = HomeBlock::findOrFail($id);
        $block->is_active = !$block->is_active;
        $block->save();

        return response()->json(['status' => true, 'is_active' => $block->is_active]);
    }

    public function searchProducts(Request $request)
    {
        $q = (string) $request->query('q', '');

        $products = Product::where('is_active', true)
            ->where(fn ($w) => $w
                ->where('name_arabic', 'like', "%{$q}%")
                ->orWhere('name_german', 'like', "%{$q}%"))
            ->limit(10)
            ->get(['id', 'name_arabic', 'name_german']);

        return response()->json(['status' => true, 'data' => $products]);
    }

    public function searchCategories(Request $request)
    {
        $q = (string) $request->query('q', '');

        $categories = Category::where(fn ($w) => $w
                ->where('name_arabic', 'like', "%{$q}%")
                ->orWhere('name_german', 'like', "%{$q}%"))
            ->limit(10)
            ->get(['id', 'name_arabic', 'name_german']);

        return response()->json(['status' => true, 'data' => $categories]);
    }

    public function railPreview(Request $request, ProductRailResolver $resolver)
    {
        $content = $request->validate([
            'rule' => ['required', 'in:newest,best_sellers,category,manual'],
            'category_id' => ['nullable', 'integer'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer'],
        ]);
        $content['limit'] = 6;

        return response()->json(['status' => true, 'data' => $resolver->resolve($content)]);
    }

    private function validated(Request $request, string $type): array
    {
        return $request->validate(array_merge(
            [
                'name' => ['required', 'string', 'max:100'],
                'platform' => ['required', 'in:web,app,both'],
                'starts_at' => ['nullable', 'date'],
                'ends_at' => ['nullable', 'date', 'after:starts_at'],
                'is_active' => ['nullable', 'boolean'],
            ],
            BlockContentRules::for($type),
            BlockContentRules::files($type),
        ));
    }

    private function mergeUploadedImages(Request $request, string $type, array $content): array
    {
        $store = fn (UploadedFile $file) => $file->store('homeBlocks', 'public');

        if ($type === 'popup' && $request->hasFile('popup_image')) {
            $content['image'] = $store($request->file('popup_image'));
        }
        if ($type === 'countdown_banner' && $request->hasFile('countdown_image')) {
            $content['image'] = $store($request->file('countdown_image'));
        }
        if ($type === 'hero_slider') {
            foreach (array_keys($content['slides'] ?? []) as $i) {
                if ($request->hasFile("slide_images_web.{$i}")) {
                    $content['slides'][$i]['image_web'] = $store($request->file("slide_images_web.{$i}"));
                }
                if ($request->hasFile("slide_images_app.{$i}")) {
                    $content['slides'][$i]['image_app'] = $store($request->file("slide_images_app.{$i}"));
                }
            }
        }
        if ($type === 'promo_tiles') {
            foreach (array_keys($content['tiles'] ?? []) as $i) {
                if ($request->hasFile("tile_images.{$i}")) {
                    $content['tiles'][$i]['image'] = $store($request->file("tile_images.{$i}"));
                }
            }
        }

        return $content;
    }

    private function assertImagesPresent(string $type, array $content): void
    {
        if ($type === 'hero_slider') {
            foreach ($content['slides'] ?? [] as $i => $slide) {
                if (empty($slide['image_web']) && empty($slide['image_app'])) {
                    throw ValidationException::withMessages([
                        'content.slides' => 'Slide ' . ($i + 1) . ' needs a web or app image.',
                    ]);
                }
            }
        }
        if ($type === 'promo_tiles') {
            foreach ($content['tiles'] ?? [] as $i => $tile) {
                if (empty($tile['image'])) {
                    throw ValidationException::withMessages([
                        'content.tiles' => 'Tile ' . ($i + 1) . ' needs an image.',
                    ]);
                }
            }
        }
    }
}
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test --filter=HomeBlockAdminTest`
Expected: PASS (9 tests). If a test fails because a view is missing, create temporary placeholders `Modules/HomeContent/resources/views/index.blade.php` and `form.blade.php` each containing `<x-admin-layout><div>WIP</div></x-admin-layout>` — Task 10/11 replace them.

- [ ] **Step 6: Commit**

```bash
git add Modules/HomeContent/app/Http/Controllers/HomeBlockAdminController.php Modules/HomeContent/routes/web.php Modules/HomeContent/resources/views tests/Feature/HomeBlockAdminTest.php
git commit -m "feat(home): admin CRUD, reorder, toggle, search and rail-preview endpoints"
```

---

### Task 10: Builder list UI, type picker, sidebar link, legacy admin redirects

**Files:**
- Create: `Modules/HomeContent/resources/views/index.blade.php`
- Modify: `resources/views/components/admin/sidebar.blade.php`, `Modules/Admin/routes/web.php`
- Test: `tests/Feature/HomeBlockAdminUiTest.php`

**Interfaces:**
- Consumes: routes from Task 9; `<x-admin-layout>` component (global, from `resources/views/layouts/admin.blade.php` — Tailwind + Alpine already loaded); `config('homecontent.preview_token')`/`storefront_url`.
- Produces: the builder list page; sidebar "Homepage" nav item; legacy admin URLs `/banners` and `/before-nav` redirect to the builder (route names `banners.index`, `before-nav.index` preserved because the sidebar/other blades may reference them until cleanup).

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/HomeBlockAdminUiTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Modules\HomeContent\Models\HomeBlock;
use Tests\TestCase;

class HomeBlockAdminUiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::create([
            'name' => 'A', 'email' => 'a' . uniqid() . '@t.test',
            'password' => 'x', 'email_verified_at' => now(),
        ]);
        UserAdmin::create(['user_id' => $user->id, 'is_active' => 1]);

        return $user;
    }

    public function test_index_lists_blocks_with_controls(): void
    {
        HomeBlock::create([
            'type' => 'announcement_bar', 'name' => 'Summer promo', 'platform' => 'web',
            'is_active' => true, 'sort_order' => 1, 'content' => ['text' => ['ar' => 'x']],
        ]);

        $this->actingAs($this->admin())
            ->get(route('home-blocks.index'))
            ->assertOk()
            ->assertSee('Homepage Builder')
            ->assertSee('Summer promo')
            ->assertSee('announcement bar')
            ->assertSee('Add block');
    }

    public function test_legacy_admin_pages_redirect_to_builder(): void
    {
        $this->actingAs($this->admin())->get('/banners')->assertRedirect(route('home-blocks.index'));
        $this->actingAs($this->admin())->get('/before-nav')->assertRedirect(route('home-blocks.index'));
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=HomeBlockAdminUiTest`
Expected: FAIL — placeholder view has no "Homepage Builder" text; `/banners` still renders the old page.

- [ ] **Step 3: Build the index view**

Replace `Modules/HomeContent/resources/views/index.blade.php` with:

```blade
<x-admin-layout>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-semibold text-slate-800">Homepage Builder</h1>
        <div class="flex items-center gap-2">
            @if (config('homecontent.preview_token'))
                <a href="{{ rtrim(config('homecontent.storefront_url'), '/') }}/?preview=1&preview_token={{ config('homecontent.preview_token') }}"
                   target="_blank" rel="noopener"
                   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-slate-700 rounded-lg text-sm">
                    Preview homepage
                </a>
            @endif
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-sm">
                    + Add block
                </button>
                <div x-show="open" @click.outside="open = false" x-cloak
                     class="absolute right-0 mt-2 w-72 bg-white border border-gray-200 rounded-lg shadow-lg z-20 py-1">
                    @php
                        // FontAwesome is already loaded by the admin layout; icon per type so
                        // non-technical admins recognize blocks visually (spec §5).
                        $typeIcons = [
                            'announcement_bar' => 'fa-bullhorn', 'popup' => 'fa-window-restore',
                            'hero_slider' => 'fa-images', 'category_grid' => 'fa-circle-dot',
                            'product_rail' => 'fa-grip-lines', 'countdown_banner' => 'fa-stopwatch',
                            'info_strip' => 'fa-truck-fast', 'promo_tiles' => 'fa-table-cells-large',
                        ];
                    @endphp
                    @foreach (\Modules\HomeContent\Models\HomeBlock::TYPES as $blockType)
                        <a href="{{ route('home-blocks.create', ['type' => $blockType]) }}"
                           class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-gray-50 capitalize">
                            <i class="fa-solid {{ $typeIcons[$blockType] }} w-4 text-slate-400"></i>
                            {{ str_replace('_', ' ', $blockType) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @if ($blocks->isEmpty())
        <p class="text-gray-500">No blocks yet — add your first block to start composing the homepage.</p>
    @endif

    <ul id="blocks-list" class="space-y-2">
        @foreach ($blocks as $block)
            <li data-id="{{ $block->id }}"
                class="bg-white border border-gray-200 rounded-lg px-4 py-3 flex items-center gap-3">
                <span class="drag-handle cursor-grab text-gray-400 text-lg leading-none select-none">&#8801;</span>
                <span class="font-medium text-slate-800 flex-1 truncate">{{ $block->name }}</span>
                <span class="text-xs px-2 py-1 rounded bg-gray-100 text-slate-600 capitalize">{{ str_replace('_', ' ', $block->type) }}</span>
                <span class="text-xs px-2 py-1 rounded bg-gray-100 text-slate-600">{{ $block->platform }}</span>
                <span class="text-xs whitespace-nowrap {{ $block->ends_at && $block->ends_at->isPast() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                    @if ($block->ends_at && $block->ends_at->isPast())
                        expired
                    @elseif ($block->starts_at && $block->starts_at->isFuture())
                        starts {{ $block->starts_at->format('M j') }}
                    @elseif ($block->ends_at)
                        ends {{ $block->ends_at->format('M j') }}
                    @endif
                </span>
                <button type="button" data-id="{{ $block->id }}"
                        class="toggle-btn text-xs px-3 py-1 rounded-full {{ $block->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500' }}">
                    {{ $block->is_active ? 'ON' : 'OFF' }}
                </button>
                <a href="{{ route('home-blocks.edit', $block) }}" class="text-blue-600 hover:underline text-sm">Edit</a>
                <form method="POST" action="{{ route('home-blocks.destroy', $block) }}"
                      onsubmit="return confirm('Delete this block?')">
                    @csrf
                    @method('DELETE')
                    <button class="text-red-600 hover:underline text-sm">Delete</button>
                </form>
            </li>
        @endforeach
    </ul>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        const csrfToken = document.querySelector('meta[name=csrf-token]').content;

        new Sortable(document.getElementById('blocks-list'), {
            handle: '.drag-handle',
            animation: 150,
            onEnd() {
                const ids = [...document.querySelectorAll('#blocks-list [data-id]')].map(el => Number(el.dataset.id));
                fetch('{{ route('home-blocks.reorder') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ ids }),
                });
            },
        });

        document.querySelectorAll('.toggle-btn').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const res = await fetch(`/home-blocks/${btn.dataset.id}/toggle`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                });
                const json = await res.json();
                btn.textContent = json.is_active ? 'ON' : 'OFF';
                btn.className = 'toggle-btn text-xs px-3 py-1 rounded-full ' +
                    (json.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500');
            });
        });
    </script>
</x-admin-layout>
```

- [ ] **Step 4: Swap the sidebar nav item and redirect legacy admin routes**

In `resources/views/components/admin/sidebar.blade.php`, find the `<!-- Banners -->` block (an `<li>`/`<a>` using `route('banners.index')` and `request()->routeIs('banners.*')`, around line 406). Duplicate its exact markup and classes, then in the copy change: `route('banners.index')` → `route('home-blocks.index')`, every `routeIs('banners.*')` → `routeIs('home-blocks.*')`, the label text `Banners` → `Homepage`, and the icon class to `fa-house` (keep sizing classes). Delete the original Banners block (and a Before Nav block if one exists).

In `Modules/Admin/routes/web.php`:
1. Delete the line `Route::resource('banners', BannerController::class)->names('banners');`
2. Delete the line `Route::resource('before-nav', BeforeNavController::class)->names('before-nav');`
3. In their place (still inside the `admin.auth` group) add:

```php
    // Legacy pages superseded by the Homepage Builder (HomeContent module)
    Route::get('banners', fn () => redirect()->route('home-blocks.index'))->name('banners.index');
    Route::get('before-nav', fn () => redirect()->route('home-blocks.index'))->name('before-nav.index');
```

4. Remove the now-unused `use ...BannerController;` and `use ...BeforeNavController;` imports from that file.

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test --filter=HomeBlockAdminUiTest`
Expected: PASS (2 tests). Also run `php artisan test` fully — any test referencing `banners.create`/`before-nav.*` routes would surface here (none are known to exist).

- [ ] **Step 6: Commit**

```bash
git add Modules/HomeContent/resources/views/index.blade.php resources/views/components/admin/sidebar.blade.php Modules/Admin/routes/web.php tests/Feature/HomeBlockAdminUiTest.php
git commit -m "feat(home): homepage builder list UI with drag reorder, sidebar link, legacy redirects"
```

---

### Task 11: Form shell + shared partials + text-block forms (announcement, countdown, info strip, popup)

**Files:**
- Create: `Modules/HomeContent/resources/views/form.blade.php`, `resources/views/partials/shared.blade.php`, `partials/i18n-input.blade.php`, `partials/link-picker.blade.php`, `forms/announcement_bar.blade.php`, `forms/countdown_banner.blade.php`, `forms/info_strip.blade.php`, `forms/popup.blade.php`
- Test: extend `tests/Feature/HomeBlockAdminUiTest.php`

**Interfaces:**
- Consumes: Task 9 controller (passes `$block`, `$type`; expects inputs named `name`, `platform`, `starts_at`, `ends_at`, `is_active`, `content[...]`, and file fields `popup_image` / `countdown_image`).
- Produces: `homecontent::form` renders `homecontent::forms.{$type}`; every type form can assume Blade vars `$block`, `$type`, `$c` (content array with `old()` precedence) and an Alpine scope `{ lang: 'ar' }` plus the global JS function `linkPicker(initial)`.

- [ ] **Step 1: Add failing render tests**

Append to `tests/Feature/HomeBlockAdminUiTest.php` (inside the class):

```php
    public function test_create_forms_render_for_text_block_types(): void
    {
        foreach (['announcement_bar', 'countdown_banner', 'info_strip', 'popup'] as $type) {
            $this->actingAs($this->admin())
                ->get(route('home-blocks.create', ['type' => $type]))
                ->assertOk()
                ->assertSee('name="name"', false)
                ->assertSee('content[', false);
        }
    }

    public function test_edit_form_shows_existing_values(): void
    {
        $block = HomeBlock::create([
            'type' => 'announcement_bar', 'name' => 'Existing bar', 'platform' => 'web',
            'is_active' => true, 'sort_order' => 1,
            'content' => ['text' => ['de' => 'Kostenloser Versand']],
        ]);

        $this->actingAs($this->admin())
            ->get(route('home-blocks.edit', $block))
            ->assertOk()
            ->assertSee('Existing bar')
            ->assertSee('Kostenloser Versand');
    }
```

Run: `php artisan test --filter=HomeBlockAdminUiTest`
Expected: new tests FAIL (placeholder form view).

- [ ] **Step 2: Build the form shell**

Replace `Modules/HomeContent/resources/views/form.blade.php` with:

```blade
<x-admin-layout>
    @php $c = old('content', $block->content ?? []); @endphp

    <h1 class="text-2xl font-semibold text-slate-800 mb-6 capitalize">
        {{ $block->exists ? 'Edit' : 'New' }} block — {{ str_replace('_', ' ', $type) }}
    </h1>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc ps-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data"
          action="{{ $block->exists ? route('home-blocks.update', $block) : route('home-blocks.store') }}"
          x-data="{ lang: 'ar' }" class="space-y-6 max-w-3xl">
        @csrf
        @if ($block->exists)
            @method('PUT')
        @endif
        <input type="hidden" name="type" value="{{ $type }}">

        @include('homecontent::partials.shared')

        <div class="bg-white border border-gray-200 rounded-lg p-5 space-y-5">
            @include('homecontent::forms.' . $type)
        </div>

        <button class="px-6 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg">Save</button>
        <a href="{{ route('home-blocks.index') }}" class="ms-2 text-sm text-gray-500 hover:underline">Cancel</a>
    </form>

    <script>
        function linkPicker(initial) {
            return {
                type: (initial && initial.type) || 'none',
                value: (initial && initial.value) || null,
                label: (initial && initial.value) ? ('#' + initial.value) : '',
                q: '',
                results: [],
                async search() {
                    if (this.q.length < 2) { this.results = []; return; }
                    const base = this.type === 'product'
                        ? '{{ route('home-blocks.search.products') }}'
                        : '{{ route('home-blocks.search.categories') }}';
                    const res = await fetch(base + '?q=' + encodeURIComponent(this.q));
                    this.results = (await res.json()).data;
                },
                pick(item) {
                    this.value = item.id;
                    this.label = item.name_german || item.name_arabic;
                    this.results = [];
                    this.q = '';
                },
            };
        }

        // Generic repeater: clones <template data-repeater-for="X"> into #X-rows replacing __IDX__.
        function addRepeaterRow(key) {
            const template = document.querySelector(`template[data-repeater-for="${key}"]`);
            const container = document.getElementById(`${key}-rows`);
            const index = container.children.length;
            const html = template.innerHTML.replaceAll('__IDX__', String(index));
            container.insertAdjacentHTML('beforeend', html);
        }
    </script>
</x-admin-layout>
```

- [ ] **Step 3: Build the shared partials**

Create `Modules/HomeContent/resources/views/partials/shared.blade.php`:

```blade
<div class="bg-white border border-gray-200 rounded-lg p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Internal name</label>
        <input type="text" name="name" value="{{ old('name', $block->name) }}" required
               class="w-full border-gray-300 rounded-lg" placeholder="e.g. Summer Sale Hero">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Platform</label>
        <select name="platform" class="w-full border-gray-300 rounded-lg">
            @foreach (['both' => 'Web + App', 'web' => 'Web only', 'app' => 'App only'] as $value => $label)
                <option value="{{ $value }}" @selected(old('platform', $block->platform) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex items-end pb-2">
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="rounded"
                   @checked(old('is_active', $block->is_active))>
            Active
        </label>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Starts at (optional)</label>
        <input type="datetime-local" name="starts_at" class="w-full border-gray-300 rounded-lg"
               value="{{ old('starts_at', optional($block->starts_at)->format('Y-m-d\TH:i')) }}">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Ends at (optional)</label>
        <input type="datetime-local" name="ends_at" class="w-full border-gray-300 rounded-lg"
               value="{{ old('ends_at', optional($block->ends_at)->format('Y-m-d\TH:i')) }}">
    </div>
</div>
```

Create `Modules/HomeContent/resources/views/partials/i18n-input.blade.php` (vars: `$label`, `$name` e.g. `content[text]`, `$values` array):

```blade
<div>
    <div class="flex items-center justify-between mb-1">
        <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
        <div class="flex gap-1 text-xs">
            @foreach (['ar' => 'العربية', 'de' => 'Deutsch', 'en' => 'English'] as $code => $langLabel)
                <button type="button" @click="lang = '{{ $code }}'"
                        :class="lang === '{{ $code }}' ? 'bg-slate-800 text-white' : 'bg-gray-100 text-slate-600'"
                        class="px-2 py-0.5 rounded">
                    {{ $langLabel }}@if (!empty($values[$code])) &#8226;@endif
                </button>
            @endforeach
        </div>
    </div>
    @foreach (['ar', 'de', 'en'] as $code)
        <input x-show="lang === '{{ $code }}'" type="text"
               name="{{ $name }}[{{ $code }}]" value="{{ $values[$code] ?? '' }}"
               dir="{{ $code === 'ar' ? 'rtl' : 'ltr' }}"
               class="w-full border-gray-300 rounded-lg" placeholder="{{ $label }} ({{ strtoupper($code) }})">
    @endforeach
</div>
```

Create `Modules/HomeContent/resources/views/partials/link-picker.blade.php` (vars: `$name` e.g. `content[link]`, `$value` array|null):

```blade
@php $initialLink = $value ?? ['type' => 'none', 'value' => null]; @endphp
<div x-data='linkPicker(@json($initialLink))' class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">Link</label>
    <select x-model="type" class="border-gray-300 rounded-lg">
        <option value="none">No link</option>
        <option value="category">Category</option>
        <option value="product">Product</option>
        <option value="url">Custom URL</option>
    </select>
    <input type="hidden" name="{{ $name }}[type]" :value="type">

    <template x-if="type === 'url'">
        <input type="text" name="{{ $name }}[value]" x-model="value"
               placeholder="https://..." class="w-full border-gray-300 rounded-lg">
    </template>

    <template x-if="type === 'category' || type === 'product'">
        <div>
            <input type="hidden" name="{{ $name }}[value]" :value="value">
            <input type="text" x-model="q" @input.debounce.300ms="search()"
                   :placeholder="'Search ' + type + 's…'" class="w-full border-gray-300 rounded-lg">
            <div x-show="results.length" class="border border-gray-200 rounded-lg bg-white mt-1 max-h-48 overflow-y-auto">
                <template x-for="result in results" :key="result.id">
                    <button type="button" @click="pick(result)"
                            class="block w-full text-start px-3 py-1.5 text-sm hover:bg-gray-50"
                            x-text="(result.name_german || result.name_arabic) + ' (#' + result.id + ')'"></button>
                </template>
            </div>
            <p class="text-xs text-gray-500 mt-1" x-show="label" x-text="'Selected: ' + label"></p>
        </div>
    </template>
</div>
```

- [ ] **Step 4: Build the four text-block forms**

Create `Modules/HomeContent/resources/views/forms/announcement_bar.blade.php`:

```blade
@include('homecontent::partials.i18n-input', ['label' => 'Announcement text', 'name' => 'content[text]', 'values' => $c['text'] ?? []])
@include('homecontent::partials.link-picker', ['name' => 'content[link]', 'value' => $c['link'] ?? null])
<div class="flex gap-8">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Background</label>
        <input type="color" name="content[bg_color]" value="{{ $c['bg_color'] ?? '#141923' }}" class="h-9 w-16">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Text color</label>
        <input type="color" name="content[text_color]" value="{{ $c['text_color'] ?? '#C5A880' }}" class="h-9 w-16">
    </div>
</div>
```

Create `Modules/HomeContent/resources/views/forms/countdown_banner.blade.php`:

```blade
@include('homecontent::partials.i18n-input', ['label' => 'Banner text', 'name' => 'content[text]', 'values' => $c['text'] ?? []])
<div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Countdown ends at</label>
    <input type="datetime-local" name="content[ends_at_display]" required class="border-gray-300 rounded-lg"
           value="{{ isset($c['ends_at_display']) ? \Carbon\Carbon::parse($c['ends_at_display'])->format('Y-m-d\TH:i') : '' }}">
</div>
@include('homecontent::partials.link-picker', ['name' => 'content[link]', 'value' => $c['link'] ?? null])
<div class="flex gap-8">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Background</label>
        <input type="color" name="content[bg_color]" value="{{ $c['bg_color'] ?? '#141923' }}" class="h-9 w-16">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Text color</label>
        <input type="color" name="content[text_color]" value="{{ $c['text_color'] ?? '#D4AF37' }}" class="h-9 w-16">
    </div>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Background image (optional)</label>
    @if (!empty($c['image']))
        <img src="{{ \Modules\HomeContent\Support\ImageUrl::make($c['image']) }}" class="h-16 rounded mb-2" alt="">
        <input type="hidden" name="content[image]" value="{{ $c['image'] }}">
    @endif
    <input type="file" name="countdown_image" accept="image/*" class="text-sm">
</div>
```

Create `Modules/HomeContent/resources/views/forms/info_strip.blade.php`:

```blade
@php $items = $c['items'] ?? [['icon' => 'truck'], ['icon' => 'shield']]; @endphp
<p class="text-sm text-gray-500">2–4 small items, e.g. “Free shipping over €49”.</p>
<div id="info-items-rows" class="space-y-4">
    @foreach ($items as $i => $item)
        <div class="border border-gray-200 rounded-lg p-4 space-y-3">
            <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-slate-700">Icon</label>
                <select name="content[items][{{ $i }}][icon]" class="border-gray-300 rounded-lg text-sm">
                    @foreach (\Modules\HomeContent\Support\BlockContentRules::ICONS as $icon)
                        <option value="{{ $icon }}" @selected(($item['icon'] ?? '') === $icon)>{{ $icon }}</option>
                    @endforeach
                </select>
            </div>
            @include('homecontent::partials.i18n-input', ['label' => 'Item text', 'name' => "content[items][$i][text]", 'values' => $item['text'] ?? []])
            @include('homecontent::partials.link-picker', ['name' => "content[items][$i][link]", 'value' => $item['link'] ?? null])
        </div>
    @endforeach
</div>
<template data-repeater-for="info-items">
    <div class="border border-gray-200 rounded-lg p-4 space-y-3">
        <div class="flex items-center gap-3">
            <label class="text-sm font-medium text-slate-700">Icon</label>
            <select name="content[items][__IDX__][icon]" class="border-gray-300 rounded-lg text-sm">
                @foreach (\Modules\HomeContent\Support\BlockContentRules::ICONS as $icon)
                    <option value="{{ $icon }}">{{ $icon }}</option>
                @endforeach
            </select>
        </div>
        @include('homecontent::partials.i18n-input', ['label' => 'Item text', 'name' => 'content[items][__IDX__][text]', 'values' => []])
        @include('homecontent::partials.link-picker', ['name' => 'content[items][__IDX__][link]', 'value' => null])
    </div>
</template>
<button type="button" onclick="addRepeaterRow('info-items')" class="text-sm text-blue-600 hover:underline">+ Add item (max 4)</button>
```

Create `Modules/HomeContent/resources/views/forms/popup.blade.php`:

```blade
<div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Popup image (optional)</label>
    @if (!empty($c['image']))
        <img src="{{ \Modules\HomeContent\Support\ImageUrl::make($c['image']) }}" class="h-24 rounded mb-2" alt="">
        <input type="hidden" name="content[image]" value="{{ $c['image'] }}">
    @endif
    <input type="file" name="popup_image" accept="image/*" class="text-sm">
</div>
@include('homecontent::partials.i18n-input', ['label' => 'Title', 'name' => 'content[title]', 'values' => $c['title'] ?? []])
@include('homecontent::partials.i18n-input', ['label' => 'Text (optional)', 'name' => 'content[text]', 'values' => $c['text'] ?? []])
@include('homecontent::partials.i18n-input', ['label' => 'Button label (optional)', 'name' => 'content[button_label]', 'values' => $c['button_label'] ?? []])
@include('homecontent::partials.link-picker', ['name' => 'content[link]', 'value' => $c['link'] ?? null])
<div x-data="{ mode: '{{ $c['frequency']['mode'] ?? 'once_per_session' }}' }" class="flex flex-wrap items-end gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Show again</label>
        <select name="content[frequency][mode]" x-model="mode" class="border-gray-300 rounded-lg">
            <option value="once_per_session">Once per session</option>
            <option value="once_per_days">Once every N days</option>
        </select>
    </div>
    <div x-show="mode === 'once_per_days'">
        <label class="block text-sm font-medium text-slate-700 mb-1">Days</label>
        <input type="number" name="content[frequency][days]" min="1" max="90"
               value="{{ $c['frequency']['days'] ?? 7 }}" class="w-24 border-gray-300 rounded-lg">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Delay (seconds)</label>
        <input type="number" name="content[delay_seconds]" min="0" max="60"
               value="{{ $c['delay_seconds'] ?? 3 }}" class="w-24 border-gray-300 rounded-lg">
    </div>
</div>
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test --filter=HomeBlockAdminUiTest`
Expected: PASS (4 tests).

- [ ] **Step 6: Commit**

```bash
git add Modules/HomeContent/resources/views tests/Feature/HomeBlockAdminUiTest.php
git commit -m "feat(home): block form shell, i18n/link partials, text-block admin forms"
```

---

### Task 12: Media & product forms (hero slider, promo tiles, category grid, product rail)

**Files:**
- Create: `Modules/HomeContent/resources/views/forms/hero_slider.blade.php`, `forms/promo_tiles.blade.php`, `forms/category_grid.blade.php`, `forms/product_rail.blade.php`
- Test: extend `tests/Feature/HomeBlockAdminUiTest.php`

**Interfaces:**
- Consumes: partials + `linkPicker`/`addRepeaterRow` from Task 11; search + rail-preview endpoints from Task 9; input naming contract from Task 9 (`slide_images_web[i]`, `slide_images_app[i]`, `tile_images[i]`, `content[category_ids][]`, `content[product_ids][]`).
- Produces: the remaining four admin forms.

- [ ] **Step 1: Add failing render tests**

Append to `tests/Feature/HomeBlockAdminUiTest.php`:

```php
    public function test_create_forms_render_for_media_block_types(): void
    {
        foreach (['hero_slider', 'promo_tiles', 'category_grid', 'product_rail'] as $type) {
            $this->actingAs($this->admin())
                ->get(route('home-blocks.create', ['type' => $type]))
                ->assertOk()
                ->assertSee('name="name"', false);
        }
    }
```

Run: `php artisan test --filter=HomeBlockAdminUiTest`
Expected: FAIL — missing `homecontent::forms.hero_slider` etc.

- [ ] **Step 2: Hero slider form**

Create `Modules/HomeContent/resources/views/forms/hero_slider.blade.php`:

```blade
@php $slides = $c['slides'] ?? [[]]; @endphp
<p class="text-sm text-gray-500">Recommended sizes — web ≈ 1600×530 (3:1), app ≈ 800×400 (2:1). Each slide needs at least one image.</p>
<div id="slides-rows" class="space-y-4">
    @foreach ($slides as $i => $slide)
        <div class="border border-gray-200 rounded-lg p-4 space-y-3">
            <p class="text-xs font-semibold text-gray-400">Slide {{ $i + 1 }}</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Web image</label>
                    @if (!empty($slide['image_web']))
                        <img src="{{ \Modules\HomeContent\Support\ImageUrl::make($slide['image_web']) }}" class="h-16 rounded mb-2" alt="">
                        <input type="hidden" name="content[slides][{{ $i }}][image_web]" value="{{ $slide['image_web'] }}">
                    @endif
                    <input type="file" name="slide_images_web[{{ $i }}]" accept="image/*" class="text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">App image</label>
                    @if (!empty($slide['image_app']))
                        <img src="{{ \Modules\HomeContent\Support\ImageUrl::make($slide['image_app']) }}" class="h-16 rounded mb-2" alt="">
                        <input type="hidden" name="content[slides][{{ $i }}][image_app]" value="{{ $slide['image_app'] }}">
                    @endif
                    <input type="file" name="slide_images_app[{{ $i }}]" accept="image/*" class="text-sm">
                </div>
            </div>
            @include('homecontent::partials.i18n-input', ['label' => 'Title (optional)', 'name' => "content[slides][$i][title]", 'values' => $slide['title'] ?? []])
            @include('homecontent::partials.i18n-input', ['label' => 'Subtitle (optional)', 'name' => "content[slides][$i][subtitle]", 'values' => $slide['subtitle'] ?? []])
            @include('homecontent::partials.link-picker', ['name' => "content[slides][$i][link]", 'value' => $slide['link'] ?? null])
        </div>
    @endforeach
</div>
<template data-repeater-for="slides">
    <div class="border border-gray-200 rounded-lg p-4 space-y-3">
        <p class="text-xs font-semibold text-gray-400">New slide</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Web image</label>
                <input type="file" name="slide_images_web[__IDX__]" accept="image/*" class="text-sm">
                <input type="hidden" name="content[slides][__IDX__][image_web]" value="">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">App image</label>
                <input type="file" name="slide_images_app[__IDX__]" accept="image/*" class="text-sm">
            </div>
        </div>
        @include('homecontent::partials.i18n-input', ['label' => 'Title (optional)', 'name' => 'content[slides][__IDX__][title]', 'values' => []])
        @include('homecontent::partials.i18n-input', ['label' => 'Subtitle (optional)', 'name' => 'content[slides][__IDX__][subtitle]', 'values' => []])
        @include('homecontent::partials.link-picker', ['name' => 'content[slides][__IDX__][link]', 'value' => null])
    </div>
</template>
<button type="button" onclick="addRepeaterRow('slides')" class="text-sm text-blue-600 hover:underline">+ Add slide (max 8)</button>

<script>
    // Spec §5: warn when an uploaded image is far from the recommended aspect ratio.
    // Delegated so it also covers rows added by the repeater.
    document.addEventListener('change', (event) => {
        const input = event.target;
        if (input.type !== 'file' || !input.files?.[0]) return;
        const isWeb = input.name.startsWith('slide_images_web');
        const isApp = input.name.startsWith('slide_images_app');
        if (!isWeb && !isApp) return;

        const expected = isWeb ? 3 : 2; // web ≈ 3:1, app ≈ 2:1
        const img = new Image();
        img.onload = () => {
            const ratio = img.width / img.height;
            input.parentElement.querySelector('.ratio-warning')?.remove();
            if (Math.abs(ratio - expected) / expected > 0.25) {
                input.insertAdjacentHTML('afterend',
                    `<p class="ratio-warning text-xs text-amber-600 mt-1">⚠ This image is ${img.width}×${img.height} (${ratio.toFixed(1)}:1); recommended is ${expected}:1 — it may look cropped or stretched.</p>`);
            }
            URL.revokeObjectURL(img.src);
        };
        img.src = URL.createObjectURL(input.files[0]);
    });
</script>
```

- [ ] **Step 3: Promo tiles form**

Create `Modules/HomeContent/resources/views/forms/promo_tiles.blade.php`:

```blade
@php $tiles = $c['tiles'] ?? [[]]; @endphp
<p class="text-sm text-gray-500">1–3 image tiles shown side by side. Each tile needs an image.</p>
<div id="tiles-rows" class="space-y-4">
    @foreach ($tiles as $i => $tile)
        <div class="border border-gray-200 rounded-lg p-4 space-y-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tile image</label>
                @if (!empty($tile['image']))
                    <img src="{{ \Modules\HomeContent\Support\ImageUrl::make($tile['image']) }}" class="h-16 rounded mb-2" alt="">
                    <input type="hidden" name="content[tiles][{{ $i }}][image]" value="{{ $tile['image'] }}">
                @endif
                <input type="file" name="tile_images[{{ $i }}]" accept="image/*" class="text-sm">
            </div>
            @include('homecontent::partials.i18n-input', ['label' => 'Label (optional)', 'name' => "content[tiles][$i][label]", 'values' => $tile['label'] ?? []])
            @include('homecontent::partials.link-picker', ['name' => "content[tiles][$i][link]", 'value' => $tile['link'] ?? null])
        </div>
    @endforeach
</div>
<template data-repeater-for="tiles">
    <div class="border border-gray-200 rounded-lg p-4 space-y-3">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tile image</label>
            <input type="file" name="tile_images[__IDX__]" accept="image/*" class="text-sm">
            <input type="hidden" name="content[tiles][__IDX__][image]" value="">
        </div>
        @include('homecontent::partials.i18n-input', ['label' => 'Label (optional)', 'name' => 'content[tiles][__IDX__][label]', 'values' => []])
        @include('homecontent::partials.link-picker', ['name' => 'content[tiles][__IDX__][link]', 'value' => null])
    </div>
</template>
<button type="button" onclick="addRepeaterRow('tiles')" class="text-sm text-blue-600 hover:underline">+ Add tile (max 3)</button>
```

- [ ] **Step 4: Category grid form (sortable picked list + search)**

Create `Modules/HomeContent/resources/views/forms/category_grid.blade.php`:

```blade
@php
    $selectedIds = array_map('intval', $c['category_ids'] ?? []);
    $selected = \Modules\ProductManagement\Models\Category::whereIn('id', $selectedIds)->get()
        ->sortBy(fn ($cat) => array_search($cat->id, $selectedIds))->values();
@endphp
<p class="text-sm text-gray-500">Pick at least 2 categories; drag to set their order on the homepage.</p>
<ul id="picked-categories" class="space-y-1">
    @foreach ($selected as $category)
        <li class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded px-3 py-1.5 text-sm" data-id="{{ $category->id }}">
            <span class="drag-handle cursor-grab text-gray-400 select-none">&#8801;</span>
            <span class="flex-1">{{ $category->name_german ?: $category->name_arabic }}</span>
            <input type="hidden" name="content[category_ids][]" value="{{ $category->id }}">
            <button type="button" onclick="this.closest('li').remove()" class="text-red-500">&times;</button>
        </li>
    @endforeach
</ul>
<div x-data="{ q: '', results: [], async search() {
        if (this.q.length < 2) { this.results = []; return; }
        const res = await fetch('{{ route('home-blocks.search.categories') }}?q=' + encodeURIComponent(this.q));
        this.results = (await res.json()).data;
    } }">
    <input type="text" x-model="q" @input.debounce.300ms="search()" placeholder="Search categories…"
           class="w-full border-gray-300 rounded-lg mt-2">
    <div x-show="results.length" class="border border-gray-200 rounded-lg bg-white mt-1">
        <template x-for="result in results" :key="result.id">
            <button type="button" class="block w-full text-start px-3 py-1.5 text-sm hover:bg-gray-50"
                    @click="addPickedCategory(result); results = []; q = '';"
                    x-text="(result.name_german || result.name_arabic) + ' (#' + result.id + ')'"></button>
        </template>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    new Sortable(document.getElementById('picked-categories'), { handle: '.drag-handle', animation: 150 });

    function addPickedCategory(category) {
        if (document.querySelector(`#picked-categories [data-id="${category.id}"]`)) return;
        document.getElementById('picked-categories').insertAdjacentHTML('beforeend', `
            <li class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded px-3 py-1.5 text-sm" data-id="${category.id}">
                <span class="drag-handle cursor-grab text-gray-400 select-none">&#8801;</span>
                <span class="flex-1">${category.name_german || category.name_arabic}</span>
                <input type="hidden" name="content[category_ids][]" value="${category.id}">
                <button type="button" onclick="this.closest('li').remove()" class="text-red-500">&times;</button>
            </li>`);
    }
</script>
```

- [ ] **Step 5: Product rail form (rule picker + manual list + live preview)**

Create `Modules/HomeContent/resources/views/forms/product_rail.blade.php`:

```blade
@include('homecontent::partials.i18n-input', ['label' => 'Rail title', 'name' => 'content[title]', 'values' => $c['title'] ?? []])

<div x-data="{ rule: '{{ $c['rule'] ?? 'newest' }}' }" class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Products come from</label>
        <div class="flex flex-wrap gap-4 text-sm">
            @foreach (['newest' => 'Newest products', 'best_sellers' => 'Best sellers', 'category' => 'A category', 'manual' => 'Hand-picked'] as $value => $label)
                <label class="inline-flex items-center gap-1.5">
                    <input type="radio" name="content[rule]" value="{{ $value }}" x-model="rule"> {{ $label }}
                </label>
            @endforeach
        </div>
    </div>

    <div x-show="rule === 'category'"
         x-data="{ q: '', results: [], picked: '{{ $c['category_id'] ?? '' }}', label: '', async search() {
            if (this.q.length < 2) { this.results = []; return; }
            const res = await fetch('{{ route('home-blocks.search.categories') }}?q=' + encodeURIComponent(this.q));
            this.results = (await res.json()).data;
         } }">
        <input type="hidden" name="content[category_id]" :value="picked">
        <input type="text" x-model="q" @input.debounce.300ms="search()" placeholder="Search categories…"
               class="w-full border-gray-300 rounded-lg">
        <div x-show="results.length" class="border border-gray-200 rounded-lg bg-white mt-1">
            <template x-for="result in results" :key="result.id">
                <button type="button" class="block w-full text-start px-3 py-1.5 text-sm hover:bg-gray-50"
                        @click="picked = result.id; label = result.name_german || result.name_arabic; results = []; q = '';"
                        x-text="(result.name_german || result.name_arabic) + ' (#' + result.id + ')'"></button>
            </template>
        </div>
        <p class="text-xs text-gray-500 mt-1" x-show="label" x-text="'Category: ' + label"></p>
    </div>

    <div x-show="rule === 'manual'">
        @php
            $pickedIds = array_map('intval', $c['product_ids'] ?? []);
            $pickedProducts = \Modules\ProductManagement\Models\Product::whereIn('id', $pickedIds)->get()
                ->sortBy(fn ($p) => array_search($p->id, $pickedIds))->values();
        @endphp
        <ul id="picked-products" class="space-y-1">
            @foreach ($pickedProducts as $product)
                <li class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded px-3 py-1.5 text-sm" data-id="{{ $product->id }}">
                    <span class="drag-handle cursor-grab text-gray-400 select-none">&#8801;</span>
                    <span class="flex-1">{{ $product->name_german ?: $product->name_arabic }}</span>
                    <input type="hidden" name="content[product_ids][]" value="{{ $product->id }}">
                    <button type="button" onclick="this.closest('li').remove()" class="text-red-500">&times;</button>
                </li>
            @endforeach
        </ul>
        <div x-data="{ q: '', results: [], async search() {
                if (this.q.length < 2) { this.results = []; return; }
                const res = await fetch('{{ route('home-blocks.search.products') }}?q=' + encodeURIComponent(this.q));
                this.results = (await res.json()).data;
            } }">
            <input type="text" x-model="q" @input.debounce.300ms="search()" placeholder="Search products…"
                   class="w-full border-gray-300 rounded-lg mt-2">
            <div x-show="results.length" class="border border-gray-200 rounded-lg bg-white mt-1">
                <template x-for="result in results" :key="result.id">
                    <button type="button" class="block w-full text-start px-3 py-1.5 text-sm hover:bg-gray-50"
                            @click="addPickedProduct(result); results = []; q = '';"
                            x-text="(result.name_german || result.name_arabic) + ' (#' + result.id + ')'"></button>
                </template>
            </div>
        </div>
    </div>

    <div class="flex items-end gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Max products</label>
            <input type="number" name="content[limit]" min="2" max="24" value="{{ $c['limit'] ?? 12 }}"
                   class="w-24 border-gray-300 rounded-lg">
        </div>
        <button type="button" onclick="railPreview()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm">
            Preview products
        </button>
    </div>
    <div id="rail-preview" class="flex gap-3 flex-wrap"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    const pickedProductsList = document.getElementById('picked-products');
    if (pickedProductsList) new Sortable(pickedProductsList, { handle: '.drag-handle', animation: 150 });

    function addPickedProduct(product) {
        if (document.querySelector(`#picked-products [data-id="${product.id}"]`)) return;
        pickedProductsList.insertAdjacentHTML('beforeend', `
            <li class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded px-3 py-1.5 text-sm" data-id="${product.id}">
                <span class="drag-handle cursor-grab text-gray-400 select-none">&#8801;</span>
                <span class="flex-1">${product.name_german || product.name_arabic}</span>
                <input type="hidden" name="content[product_ids][]" value="${product.id}">
                <button type="button" onclick="this.closest('li').remove()" class="text-red-500">&times;</button>
            </li>`);
    }

    async function railPreview() {
        const form = document.querySelector('form');
        const body = {
            rule: form.querySelector('input[name="content[rule]"]:checked').value,
            category_id: form.querySelector('input[name="content[category_id]"]')?.value || null,
            product_ids: [...form.querySelectorAll('input[name="content[product_ids][]"]')].map(el => Number(el.value)),
        };
        const res = await fetch('{{ route('home-blocks.rail-preview') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
            body: JSON.stringify(body),
        });
        const json = await res.json();
        document.getElementById('rail-preview').innerHTML = json.data.length
            ? json.data.map(p => `
                <div class="w-28 text-xs text-center">
                    <div class="h-28 bg-gray-100 rounded overflow-hidden mb-1">
                        ${p.image ? `<img src="${p.image}" class="w-full h-full object-cover" alt="">` : ''}
                    </div>
                    <div class="truncate">${p.name_german || p.name_arabic}</div>
                    <div class="font-semibold">€${p.min_price}</div>
                </div>`).join('')
            : '<p class="text-sm text-red-600">No products match this rule yet — the rail would be hidden on the homepage.</p>';
    }
</script>
```

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test --filter=HomeBlockAdminUiTest`
Expected: PASS (5 tests).

- [ ] **Step 7: Commit**

```bash
git add Modules/HomeContent/resources/views/forms tests/Feature/HomeBlockAdminUiTest.php
git commit -m "feat(home): hero, promo-tiles, category-grid and product-rail admin forms"
```

---

### Task 13: Full suite, manual smoke check, deployment notes

**Files:**
- Modify: `DEPLOYMENT.md` (repo root `C:\xampp\htdocs\Narzin\DEPLOYMENT.md`)

- [ ] **Step 1: Run the entire backend test suite**

Run: `php artisan test`
Expected: PASS — zero failures, including all pre-existing tests (Promotion*, Checkout, RoleSeparation, etc.). Fix any regression before proceeding; do not skip tests.

- [ ] **Step 2: Manual smoke check (local)**

With XAMPP MySQL running and `php artisan serve` (or the XAMPP vhost):
1. `php artisan storage:link` (if not already linked) and set `HOME_PREVIEW_TOKEN=<random string>` in `.env`.
2. `php artisan migrate` then `php artisan home:migrate-legacy` — expect "Created N home blocks".
3. Log into the admin panel → sidebar shows **Homepage** → builder lists the migrated blocks.
4. Create one block of each of the 8 types (upload real images); drag-reorder; toggle one off; edit one.
5. `GET /api/v1/home?platform=web&locale=ar` and `?platform=app&locale=de` in the browser — confirm resolved JSON, correct languages, absolute image URLs.
6. `GET /api/v1/banners/web` and `/api/v1/before-nav/current` — confirm the legacy web storefront still shows its announcement bar (it reads `.text`).

- [ ] **Step 3: Document deployment steps**

Append to `DEPLOYMENT.md` a short "Homepage Builder (Phase 1)" section:

```markdown
## Homepage Builder (Phase 1)

Release steps for the HomeContent module:

1. `php artisan migrate` (creates `home_blocks`).
2. Set in `.env`: `HOME_PREVIEW_TOKEN` (any long random string) and `STOREFRONT_URL` (the React storefront origin, e.g. https://narzin.com).
3. `php artisan home:migrate-legacy` — one-time conversion of `banners` + `before_nav` rows into blocks (idempotent).
4. `php artisan config:clear && php artisan route:clear && php artisan cache:clear`.
5. Verify `GET /api/v1/home?platform=web`, `GET /api/v1/banners/mobile`, `GET /api/v1/before-nav/current`.

The legacy `banners`/`before_nav` tables and endpoints stay until Phase 4 cleanup.
```

- [ ] **Step 4: Commit**

```bash
git add ../DEPLOYMENT.md
git commit -m "docs(home): phase 1 deployment steps for homepage builder"
```

---

## Out of scope for this plan

- **Phase 2** (React storefront: `homeSlice`, `BlockRenderer`, 8 block components, Shein-density redesign, RTL/skeletons/fallback) — separate plan, written after Phase 1 ships so it can code against real API responses.
- **Phase 3** (Flutter: `HomeCubit`, block models/widgets) — separate plan.
- **Phase 4** (drop legacy tables/endpoints/admin views) — separate small plan.
- Rail rule `biggest_discount` — deferred per spec amendment (no catalog-level discounts exist yet).

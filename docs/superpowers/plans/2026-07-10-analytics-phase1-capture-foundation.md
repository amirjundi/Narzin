# Analytics Phase 1 — Capture Foundation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stand up the behavioral capture layer (sessions + cart/checkout/search events) so no data is lost while later reporting phases are built.

**Architecture:** Four typed tables and Eloquent models live in the existing `Telemetry` module. A single `CaptureService` owns every write and swallows all exceptions (capture must never break checkout/search/login). Most capture is server-side (search, checkout steps, login backfill); two thin endpoints (`POST /api/v1/track/cart`, `POST /api/v1/track/session`) accept the only client-side signals.

**Tech Stack:** Laravel 11, nwidart/laravel-modules, Sanctum, PHPUnit 11, SQLite test DB with `RefreshDatabase`.

## Global Constraints

- Money columns: `decimal(12,2)`. (from spec)
- Capture is **best-effort and non-blocking**: every `CaptureService` method wraps its work in `try/catch (\Throwable)`, logs a warning, and returns — it must never throw. (from spec)
- `occurred_at` (event clock) is a distinct column from `created_at` (row insert). (from spec)
- `session_id` (string, client UUID) is the join key across all tables. (from spec)
- New capture code lives in `Modules/Telemetry`. (from spec)
- Follow the existing Telemetry pattern: `auth('sanctum')->id()` yields the user id or `null` for guests; tracking routes are **not** behind auth middleware. (from `Modules/Telemetry/routes/api.php`)

---

### Task 1: Typed tables + models

**Files:**
- Create: `Modules/Telemetry/database/migrations/2026_07_10_000001_create_visit_sessions_table.php`
- Create: `Modules/Telemetry/database/migrations/2026_07_10_000002_create_cart_events_table.php`
- Create: `Modules/Telemetry/database/migrations/2026_07_10_000003_create_checkout_events_table.php`
- Create: `Modules/Telemetry/database/migrations/2026_07_10_000004_create_search_logs_table.php`
- Create: `Modules/Telemetry/app/Models/VisitSession.php`
- Create: `Modules/Telemetry/app/Models/CartEvent.php`
- Create: `Modules/Telemetry/app/Models/CheckoutEvent.php`
- Create: `Modules/Telemetry/app/Models/SearchLog.php`
- Test: `tests/Feature/Telemetry/CaptureSchemaTest.php`

**Interfaces:**
- Produces models `Modules\Telemetry\Models\{VisitSession, CartEvent, CheckoutEvent, SearchLog}` with the `$fillable` fields listed below. Task 2 consumes all four.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Telemetry/CaptureSchemaTest.php`:

```php
<?php

namespace Tests\Feature\Telemetry;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Telemetry\Models\CartEvent;
use Modules\Telemetry\Models\CheckoutEvent;
use Modules\Telemetry\Models\SearchLog;
use Modules\Telemetry\Models\VisitSession;
use Tests\TestCase;

class CaptureSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_four_capture_tables_accept_rows(): void
    {
        VisitSession::create([
            'session_id' => 'sess-1', 'utm_source' => 'google',
            'first_seen_at' => now(), 'last_seen_at' => now(),
        ]);
        CartEvent::create([
            'session_id' => 'sess-1', 'product_id' => 1, 'action' => 'add',
            'quantity' => 2, 'unit_price' => 9.99, 'occurred_at' => now(),
        ]);
        CheckoutEvent::create([
            'session_id' => 'sess-1', 'step' => 'checkout_start', 'occurred_at' => now(),
        ]);
        SearchLog::create([
            'query' => 'Blue Shirt', 'normalized_query' => 'blue shirt',
            'results_count' => 3, 'occurred_at' => now(),
        ]);

        $this->assertDatabaseHas('visit_sessions', ['session_id' => 'sess-1', 'utm_source' => 'google']);
        $this->assertDatabaseHas('cart_events', ['session_id' => 'sess-1', 'action' => 'add', 'quantity' => 2]);
        $this->assertDatabaseHas('checkout_events', ['step' => 'checkout_start']);
        $this->assertDatabaseHas('search_logs', ['normalized_query' => 'blue shirt', 'results_count' => 3]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CaptureSchemaTest`
Expected: FAIL — `Class "Modules\Telemetry\Models\VisitSession" not found`.

- [ ] **Step 3: Write the migrations**

`Modules/Telemetry/database/migrations/2026_07_10_000001_create_visit_sessions_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('referrer')->nullable();
            $table->string('landing_url')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_sessions');
    }
};
```

`Modules/Telemetry/database/migrations/2026_07_10_000002_create_cart_events_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_events', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->enum('action', ['add', 'remove', 'update']);
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_events');
    }
};
```

`Modules/Telemetry/database/migrations/2026_07_10_000003_create_checkout_events_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_events', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('step', ['checkout_start', 'address', 'shipping', 'payment', 'placed']);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_events');
    }
};
```

`Modules/Telemetry/database/migrations/2026_07_10_000004_create_search_logs_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('query');
            $table->string('normalized_query')->index();
            $table->integer('results_count')->default(0);
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_logs');
    }
};
```

- [ ] **Step 4: Write the four models**

`Modules/Telemetry/app/Models/VisitSession.php`:

```php
<?php

namespace Modules\Telemetry\Models;

use Illuminate\Database\Eloquent\Model;

class VisitSession extends Model
{
    protected $table = 'visit_sessions';

    protected $fillable = [
        'session_id', 'user_id',
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
        'referrer', 'landing_url', 'first_seen_at', 'last_seen_at',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];
}
```

`Modules/Telemetry/app/Models/CartEvent.php`:

```php
<?php

namespace Modules\Telemetry\Models;

use Illuminate\Database\Eloquent\Model;

class CartEvent extends Model
{
    protected $table = 'cart_events';

    protected $fillable = [
        'session_id', 'user_id', 'product_id', 'variant_id',
        'action', 'quantity', 'unit_price', 'occurred_at',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];
}
```

`Modules/Telemetry/app/Models/CheckoutEvent.php`:

```php
<?php

namespace Modules\Telemetry\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutEvent extends Model
{
    protected $table = 'checkout_events';

    protected $fillable = [
        'session_id', 'user_id', 'step', 'order_id', 'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];
}
```

`Modules/Telemetry/app/Models/SearchLog.php`:

```php
<?php

namespace Modules\Telemetry\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    protected $table = 'search_logs';

    protected $fillable = [
        'session_id', 'user_id', 'query', 'normalized_query',
        'results_count', 'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=CaptureSchemaTest`
Expected: PASS (1 test).

- [ ] **Step 6: Commit**

```bash
git add Modules/Telemetry/database/migrations tests/Feature/Telemetry/CaptureSchemaTest.php Modules/Telemetry/app/Models
git commit -m "feat(telemetry): capture tables + models for sessions/cart/checkout/search"
```

---

### Task 2: CaptureService

**Files:**
- Create: `Modules/Telemetry/app/Services/CaptureService.php`
- Test: `tests/Feature/Telemetry/CaptureServiceTest.php`

**Interfaces:**
- Consumes: the four models from Task 1.
- Produces `Modules\Telemetry\Services\CaptureService` with these **static** methods (Tasks 3–6 call them):
  - `recordSession(string $sessionId, ?int $userId, array $attribution): void` — `$attribution` may contain keys `utm_source, utm_medium, utm_campaign, utm_term, utm_content, referrer, landing_url`. Sets attribution + `first_seen_at` **only on first touch**; always updates `last_seen_at` and sets `user_id` when non-null.
  - `backfillUser(string $sessionId, int $userId): void`
  - `recordCartEvent(string $sessionId, ?int $userId, int $productId, ?int $variantId, string $action, int $quantity, ?float $unitPrice): void`
  - `recordCheckoutEvent(?string $sessionId, ?int $userId, string $step, ?int $orderId): void`
  - `recordSearch(?string $sessionId, ?int $userId, string $query, int $resultsCount): void` — normalizes to `mb_strtolower(trim($query))`; a blank normalized query writes nothing.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Telemetry/CaptureServiceTest.php`:

```php
<?php

namespace Tests\Feature\Telemetry;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Telemetry\Models\VisitSession;
use Modules\Telemetry\Services\CaptureService;
use Tests\TestCase;

class CaptureServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_search_normalizes_and_stores_count(): void
    {
        CaptureService::recordSearch('sess-1', null, '  Blue SHIRT ', 4);

        $this->assertDatabaseHas('search_logs', [
            'session_id' => 'sess-1',
            'query' => 'Blue SHIRT',
            'normalized_query' => 'blue shirt',
            'results_count' => 4,
        ]);
    }

    public function test_record_search_ignores_blank_query(): void
    {
        CaptureService::recordSearch('sess-1', null, '   ', 0);
        $this->assertDatabaseCount('search_logs', 0);
    }

    public function test_record_cart_event_stores_row(): void
    {
        CaptureService::recordCartEvent('sess-1', null, 7, 12, 'add', 3, 19.50);

        $this->assertDatabaseHas('cart_events', [
            'session_id' => 'sess-1', 'product_id' => 7, 'variant_id' => 12,
            'action' => 'add', 'quantity' => 3,
        ]);
    }

    public function test_record_checkout_event_stores_row(): void
    {
        CaptureService::recordCheckoutEvent('sess-1', 5, 'placed', 99);

        $this->assertDatabaseHas('checkout_events', [
            'session_id' => 'sess-1', 'user_id' => 5, 'step' => 'placed', 'order_id' => 99,
        ]);
    }

    public function test_record_session_sets_attribution_only_on_first_touch(): void
    {
        CaptureService::recordSession('sess-1', null, ['utm_source' => 'google']);
        CaptureService::recordSession('sess-1', 42, ['utm_source' => 'facebook']); // later touch

        $this->assertDatabaseCount('visit_sessions', 1);
        $session = VisitSession::where('session_id', 'sess-1')->first();
        $this->assertSame('google', $session->utm_source); // first touch wins
        $this->assertSame(42, $session->user_id);          // user backfilled on later touch
    }

    public function test_backfill_user_sets_user_on_null_session(): void
    {
        CaptureService::recordSession('sess-1', null, []);
        CaptureService::backfillUser('sess-1', 77);

        $this->assertDatabaseHas('visit_sessions', ['session_id' => 'sess-1', 'user_id' => 77]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CaptureServiceTest`
Expected: FAIL — `Class "Modules\Telemetry\Services\CaptureService" not found`.

- [ ] **Step 3: Write the service**

`Modules/Telemetry/app/Services/CaptureService.php`:

```php
<?php

namespace Modules\Telemetry\Services;

use Illuminate\Support\Facades\Log;
use Modules\Telemetry\Models\CartEvent;
use Modules\Telemetry\Models\CheckoutEvent;
use Modules\Telemetry\Models\SearchLog;
use Modules\Telemetry\Models\VisitSession;

/**
 * Best-effort behavioral capture. Every method swallows exceptions (logs and
 * continues) so a capture failure can NEVER break checkout, search, or login.
 */
class CaptureService
{
    public static function recordSession(string $sessionId, ?int $userId, array $attribution): void
    {
        try {
            $session = VisitSession::firstOrNew(['session_id' => $sessionId]);
            if (!$session->exists) {
                $session->utm_source   = $attribution['utm_source']   ?? null;
                $session->utm_medium   = $attribution['utm_medium']   ?? null;
                $session->utm_campaign = $attribution['utm_campaign'] ?? null;
                $session->utm_term     = $attribution['utm_term']     ?? null;
                $session->utm_content  = $attribution['utm_content']  ?? null;
                $session->referrer     = $attribution['referrer']     ?? null;
                $session->landing_url  = $attribution['landing_url']  ?? null;
                $session->first_seen_at = now();
            }
            if ($userId !== null) {
                $session->user_id = $userId;
            }
            $session->last_seen_at = now();
            $session->save();
        } catch (\Throwable $e) {
            Log::warning('CaptureService::recordSession failed', ['error' => $e->getMessage()]);
        }
    }

    public static function backfillUser(string $sessionId, int $userId): void
    {
        try {
            VisitSession::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->update(['user_id' => $userId, 'last_seen_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('CaptureService::backfillUser failed', ['error' => $e->getMessage()]);
        }
    }

    public static function recordCartEvent(string $sessionId, ?int $userId, int $productId, ?int $variantId, string $action, int $quantity, ?float $unitPrice): void
    {
        try {
            CartEvent::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'action' => $action,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('CaptureService::recordCartEvent failed', ['error' => $e->getMessage()]);
        }
    }

    public static function recordCheckoutEvent(?string $sessionId, ?int $userId, string $step, ?int $orderId): void
    {
        try {
            CheckoutEvent::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'step' => $step,
                'order_id' => $orderId,
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('CaptureService::recordCheckoutEvent failed', ['error' => $e->getMessage()]);
        }
    }

    public static function recordSearch(?string $sessionId, ?int $userId, string $query, int $resultsCount): void
    {
        try {
            $normalized = mb_strtolower(trim($query));
            if ($normalized === '') {
                return;
            }
            SearchLog::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'query' => mb_substr(trim($query), 0, 255),
                'normalized_query' => mb_substr($normalized, 0, 255),
                'results_count' => $resultsCount,
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('CaptureService::recordSearch failed', ['error' => $e->getMessage()]);
        }
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=CaptureServiceTest`
Expected: PASS (6 tests).

- [ ] **Step 5: Commit**

```bash
git add Modules/Telemetry/app/Services/CaptureService.php tests/Feature/Telemetry/CaptureServiceTest.php
git commit -m "feat(telemetry): non-blocking CaptureService for behavioral events"
```

---

### Task 3: Client tracking endpoints (cart + session)

**Files:**
- Create: `Modules/Telemetry/app/Http/Controllers/TrackingController.php`
- Modify: `Modules/Telemetry/routes/api.php`
- Test: `tests/Feature/Telemetry/TrackingEndpointTest.php`

**Interfaces:**
- Consumes: `CaptureService` (Task 2).
- Produces routes `POST /api/v1/track/cart` and `POST /api/v1/track/session`. Both return HTTP 200 always (invalid payload → 200 with nothing captured — the non-blocking contract).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Telemetry/TrackingEndpointTest.php`:

```php
<?php

namespace Tests\Feature\Telemetry;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_track_cart_writes_a_cart_event(): void
    {
        $response = $this->postJson('/api/v1/track/cart', [
            'session_id' => 'sess-abc',
            'product_id' => 3,
            'action' => 'add',
            'quantity' => 2,
            'unit_price' => 12.00,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cart_events', [
            'session_id' => 'sess-abc', 'product_id' => 3, 'action' => 'add', 'quantity' => 2,
        ]);
    }

    public function test_malformed_cart_payload_is_acknowledged_but_not_stored(): void
    {
        $response = $this->postJson('/api/v1/track/cart', [
            'session_id' => 'sess-abc',
            'action' => 'teleport', // invalid enum, product_id missing
        ]);

        $response->assertStatus(200); // non-blocking: never error the client
        $this->assertDatabaseCount('cart_events', 0);
    }

    public function test_track_session_records_attribution(): void
    {
        $response = $this->postJson('/api/v1/track/session', [
            'session_id' => 'sess-xyz',
            'utm_source' => 'newsletter',
            'utm_campaign' => 'july_sale',
            'referrer' => 'https://example.com',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('visit_sessions', [
            'session_id' => 'sess-xyz', 'utm_source' => 'newsletter', 'utm_campaign' => 'july_sale',
        ]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TrackingEndpointTest`
Expected: FAIL — 404 (route not defined), assertions fail.

- [ ] **Step 3: Write the controller**

`Modules/Telemetry/app/Http/Controllers/TrackingController.php`:

```php
<?php

namespace Modules\Telemetry\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Telemetry\Services\CaptureService;

class TrackingController extends Controller
{
    /** Thin client hook: cart add/remove/update. Always 200 (non-blocking). */
    public function cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string|max:255',
            'product_id' => 'required|integer',
            'variant_id' => 'nullable|integer',
            'action' => 'required|in:add,remove,update',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'ignored'], 200);
        }

        CaptureService::recordCartEvent(
            $request->input('session_id'),
            auth('sanctum')->id(),
            (int) $request->input('product_id'),
            $request->input('variant_id') !== null ? (int) $request->input('variant_id') : null,
            $request->input('action'),
            (int) $request->input('quantity'),
            $request->input('unit_price') !== null ? (float) $request->input('unit_price') : null,
        );

        return response()->json(['message' => 'ok'], 200);
    }

    /** Thin client hook: session bootstrap + UTM/referrer. Always 200. */
    public function session(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'ignored'], 200);
        }

        CaptureService::recordSession(
            $request->input('session_id'),
            auth('sanctum')->id(),
            $request->only(['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'referrer', 'landing_url']),
        );

        return response()->json(['message' => 'ok'], 200);
    }
}
```

- [ ] **Step 4: Add the routes**

In `Modules/Telemetry/routes/api.php`, add the import near the existing `use` line and append the route group. The file becomes:

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Telemetry\Http\Controllers\TelemetryController;
use Modules\Telemetry\Http\Controllers\TrackingController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 */

// We want this endpoint to be accessible to both guests and authenticated users.
// Sanctum middleware is not required because we handle null user_id in the controller for guests.
Route::prefix('v1/telemetry')->group(function () {
    Route::post('/view', [TelemetryController::class, 'trackView']);
});

// Behavioral capture — guest-friendly, always 200 (non-blocking).
Route::prefix('v1/track')->group(function () {
    Route::post('/cart', [TrackingController::class, 'cart']);
    Route::post('/session', [TrackingController::class, 'session']);
});
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=TrackingEndpointTest`
Expected: PASS (3 tests).

- [ ] **Step 6: Commit**

```bash
git add Modules/Telemetry/app/Http/Controllers/TrackingController.php Modules/Telemetry/routes/api.php tests/Feature/Telemetry/TrackingEndpointTest.php
git commit -m "feat(telemetry): /track/cart and /track/session client hooks"
```

---

### Task 4: Server-side search capture

**Files:**
- Modify: `Modules/ProductManagement/app/Http/Controllers/V1/Api/ProductController.php` (`index` ~line 106, `search` — after its `$products = $query->paginate(...)`)
- Test: `tests/Feature/Telemetry/SearchCaptureTest.php`

**Interfaces:**
- Consumes: `CaptureService::recordSearch` (Task 2).
- Produces: a `search_logs` row per search request that carries a `search` term, on both `GET /api/v1/products` and `GET /api/v1/products/search`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Telemetry/SearchCaptureTest.php`:

```php
<?php

namespace Tests\Feature\Telemetry;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Tests\TestCase;

class SearchCaptureTest extends TestCase
{
    use RefreshDatabase;

    private function seedProduct(string $nameDe): void
    {
        $cat = Category::create([
            'name_arabic' => 'فئة', 'name_german' => 'Kategorie',
            'slug_arabic' => 'c-ar-' . uniqid(), 'slug_german' => 'c-de-' . uniqid(),
        ]);
        Product::create([
            'name_arabic' => 'منتج', 'name_german' => $nameDe,
            'slug_arabic' => 'p-ar-' . uniqid(), 'slug_german' => 'p-de-' . uniqid(),
            'category_id' => $cat->id, 'is_active' => true,
        ]);
    }

    public function test_search_request_logs_query_and_result_count(): void
    {
        $this->seedProduct('Blue Running Shirt');

        $this->getJson('/api/v1/products/search?search=shirt&session_id=sess-1')
            ->assertStatus(200);

        $this->assertDatabaseHas('search_logs', [
            'session_id' => 'sess-1',
            'normalized_query' => 'shirt',
            'results_count' => 1,
        ]);
    }

    public function test_zero_result_search_is_logged_with_zero_count(): void
    {
        $this->seedProduct('Blue Running Shirt');

        $this->getJson('/api/v1/products/search?search=nonexistentxyz')
            ->assertStatus(200);

        $this->assertDatabaseHas('search_logs', [
            'normalized_query' => 'nonexistentxyz',
            'results_count' => 0,
        ]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SearchCaptureTest`
Expected: FAIL — no `search_logs` rows written.

- [ ] **Step 3: Add capture to `index`**

In `ProductController::index`, immediately after the line `$products = $query->paginate($request->get('per_page', 15));` (~line 106), insert:

```php
            if ($request->filled('search')) {
                \Modules\Telemetry\Services\CaptureService::recordSearch(
                    $request->query('session_id'),
                    auth('sanctum')->id(),
                    (string) $request->search,
                    $products->total(),
                );
            }
```

- [ ] **Step 4: Add capture to `search`**

In `ProductController::search`, find its results line `$products = $query->paginate(...)` (the paginate call near the end of the method, before the collection transform / return) and insert the **same** block immediately after it:

```php
            if ($request->filled('search')) {
                \Modules\Telemetry\Services\CaptureService::recordSearch(
                    $request->query('session_id'),
                    auth('sanctum')->id(),
                    (string) $request->search,
                    $products->total(),
                );
            }
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=SearchCaptureTest`
Expected: PASS (2 tests).

- [ ] **Step 6: Commit**

```bash
git add Modules/ProductManagement/app/Http/Controllers/V1/Api/ProductController.php tests/Feature/Telemetry/SearchCaptureTest.php
git commit -m "feat(telemetry): log search queries + result counts (index + search)"
```

---

### Task 5: Server-side checkout capture

**Files:**
- Modify: `Modules/Checkout/app/Http/Controllers/V1/Api/CheckoutController.php` (`placeOrder` — entry ~line 48; after the post-order `DB::commit()` ~line 357)
- Test: `tests/Feature/Telemetry/CheckoutCaptureTest.php`

**Interfaces:**
- Consumes: `CaptureService::recordCheckoutEvent` (Task 2).
- Produces: a `checkout_start` event at the start of every `placeOrder` call and a `placed` event (carrying `order_id`) after a successful order commit.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Telemetry/CheckoutCaptureTest.php`. It reuses the fixture pattern from `tests/Feature/Checkout/PlaceOrderTest.php` and mocks the payment gateway:

```php
<?php

namespace Tests\Feature\Telemetry;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Services\NassPaymentService;
use Tests\TestCase;

class CheckoutCaptureTest extends TestCase
{
    use RefreshDatabase;

    public function test_place_order_records_checkout_start_and_placed(): void
    {
        $user = User::factory()->create();

        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id, 'address' => '123 Test Street',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $vendorId = DB::table('vendors')->insertGetId([
            'store_name_in_arabic' => 'متجر', 'store_name_in_german' => 'Laden',
            'user_id' => User::factory()->create()->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'name_arabic' => 'فئة', 'name_german' => 'Kategorie',
            'slug_arabic' => 'cat-ar', 'slug_german' => 'cat-de',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $productId = DB::table('products')->insertGetId([
            'name_arabic' => 'منتج', 'name_german' => 'Produkt',
            'slug_arabic' => 'prod-ar', 'slug_german' => 'prod-de',
            'category_id' => $categoryId, 'vendor_id' => $vendorId,
            'is_active' => true, 'weight' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $variantId = DB::table('product_variants')->insertGetId([
            'product_id' => $productId, 'price' => 100, 'stock' => 10,
            'sku' => 'SKU-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $zoneId = DB::table('delivery_zones')->insertGetId([
            'name_english' => 'Zone', 'name_german' => 'Zone', 'name_arabic' => 'Zone',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $methodId = DB::table('delivery_methods')->insertGetId([
            'delivery_zone_id' => $zoneId,
            'name_english' => 'Standard', 'name_german' => 'Standard', 'name_arabic' => 'Standard',
            'base_price' => 5, 'price_per_kg' => 0, 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('cart')->insert([
            'user_id' => $user->id, 'product_id' => $productId, 'product_variant_id' => $variantId,
            'quantity' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        // Mock the external Nass gateway so the test never makes a network call
        // (matches tests/Feature/Checkout/PlaceOrderTest.php).
        $this->mock(NassPaymentService::class, function ($mock) {
            $mock->shouldReceive('createTransaction')->once()->andReturn([
                'success' => true,
                'data' => ['url' => 'https://pay.example/redirect', 'transactionParams' => []],
            ]);
        });

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/place-order', [
            'session_id' => 'sess-co',
            'address_id' => $addressId,
            'delivery_method_id' => $methodId,
        ])->assertStatus(200);

        $this->assertDatabaseHas('checkout_events', ['session_id' => 'sess-co', 'step' => 'checkout_start']);
        $this->assertDatabaseHas('checkout_events', ['step' => 'placed']);
        $placed = DB::table('checkout_events')->where('step', 'placed')->first();
        $this->assertNotNull($placed->order_id);
    }
}
```

> Note: request body is just `address_id` + `delivery_method_id` (the seeded `cart` row drives the rest); the gateway mock is `createTransaction`. This mirrors `tests/Feature/Checkout/PlaceOrderTest.php::test_successful_order_deducts_stock_and_clears_cart` — consult it if the fixture graph needs adjusting. `session_id` is passed through unvalidated and read via `$request->input('session_id')`.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CheckoutCaptureTest`
Expected: FAIL — no `checkout_events` rows.

- [ ] **Step 3: Record `checkout_start` at method entry**

In `CheckoutController::placeOrder`, as the **first statement inside the opening `try` block** (right after `placeOrder` begins, ~line 48–50), insert:

```php
            \Modules\Telemetry\Services\CaptureService::recordCheckoutEvent(
                $request->input('session_id'),
                auth('sanctum')->id(),
                'checkout_start',
                null,
            );
```

- [ ] **Step 4: Record `placed` after the successful commit**

In `CheckoutController::placeOrder`, immediately after the `DB::commit();` that follows `$order = Order::create([...])` (~line 357, the commit on the happy path where `$order` is defined), insert:

```php
            \Modules\Telemetry\Services\CaptureService::recordCheckoutEvent(
                $request->input('session_id'),
                auth('sanctum')->id(),
                'placed',
                $order->id,
            );
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=CheckoutCaptureTest`
Expected: PASS (1 test).

- [ ] **Step 6: Commit**

```bash
git add Modules/Checkout/app/Http/Controllers/V1/Api/CheckoutController.php tests/Feature/Telemetry/CheckoutCaptureTest.php
git commit -m "feat(telemetry): capture checkout_start + placed events in placeOrder"
```

---

### Task 6: Login → session user backfill

**Files:**
- Modify: `app/Http/Controllers/V1/Api/Auth/LoginController.php` (`__invoke` — after successful auth, before the success JSON responses at ~line 79 and ~line 105)
- Test: `tests/Feature/Telemetry/LoginBackfillTest.php`

**Interfaces:**
- Consumes: `CaptureService::backfillUser` (Task 2).
- Produces: when a login request carries `session_id`, the matching `visit_sessions` row's `user_id` is set to the authenticated user.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Telemetry/LoginBackfillTest.php`:

```php
<?php

namespace Tests\Feature\Telemetry;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Telemetry\Services\CaptureService;
use Tests\TestCase;

class LoginBackfillTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_backfills_user_id_onto_session(): void
    {
        $user = User::factory()->create([
            'email' => 'buyer@test.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        CaptureService::recordSession('sess-guest', null, ['utm_source' => 'google']);

        $this->postJson('/api/v1/login', [
            'email' => 'buyer@test.com',
            'password' => 'password123',
            'session_id' => 'sess-guest',
        ])->assertStatus(200);

        $this->assertDatabaseHas('visit_sessions', [
            'session_id' => 'sess-guest', 'user_id' => $user->id, 'utm_source' => 'google',
        ]);
    }
}
```

> Note: the login route is `POST /api/v1/login` (throttle 5/min, confirmed in `routes/api.php`). `session_id` is passed through unvalidated and read via `$request->input('session_id')`.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=LoginBackfillTest`
Expected: FAIL — `visit_sessions.user_id` still null.

- [ ] **Step 3: Add backfill after successful auth**

In `LoginController::__invoke`, after the user is authenticated and the token is created but before each `return response()->json([...])` **success** response (there are two token-issuing paths, ~line 77 and ~line 103), add the backfill call. Place it once right after `$user` is confirmed authenticated and `$token` is created, guarding on the presence of `session_id`:

```php
            if ($request->filled('session_id')) {
                \Modules\Telemetry\Services\CaptureService::backfillUser(
                    $request->input('session_id'),
                    $user->id,
                );
            }
```

If both success paths build their response separately, add the block before each success `return`. The call is idempotent, so if it runs on both paths that is harmless.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=LoginBackfillTest`
Expected: PASS (1 test).

- [ ] **Step 5: Run the full analytics suite + commit**

Run: `php artisan test --filter=Telemetry`
Expected: PASS (all Task 1–6 tests green).

```bash
git add app/Http/Controllers/V1/Api/Auth/LoginController.php tests/Feature/Telemetry/LoginBackfillTest.php
git commit -m "feat(telemetry): backfill visit_session user_id on login"
```

---

## Definition of done (Phase 1)

- `php artisan test --filter=Telemetry` is green.
- Four tables migrate cleanly; capture is confirmed on search (incl. zero-result), checkout (`checkout_start` + `placed`), the two `/track/*` endpoints, and login backfill.
- No capture path can throw into a user-facing request (enforced by the malformed-payload test + `try/catch` in `CaptureService`).
- Nothing reads these tables yet — reporting arrives in Phase 2 (funnel + abandoned cart), which also introduces the date-range query service.

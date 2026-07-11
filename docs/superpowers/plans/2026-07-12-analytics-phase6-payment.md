# Analytics Phase 6 — Payment Analytics Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Capture Nass payment attempts (non-blocking) and report payment health — success/failure rate, method mix, failure reasons, retries — on a new admin Payments page.

**Architecture:** A `payment_attempts` table + `PaymentAttemptRecorder` (best-effort, never throws) capture each Nass attempt at 3 points in `CheckoutController`. `PaymentAnalyticsService` reports order-level metrics (immediate) + attempt-level metrics (going forward). New `paymentStatistics` controller + `admin::statistics.payments` page. Reuses `DateRange`.

**Tech Stack:** Laravel 11, nwidart modules, PHPUnit 11, SQLite test DB with `RefreshDatabase`. Admin routes behind `admin.auth`; tests authenticate with `actingAs($user)` where `$user` has a `Modules\Admin\Models\UserAdmin` row.

## Global Constraints

- Capture is best-effort and NON-BLOCKING: `PaymentAttemptRecorder::record` wraps its work in `try/catch (\Throwable)`, logs, and returns — it must never throw into checkout/payment. (from spec)
- `occurred_at` (event clock) distinct from `created_at`; money `decimal(12,2)`; rates rounded 4; divide-by-zero guarded. (from spec)
- Wallet-only payments are NOT gateway attempts — they are covered by order-level method mix, not `payment_attempts`. (from spec)
- Paid order = `payment_status = 'completed'`; failure = `'failed'`/`'expired'`; success responseCode = `'00'`. (verified)
- Run commands from `C:\xampp\htdocs\Narzin\narzinapp-main`.

---

### Task 1: payment_attempts table + model

**Files:**
- Create: `Modules/Checkout/database/migrations/2026_07_12_000000_create_payment_attempts_table.php`
- Create: `Modules/Checkout/app/Models/PaymentAttempt.php`
- Test: `tests/Feature/Analytics/PaymentAttemptSchemaTest.php`

**Interfaces:**
- Produces `Modules\Checkout\Models\PaymentAttempt` with `$fillable`: `order_id, user_id, gateway, status, response_code, amount, occurred_at`. Tasks 2–3 consume it.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Analytics/PaymentAttemptSchemaTest.php`:

```php
<?php

namespace Tests\Feature\Analytics;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Checkout\Models\PaymentAttempt;
use Tests\TestCase;

class PaymentAttemptSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_attempts_accepts_a_row(): void
    {
        PaymentAttempt::create([
            'order_id' => null, 'user_id' => null, 'gateway' => 'nass',
            'status' => 'failed', 'response_code' => '51', 'amount' => 25.50,
            'occurred_at' => now(),
        ]);

        $this->assertDatabaseHas('payment_attempts', [
            'gateway' => 'nass', 'status' => 'failed', 'response_code' => '51',
        ]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PaymentAttemptSchemaTest`
Expected: FAIL — `Class "Modules\Checkout\Models\PaymentAttempt" not found`.

- [ ] **Step 3: Write the migration**

Create `Modules/Checkout/database/migrations/2026_07_12_000000_create_payment_attempts_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('gateway');
            $table->enum('status', ['initiated', 'success', 'failed']);
            $table->string('response_code')->nullable()->index();
            $table->decimal('amount', 12, 2)->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
```

- [ ] **Step 4: Write the model**

Create `Modules/Checkout/app/Models/PaymentAttempt.php`:

```php
<?php

namespace Modules\Checkout\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAttempt extends Model
{
    protected $table = 'payment_attempts';

    protected $fillable = [
        'order_id', 'user_id', 'gateway', 'status', 'response_code', 'amount', 'occurred_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=PaymentAttemptSchemaTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add Modules/Checkout/database/migrations/2026_07_12_000000_create_payment_attempts_table.php Modules/Checkout/app/Models/PaymentAttempt.php tests/Feature/Analytics/PaymentAttemptSchemaTest.php
git commit -m "feat(payments): payment_attempts capture table + model"
```

---

### Task 2: PaymentAttemptRecorder (non-blocking) + wire 3 capture points

**Files:**
- Create: `Modules/Checkout/app/Services/PaymentAttemptRecorder.php`
- Modify: `Modules/Checkout/app/Http/Controllers/V1/Api/CheckoutController.php` (3 capture points)
- Test: `tests/Feature/Analytics/PaymentAttemptCaptureTest.php`

**Interfaces:**
- Consumes: `PaymentAttempt` (Task 1).
- Produces `Modules\Checkout\Services\PaymentAttemptRecorder::record(?int $orderId, ?int $userId, string $gateway, string $status, ?string $responseCode, ?float $amount): void` — static, best-effort, never throws.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Analytics/PaymentAttemptCaptureTest.php`:

```php
<?php

namespace Tests\Feature\Analytics;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Checkout\Services\PaymentAttemptRecorder;
use Tests\TestCase;

class PaymentAttemptCaptureTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_writes_an_attempt(): void
    {
        PaymentAttemptRecorder::record(null, 5, 'nass', 'success', '00', 40.00);

        $this->assertDatabaseHas('payment_attempts', [
            'user_id' => 5, 'gateway' => 'nass', 'status' => 'success', 'response_code' => '00',
        ]);
    }

    public function test_record_never_throws_on_bad_input(): void
    {
        // order_id has an enforced FK to orders; a non-existent id throws at the
        // DB — the recorder must swallow it and not propagate (reliable across
        // SQLite/MySQL, unlike relying on enum CHECK enforcement).
        PaymentAttemptRecorder::record(999999, null, 'nass', 'success', '00', 10.00);

        $this->assertDatabaseCount('payment_attempts', 0); // swallowed, nothing written
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PaymentAttemptCaptureTest`
Expected: FAIL — `Class "...PaymentAttemptRecorder" not found`.

- [ ] **Step 3: Write the recorder**

Create `Modules/Checkout/app/Services/PaymentAttemptRecorder.php`:

```php
<?php

namespace Modules\Checkout\Services;

use Illuminate\Support\Facades\Log;
use Modules\Checkout\Models\PaymentAttempt;

/**
 * Best-effort payment-attempt capture. Swallows all exceptions (logs and
 * continues) so a capture failure can NEVER break checkout or payment.
 */
class PaymentAttemptRecorder
{
    public static function record(
        ?int $orderId,
        ?int $userId,
        string $gateway,
        string $status,
        ?string $responseCode,
        ?float $amount
    ): void {
        try {
            PaymentAttempt::create([
                'order_id' => $orderId,
                'user_id' => $userId,
                'gateway' => $gateway,
                'status' => $status,
                'response_code' => $responseCode,
                'amount' => $amount,
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('PaymentAttemptRecorder::record failed', ['error' => $e->getMessage()]);
        }
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=PaymentAttemptCaptureTest`
Expected: PASS (2 tests). The second test forces a real DB error via a non-existent `order_id` (enforced FK) and asserts the recorder swallowed it (0 rows) — reliable across SQLite/MySQL.

- [ ] **Step 5: Wire the 3 capture points in CheckoutController**

All three sit beside existing `logAudit(...)` calls (precedent for adding a line there). Add the import at the top of `CheckoutController.php` (with the other `use` lines):

```php
use Modules\Checkout\Services\PaymentAttemptRecorder;
```

**5a — Initiate** (`placeOrder`, inside `if ($nassResponse['success'] ?? false)`, ~line 416, next to the `payment_initiated` `logAudit`):

```php
                    PaymentAttemptRecorder::record($order->id, $order->user_id, 'nass', 'initiated', null, (float) $finalAmount);
```

**5b — Resolve in verifyPayment** (right after `$nassStatus = $this->nassPaymentService->checkTransactionStatus($paymentId);`, ~line 535):

```php
            $rc = $nassStatus['data']['responseCode'] ?? null;
            PaymentAttemptRecorder::record($order->id, $order->user_id, 'nass', $rc === '00' ? 'success' : 'failed', $rc, (float) $order->final_price);
```

**5c — Resolve in nassWebhook** (right after that method's `$nassStatus = $this->nassPaymentService->checkTransactionStatus($paymentId);`, ~line 725):

```php
            $rc = $nassStatus['data']['responseCode'] ?? null;
            PaymentAttemptRecorder::record($order->id, $order->user_id, 'nass', $rc === '00' ? 'success' : 'failed', $rc, (float) $order->final_price);
```

> If `$order` or `$finalAmount`/`$order->final_price` is not in scope at an anchor, use what is (the order and a nullable amount) — the analytics only require order_id/status/response_code; amount is nullable. If an anchor is ambiguous, STOP and report BLOCKED with the surrounding lines rather than guessing.

- [ ] **Step 6: Add a feature test for the initiate capture**

Append to `tests/Feature/Analytics/PaymentAttemptCaptureTest.php` a test that places an order (mirroring `tests/Feature/Telemetry/CheckoutCaptureTest.php` — seed fixtures + a `cart` row, mock `NassPaymentService::createTransaction` to return success) and asserts an `initiated` `payment_attempts` row is written for the order. Copy the fixture/mock setup from `CheckoutCaptureTest` verbatim; assert:

```php
        $this->assertDatabaseHas('payment_attempts', ['status' => 'initiated', 'gateway' => 'nass']);
```

If mocking the full place-order flow proves heavy, keep the recorder unit tests (Steps 1/3) as the binding coverage and note in the report that the initiate wiring is covered by inspection — do not spend excessive effort fully simulating Nass. The verify/webhook wiring is verified by inspection (heavy Nass mocking is out of scope).

- [ ] **Step 7: Run tests + full suite + commit**

Run: `php artisan test --filter=PaymentAttemptCaptureTest` then `php artisan test`
Expected: capture tests pass; full suite green except the pre-existing `AuthenticationTest > users_can_logout`.

```bash
git add Modules/Checkout/app/Services/PaymentAttemptRecorder.php Modules/Checkout/app/Http/Controllers/V1/Api/CheckoutController.php tests/Feature/Analytics/PaymentAttemptCaptureTest.php
git commit -m "feat(payments): non-blocking payment-attempt capture at Nass initiate + resolve"
```

---

### Task 3: PaymentAnalyticsService

**Files:**
- Create: `Modules/Admin/app/Services/PaymentAnalyticsService.php`
- Test: `tests/Feature/Analytics/PaymentAnalyticsServiceTest.php`

**Interfaces:**
- Consumes: `DateRange`, `Modules\Checkout\Models\{Order,PaymentAttempt}`.
- Produces `Modules\Admin\Services\PaymentAnalyticsService` with `orderPaymentSummary(DateRange): array`, `methodMix(DateRange): array`, `attemptSummary(DateRange): array`, `failureReasons(DateRange): Collection`. Task 4 consumes them.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Analytics/PaymentAnalyticsServiceTest.php`:

```php
<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\PaymentAnalyticsService;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\PaymentAttempt;
use Tests\TestCase;

class PaymentAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private function range(): DateRange
    {
        return new DateRange(now()->subDays(30)->startOfDay(), now()->endOfDay());
    }

    private function order(array $attrs): void
    {
        $user = User::factory()->create();
        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id, 'address' => '1 St', 'created_at' => now(), 'updated_at' => now(),
        ]);
        Order::create(array_merge([
            'user_id' => $user->id, 'address_id' => $addressId,
            'order_number' => 'T-' . uniqid(), 'order_status' => 'pending',
            'total_amount' => 100, 'price_after_discount' => 100,
        ], $attrs));
    }

    public function test_order_payment_summary_success_rate(): void
    {
        $this->order(['payment_status' => 'completed']);
        $this->order(['payment_status' => 'completed']);
        $this->order(['payment_status' => 'failed']);
        $this->order(['payment_status' => 'not_paid']); // pending, excluded from rate

        $s = (new PaymentAnalyticsService())->orderPaymentSummary($this->range());
        $this->assertSame(2, $s['completed']);
        $this->assertSame(1, $s['failed']);
        $this->assertEquals(round(2 / 3, 4), $s['success_rate']); // 2 completed / 3 resolved
    }

    public function test_method_mix_wallet_vs_gateway(): void
    {
        $this->order(['payment_status' => 'completed', 'wallet_usage' => 30]);
        $this->order(['payment_status' => 'completed', 'wallet_usage' => 0]);

        $s = (new PaymentAnalyticsService())->methodMix($this->range());
        $this->assertSame(1, $s['wallet_involved']);
        $this->assertSame(1, $s['gateway_only']);
    }

    public function test_attempt_summary_and_failure_reasons(): void
    {
        PaymentAttempt::create(['gateway' => 'nass', 'status' => 'success', 'response_code' => '00', 'occurred_at' => now()]);
        PaymentAttempt::create(['gateway' => 'nass', 'status' => 'failed', 'response_code' => '51', 'occurred_at' => now()]);
        PaymentAttempt::create(['gateway' => 'nass', 'status' => 'failed', 'response_code' => '51', 'occurred_at' => now()]);

        $svc = new PaymentAnalyticsService();
        $a = $svc->attemptSummary($this->range());
        $this->assertSame(3, $a['total']);
        $this->assertSame(1, $a['success']);
        $this->assertSame(2, $a['failed']);
        $this->assertEquals(round(1 / 3, 4), $a['gateway_success_rate']);

        $reasons = $svc->failureReasons($this->range());
        $this->assertSame('51', $reasons->first()['response_code']);
        $this->assertSame(2, $reasons->first()['count']);
    }

    public function test_no_data_no_divide_by_zero(): void
    {
        $s = (new PaymentAnalyticsService())->orderPaymentSummary($this->range());
        $this->assertEquals(0.0, $s['success_rate']);
        $a = (new PaymentAnalyticsService())->attemptSummary($this->range());
        $this->assertEquals(0.0, $a['gateway_success_rate']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PaymentAnalyticsServiceTest`
Expected: FAIL — class not found.

- [ ] **Step 3: Write the service**

Create `Modules/Admin/app/Services/PaymentAnalyticsService.php`:

```php
<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Collection;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\PaymentAttempt;

/**
 * Read-only payment health. Order-level (payment_status, wallet_usage) works
 * over existing orders; attempt-level (payment_attempts) fills in going forward.
 */
class PaymentAnalyticsService
{
    public function orderPaymentSummary(DateRange $range): array
    {
        $counts = Order::query()
            ->whereBetween('created_at', [$range->from, $range->to])
            ->selectRaw('payment_status, COUNT(*) as c')
            ->groupBy('payment_status')
            ->pluck('c', 'payment_status');

        $completed = (int) ($counts['completed'] ?? 0);
        $failed = (int) ($counts['failed'] ?? 0);
        $expired = (int) ($counts['expired'] ?? 0);
        $resolved = $completed + $failed + $expired;

        return [
            'completed' => $completed,
            'failed' => $failed,
            'expired' => $expired,
            'processing' => (int) ($counts['processing'] ?? 0),
            'not_paid' => (int) ($counts['not_paid'] ?? 0),
            'success_rate' => $resolved > 0 ? round($completed / $resolved, 4) : 0.0,
        ];
    }

    public function methodMix(DateRange $range): array
    {
        $base = Order::query()->whereBetween('created_at', [$range->from, $range->to]);
        $wallet = (clone $base)->where('wallet_usage', '>', 0)->count();
        $total = (clone $base)->count();

        return [
            'wallet_involved' => $wallet,
            'gateway_only' => $total - $wallet,
        ];
    }

    public function attemptSummary(DateRange $range): array
    {
        $counts = PaymentAttempt::query()
            ->whereBetween('occurred_at', [$range->from, $range->to])
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $success = (int) ($counts['success'] ?? 0);
        $failed = (int) ($counts['failed'] ?? 0);
        $resolved = $success + $failed;

        return [
            'total' => $success + $failed + (int) ($counts['initiated'] ?? 0),
            'success' => $success,
            'failed' => $failed,
            'initiated' => (int) ($counts['initiated'] ?? 0),
            'gateway_success_rate' => $resolved > 0 ? round($success / $resolved, 4) : 0.0,
        ];
    }

    public function failureReasons(DateRange $range): Collection
    {
        return PaymentAttempt::query()
            ->whereBetween('occurred_at', [$range->from, $range->to])
            ->where('status', 'failed')
            ->selectRaw('response_code, COUNT(*) as c')
            ->groupBy('response_code')
            ->orderByDesc('c')
            ->get()
            ->map(fn ($r) => ['response_code' => $r->response_code ?? '(none)', 'count' => (int) $r->c]);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=PaymentAnalyticsServiceTest`
Expected: PASS (4 tests).

- [ ] **Step 5: Commit**

```bash
git add Modules/Admin/app/Services/PaymentAnalyticsService.php tests/Feature/Analytics/PaymentAnalyticsServiceTest.php
git commit -m "feat(analytics): PaymentAnalyticsService — order + attempt payment metrics"
```

---

### Task 4: Payments page (controller + route + Blade + sidebar)

**Files:**
- Modify: `Modules/Admin/app/Http/Controllers/StatisticsController.php` (`paymentStatistics` + import)
- Modify: `Modules/Admin/routes/web.php` (`statistics/payments` route in `admin.auth` group)
- Create: `Modules/Admin/resources/views/statistics/payments.blade.php`
- Modify: `resources/views/components/admin/sidebar.blade.php` (Payments link)
- Test: `tests/Feature/Analytics/PaymentsPageTest.php`

**Interfaces:**
- Consumes: `PaymentAnalyticsService`, `DateRange`.
- Produces: `GET statistics/payments` (route name `statistics.payments`) rendering `admin::statistics.payments`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Analytics/PaymentsPageTest.php`:

```php
<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Tests\TestCase;

class PaymentsPageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::create([
            'name' => 'Admin', 'email' => 'admin' . uniqid() . '@t.test',
            'password' => 'x', 'email_verified_at' => now(),
        ]);
        UserAdmin::create(['user_id' => $user->id, 'is_active' => 1]);
        return $user;
    }

    public function test_admin_sees_payments_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('statistics.payments'))
            ->assertOk()
            ->assertSee('Payment')
            ->assertSee('Success rate', false);
    }

    public function test_guest_cannot_reach_payments_page(): void
    {
        $this->get(route('statistics.payments'))->assertRedirect();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PaymentsPageTest`
Expected: FAIL — route `statistics.payments` not defined.

- [ ] **Step 3: Route**

In `Modules/Admin/routes/web.php`, after the `statistics/profit` route (inside `admin.auth`):

```php
    Route::get('statistics/payments', [StatisticsController::class, 'paymentStatistics'])->name('statistics.payments');
```

- [ ] **Step 4: Controller**

Import in `StatisticsController.php`:

```php
use Modules\Admin\Services\PaymentAnalyticsService;
```

Method (after `profitStatistics`):

```php
    public function paymentStatistics(Request $request)
    {
        $range = DateRange::fromRequest($request);
        $service = new PaymentAnalyticsService();

        return view('admin::statistics.payments', [
            'orderSummary' => $service->orderPaymentSummary($range),
            'methodMix' => $service->methodMix($range),
            'attempts' => $service->attemptSummary($range),
            'failureReasons' => $service->failureReasons($range),
            'from' => $range->from->toDateString(),
            'to' => $range->to->toDateString(),
        ]);
    }
```

- [ ] **Step 5: Blade view**

Create `Modules/Admin/resources/views/statistics/payments.blade.php`:

```blade
<x-admin-layout>
    <div class="space-y-8 px-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-1">Payment Analytics</h1>
            <p class="text-sm text-gray-500">
                Order-level metrics cover all orders now. Attempt-level metrics
                (retries, failure reasons) fill in as new gateway payments flow.
            </p>
            <form method="GET" class="mt-4 flex flex-wrap items-end gap-3">
                <label class="text-sm">From <input type="date" name="from" value="{{ $from }}" class="block border rounded px-2 py-1" /></label>
                <label class="text-sm">To <input type="date" name="to" value="{{ $to }}" class="block border rounded px-2 py-1" /></label>
                <button type="submit" class="bg-gray-800 text-white rounded px-4 py-1.5 text-sm">Apply</button>
            </form>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Order success rate</div>
                <div class="text-3xl font-bold">{{ number_format($orderSummary['success_rate'] * 100, 1) }}%</div>
                <div class="text-xs text-gray-400 mt-1">{{ number_format($orderSummary['completed']) }} completed · {{ number_format($orderSummary['failed'] + $orderSummary['expired']) }} failed</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Gateway attempt success rate</div>
                <div class="text-3xl font-bold">{{ number_format($attempts['gateway_success_rate'] * 100, 1) }}%</div>
                <div class="text-xs text-gray-400 mt-1">{{ number_format($attempts['total']) }} attempts</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-gray-500 text-sm">Method mix (inferred)</div>
                <div class="text-lg font-semibold">{{ number_format($methodMix['wallet_involved']) }} wallet · {{ number_format($methodMix['gateway_only']) }} gateway</div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Order payment status</h2>
            <div class="flex flex-wrap gap-6 text-sm">
                @foreach (['completed'=>'Completed','failed'=>'Failed','expired'=>'Expired','processing'=>'Processing','not_paid'=>'Not paid'] as $k => $label)
                    <div><span class="text-gray-500">{{ $label }}:</span> <span class="font-semibold">{{ number_format($orderSummary[$k]) }}</span></div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Gateway failure reasons</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4">Response code</th>
                            <th class="py-2 pr-4">Failed attempts</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($failureReasons as $row)
                            <tr class="border-b">
                                <td class="py-2 pr-4 font-mono">{{ $row['response_code'] }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['count']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-center text-gray-400">No failed gateway attempts recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
```

- [ ] **Step 6: Sidebar link**

In `resources/views/components/admin/sidebar.blade.php`, after the "Platform Profit" `<li>` (search `statistics.profit`), insert a Payments link mirroring that block, with `route('statistics.payments')`, `request()->routeIs('statistics.payments')`, label "Payments", and any inline SVG icon (reuse the profit block's `<svg>` markup — icon choice is cosmetic).

- [ ] **Step 7: Run test + full analytics suite + commit**

Run: `php artisan test --filter=PaymentsPageTest` then `php artisan test --filter=Analytics`
Expected: PASS. If `<x-admin-layout>` 500s, report BLOCKED — do not stub the layout.

```bash
git add Modules/Admin/app/Http/Controllers/StatisticsController.php Modules/Admin/routes/web.php Modules/Admin/resources/views/statistics/payments.blade.php resources/views/components/admin/sidebar.blade.php tests/Feature/Analytics/PaymentsPageTest.php
git commit -m "feat(analytics): admin Payments page — payment health + failure reasons"
```

---

## Definition of done (Phase 6)

- `php artisan test --filter=Analytics` green.
- Payment attempts captured (non-blocking) at Nass initiate + both resolution paths; capture can never break checkout/payment.
- `GET statistics/payments` renders for an admin: order success rate + status breakdown (immediate), method mix, gateway attempt success rate, and failure-reason table; date-range respected; guest redirected.
- One migration (`payment_attempts`); attempt-level metrics fill in going forward; order-level metrics work over existing data.

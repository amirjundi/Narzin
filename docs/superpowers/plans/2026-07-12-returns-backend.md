# Returns Backend Feature Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Backend for customer returns — request→approve/reject→refund state machine, reusing the existing whole-order wallet refund, plus return-rate reporting.

**Architecture:** `order_returns` table + `OrderReturn` model. The refund logic is extracted from `OrderController::refundToWallet` into a shared `OrderRefundService::refundWholeOrder` (behavior-identical) that both the legacy admin button and the return-refund call. Customer API requests/lists returns; admin API approves/rejects/refunds. `ReturnAnalyticsService` + admin Returns page report on it.

**Tech Stack:** Laravel 11, nwidart modules, Sanctum (customer) + admin.auth (admin), PHPUnit 11, SQLite test DB with `RefreshDatabase`.

## Global Constraints

- **Whole-order returns only (v1).** `order_returns.order_item_id` is nullable and always null in v1 — per-item is a future enhancement. (from spec)
- Refund is **to wallet** (matching the existing admin refund), never to the gateway. (confirmed)
- Money `decimal(12,2)`; the refund path is transactional and behavior-identical to today's `refundToWallet` (wallet credit + stock refill + vendor-ledger reversal + audit + status → refunded/cancelled). (from spec)
- State machine: `requested`→`approved`/`rejected`; `approved`→`refunded`; `rejected`/`refunded` terminal; illegal transitions 422. (from spec)
- Reason enum: `damaged, wrong_item, not_as_described, no_longer_needed, other`. (from spec)
- Refund is idempotent — guarded by the order's `payment_status='refunded'` (never double-credit). (from spec)
- Run commands from `C:\xampp\htdocs\Narzin\narzinapp-main`.

---

### Task 1: order_returns table + model

**Files:**
- Create: `Modules/Checkout/database/migrations/2026_07_12_100000_create_order_returns_table.php`
- Create: `Modules/Checkout/app/Models/OrderReturn.php`
- Test: `tests/Feature/Returns/OrderReturnSchemaTest.php`

**Interfaces:**
- Produces `Modules\Checkout\Models\OrderReturn` (`$fillable`: order_id, order_item_id, user_id, reason, status, refund_amount, admin_note, requested_at, resolved_at; `belongsTo` Order + User). Tasks 3–5 consume it.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Returns/OrderReturnSchemaTest.php`:

```php
<?php

namespace Tests\Feature\Returns;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderReturn;
use Tests\TestCase;

class OrderReturnSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_return_row_round_trips(): void
    {
        $user = User::factory()->create();
        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id, 'address' => '1 St', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $order = Order::create([
            'user_id' => $user->id, 'address_id' => $addressId, 'order_number' => 'T-' . uniqid(),
            'order_status' => 'pending', 'payment_status' => 'completed', 'total_amount' => 100, 'final_price' => 100,
        ]);

        $return = OrderReturn::create([
            'order_id' => $order->id, 'order_item_id' => null, 'user_id' => $user->id,
            'reason' => 'damaged', 'status' => 'requested', 'requested_at' => now(),
        ]);

        $this->assertDatabaseHas('order_returns', [
            'id' => $return->id, 'order_id' => $order->id, 'reason' => 'damaged', 'status' => 'requested',
        ]);
        $this->assertSame($order->id, $return->order->id);
        $this->assertSame($user->id, $return->user->id);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=OrderReturnSchemaTest`
Expected: FAIL — `Class "Modules\Checkout\Models\OrderReturn" not found`.

- [ ] **Step 3: Write the migration**

Create `Modules/Checkout/database/migrations/2026_07_12_100000_create_order_returns_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('reason');
            $table->enum('status', ['requested', 'approved', 'rejected', 'refunded'])->default('requested')->index();
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->string('admin_note')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('order_item_id')->references('id')->on('order_items')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_returns');
    }
};
```

- [ ] **Step 4: Write the model**

Create `Modules/Checkout/app/Models/OrderReturn.php`:

```php
<?php

namespace Modules\Checkout\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReturn extends Model
{
    protected $table = 'order_returns';

    protected $fillable = [
        'order_id', 'order_item_id', 'user_id', 'reason', 'status',
        'refund_amount', 'admin_note', 'requested_at', 'resolved_at',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=OrderReturnSchemaTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add Modules/Checkout/database/migrations/2026_07_12_100000_create_order_returns_table.php Modules/Checkout/app/Models/OrderReturn.php tests/Feature/Returns/OrderReturnSchemaTest.php
git commit -m "feat(returns): order_returns table + model"
```

---

### Task 2: Extract OrderRefundService (behavior-preserving refund refactor)

**Files:**
- Create: `Modules/Checkout/app/Services/OrderRefundService.php`
- Modify: `Modules/Admin/app/Http/Controllers/OrderController.php` (`refundToWallet` delegates to the service)
- Test: `tests/Feature/Returns/OrderRefundServiceTest.php`

**Interfaces:**
- Produces `Modules\Checkout\Services\OrderRefundService::refundWholeOrder(Order $order, string $reason, ?int $adminId): float` — transactional; credits wallet by `final_price`, writes a `WalletTransaction`, refills stock, reverses vendor earnings, sets `payment_status='refunded'` + `order_status='cancelled'`, logs `OrderAudit`; returns the refunded amount; idempotent no-op (returns 0.0) if already `refunded`. Task 4 consumes it.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Returns/OrderRefundServiceTest.php`. It seeds an order with a vendor/product/variant + an order_item (so stock refill + ledger reversal have something to act on), a paid order, then refunds:

```php
<?php

namespace Tests\Feature\Returns;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\Checkout\Services\OrderRefundService;
use Tests\TestCase;

class OrderRefundServiceTest extends TestCase
{
    use RefreshDatabase;

    private function paidOrderWithItem(User $user, int $stock = 5, int $qty = 2): array
    {
        $addressId = DB::table('user_address')->insertGetId(['user_id' => $user->id, 'address' => '1 St', 'created_at' => now(), 'updated_at' => now()]);
        $vendorId = DB::table('vendors')->insertGetId(['store_name_in_arabic' => 'م', 'store_name_in_german' => 'L', 'user_id' => User::factory()->create()->id, 'created_at' => now(), 'updated_at' => now()]);
        $categoryId = DB::table('categories')->insertGetId(['name_arabic' => 'ف', 'name_german' => 'K', 'slug_arabic' => 'c-' . uniqid(), 'slug_german' => 'c-' . uniqid(), 'created_at' => now(), 'updated_at' => now()]);
        $productId = DB::table('products')->insertGetId(['name_arabic' => 'م', 'name_german' => 'P', 'slug_arabic' => 'p-' . uniqid(), 'slug_german' => 'p-' . uniqid(), 'category_id' => $categoryId, 'vendor_id' => $vendorId, 'is_active' => true, 'weight' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $variantId = DB::table('product_variants')->insertGetId(['product_id' => $productId, 'price' => 50, 'stock' => $stock, 'sku' => 'SKU-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false, 'created_at' => now(), 'updated_at' => now()]);

        $order = Order::create([
            'user_id' => $user->id, 'address_id' => $addressId, 'order_number' => 'T-' . uniqid(),
            'order_status' => 'pending', 'payment_status' => 'completed', 'total_amount' => 100, 'final_price' => 100,
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $productId, 'product_variant_id' => $variantId,
            'quantity' => $qty, 'vendor_id' => $vendorId, 'unit_price' => 50, 'subtotal' => 100, 'final_price' => 100,
            'vendor_earning' => 40,
        ]);

        return ['order' => $order, 'variant_id' => $variantId, 'stock' => $stock, 'qty' => $qty];
    }

    public function test_refund_credits_wallet_refills_stock_sets_refunded(): void
    {
        $user = User::factory()->create();
        $f = $this->paidOrderWithItem($user);

        $amount = (new OrderRefundService())->refundWholeOrder($f['order'], 'customer return', null);

        $this->assertEquals(100.00, $amount);
        $this->assertEquals(100, DB::table('user_wallet')->where('user_id', $user->id)->value('balance'));
        $this->assertEquals($f['stock'] + $f['qty'], DB::table('product_variants')->where('id', $f['variant_id'])->value('stock'));
        $this->assertDatabaseHas('orders', ['id' => $f['order']->id, 'payment_status' => 'refunded', 'order_status' => 'cancelled']);
    }

    public function test_refund_is_idempotent_on_already_refunded(): void
    {
        $user = User::factory()->create();
        $f = $this->paidOrderWithItem($user);
        $svc = new OrderRefundService();
        $svc->refundWholeOrder($f['order'], 'r', null);

        $second = $svc->refundWholeOrder($f['order']->fresh(), 'r again', null);

        $this->assertEquals(0.0, $second); // no-op
        $this->assertEquals(100, DB::table('user_wallet')->where('user_id', $user->id)->value('balance')); // not double-credited
    }
}
```

> The `user_wallets` table name: confirm it (the code uses `UserWallet` model — check its `$table`). If it differs (e.g. `user_wallet`), fix the assertion table name. The refund amount + status assertions are the point.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=OrderRefundServiceTest`
Expected: FAIL — service class not found.

- [ ] **Step 3: Write the service (extract from refundToWallet)**

Create `Modules/Checkout/app/Services/OrderRefundService.php`. Move the body of `OrderController::refundToWallet`'s try-block into it verbatim (the wallet credit, WalletTransaction, stock refill, status update, vendor ledger reversal, OrderAudit), plus the idempotency guard:

```php
<?php

namespace Modules\Checkout\Services;

use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\UserWallet;
use Modules\Checkout\Models\WalletTransaction;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\Vendor\Services\VendorLedgerService;
use Modules\Checkout\Models\OrderAudit;

/**
 * Whole-order refund to wallet, extracted from OrderController::refundToWallet
 * so both the legacy admin button and the returns flow share one path.
 * Behavior-identical: transactional wallet credit + stock refill + vendor-ledger
 * reversal + audit + status → refunded/cancelled. Idempotent: a no-op (0.0) if
 * the order is already refunded.
 */
class OrderRefundService
{
    public function refundWholeOrder(Order $order, string $reason, ?int $adminId): float
    {
        if ($order->payment_status === 'refunded') {
            return 0.0; // already refunded — never double-credit
        }

        DB::beginTransaction();
        try {
            $wallet = UserWallet::firstOrCreate(['user_id' => $order->user_id], ['balance' => 0]);
            $refundAmount = (float) $order->final_price;
            $wallet->increment('balance', $refundAmount);

            WalletTransaction::create([
                'user_id' => $order->user_id,
                'wallet_id' => $wallet->id,
                'type' => 'order',
                'amount' => $refundAmount,
                'order_id' => $order->id,
            ]);

            $order->load('items');
            foreach ($order->items as $item) {
                ProductVariant::where('id', $item->product_variant_id)->increment('stock', $item->quantity);
            }

            $oldPaymentStatus = $order->payment_status;
            $oldOrderStatus = $order->order_status;
            $order->update([
                'payment_status' => 'refunded',
                'order_status' => 'cancelled',
                'notes' => ($order->notes ?? '') . ' | Refunded: ' . $reason,
            ]);

            $ledger = new VendorLedgerService();
            foreach ($order->items as $orderItem) {
                $ledger->reverseEarning($orderItem);
            }

            OrderAudit::create([
                'order_id' => $order->id,
                'action' => 'refunded',
                'old_payment_status' => $oldPaymentStatus,
                'new_payment_status' => 'refunded',
                'old_order_status' => $oldOrderStatus,
                'new_order_status' => 'cancelled',
                'triggered_by' => $adminId ? 'admin' : 'system',
                'user_id' => $adminId,
                'data' => ['refund_amount' => $refundAmount, 'reason' => $reason],
                'notes' => 'Order refunded to wallet',
                'created_at' => now(),
            ]);

            DB::commit();
            return $refundAmount;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
```

> Verify the referenced classes/namespaces against the originals in `refundToWallet` (UserWallet, WalletTransaction, ProductVariant, VendorLedgerService, OrderAudit) — use the exact same FQNs the controller used. If `OrderAudit` lives elsewhere (the controller imports it), match that import.

- [ ] **Step 4: Make the legacy `refundToWallet` delegate**

In `Modules/Admin/app/Http/Controllers/OrderController.php`, replace the try/catch body of `refundToWallet` with a call to the service (keep the paid-status guard and the redirect responses):

```php
    public function refundToWallet(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if (!in_array($order->payment_status, ['processing', 'completed'])) {
            return redirect()->back()->with('error', 'Only paid orders can be refunded');
        }

        try {
            $amount = (new \Modules\Checkout\Services\OrderRefundService())
                ->refundWholeOrder($order, $request->reason ?? 'No reason provided', \Illuminate\Support\Facades\Auth::id());
            return redirect()->back()->with('success', "Order refunded. IQD{$amount} added to customer wallet.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Refund failed', ['order_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Refund failed: ' . $e->getMessage());
        }
    }
```

The now-unused private `refillOrderStock` in OrderController may remain (harmless) or be removed if nothing else calls it — grep first; if only `refundToWallet` used it, remove it.

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=OrderRefundServiceTest`
Expected: PASS (2 tests).

- [ ] **Step 6: Confirm the legacy admin refund still works**

Run: `php artisan test` (full suite). Any existing test covering the admin refund button must still pass. Expected: green except the pre-existing `AuthenticationTest > users_can_logout`. If a `WalletDeductionTest` or refund test exists and breaks, the extraction changed behavior — STOP and reconcile.

- [ ] **Step 7: Commit**

```bash
git add Modules/Checkout/app/Services/OrderRefundService.php Modules/Admin/app/Http/Controllers/OrderController.php tests/Feature/Returns/OrderRefundServiceTest.php
git commit -m "refactor(returns): extract OrderRefundService::refundWholeOrder; admin refund delegates"
```

---

### Task 3: Customer returns API (request + list)

**Files:**
- Create: `Modules/Checkout/app/Http/Controllers/V1/Api/ReturnController.php`
- Modify: `Modules/Checkout/routes/api.php` (2 customer routes in the `auth:sanctum` group)
- Test: `tests/Feature/Returns/CustomerReturnApiTest.php`

**Interfaces:**
- Consumes: `OrderReturn` (Task 1).
- Produces: `POST /api/v1/orders/{id}/returns` (request), `GET /api/v1/returns` (list mine).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Returns/CustomerReturnApiTest.php`:

```php
<?php

namespace Tests\Feature\Returns;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Order;
use Tests\TestCase;

class CustomerReturnApiTest extends TestCase
{
    use RefreshDatabase;

    private function order(User $user, array $attrs = []): Order
    {
        $addressId = DB::table('user_address')->insertGetId(['user_id' => $user->id, 'address' => '1 St', 'created_at' => now(), 'updated_at' => now()]);
        return Order::create(array_merge([
            'user_id' => $user->id, 'address_id' => $addressId, 'order_number' => 'T-' . uniqid(),
            'order_status' => 'pending', 'payment_status' => 'completed', 'total_amount' => 100, 'final_price' => 100,
        ], $attrs));
    }

    public function test_customer_can_request_a_return(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'damaged'])
            ->assertStatus(201);

        $this->assertDatabaseHas('order_returns', [
            'order_id' => $order->id, 'user_id' => $user->id, 'reason' => 'damaged', 'status' => 'requested',
        ]);
    }

    public function test_cannot_return_someone_elses_order(): void
    {
        $owner = User::factory()->create();
        $order = $this->order($owner);
        $attacker = User::factory()->create();

        $this->actingAs($attacker, 'sanctum')
            ->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'damaged'])
            ->assertStatus(403);
        $this->assertDatabaseCount('order_returns', 0);
    }

    public function test_cannot_return_unpaid_order(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, ['payment_status' => 'not_paid']);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'damaged'])
            ->assertStatus(422);
    }

    public function test_cannot_duplicate_active_return(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);
        $this->actingAs($user, 'sanctum')->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'damaged'])->assertStatus(201);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'wrong_item'])
            ->assertStatus(422);
    }

    public function test_invalid_reason_rejected(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'nonsense'])
            ->assertStatus(422);
    }

    public function test_list_returns_only_mine(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);
        $this->actingAs($user, 'sanctum')->postJson("/api/v1/orders/{$order->id}/returns", ['reason' => 'damaged']);

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/returns')
            ->assertOk()->assertJsonPath('data.0.reason', 'damaged');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CustomerReturnApiTest`
Expected: FAIL — routes not defined.

- [ ] **Step 3: Write the controller**

Create `Modules/Checkout/app/Http/Controllers/V1/Api/ReturnController.php`:

```php
<?php

namespace Modules\Checkout\Http\Controllers\V1\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderReturn;

class ReturnController extends Controller
{
    private const REASONS = ['damaged', 'wrong_item', 'not_as_described', 'no_longer_needed', 'other'];

    public function store(Request $request, $orderId)
    {
        $request->validate([
            'reason' => 'required|in:' . implode(',', self::REASONS),
            'note' => 'nullable|string|max:1000',
        ]);

        $order = Order::findOrFail($orderId);

        if ((int) $order->user_id !== (int) Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Not your order'], 403);
        }
        if (!in_array($order->payment_status, ['completed', 'processing'])) {
            return response()->json(['status' => false, 'message' => 'Only paid orders can be returned'], 422);
        }
        $active = OrderReturn::where('order_id', $order->id)
            ->whereIn('status', ['requested', 'approved', 'refunded'])->exists();
        if ($active) {
            return response()->json(['status' => false, 'message' => 'A return already exists for this order'], 422);
        }

        $return = OrderReturn::create([
            'order_id' => $order->id,
            'order_item_id' => null,
            'user_id' => Auth::id(),
            'reason' => $request->reason,
            'status' => 'requested',
            'admin_note' => $request->note,
            'requested_at' => now(),
        ]);

        return response()->json(['status' => true, 'data' => $return], 201);
    }

    public function index()
    {
        $returns = OrderReturn::where('user_id', Auth::id())
            ->with('order')
            ->orderByDesc('requested_at')
            ->get();

        return response()->json(['status' => true, 'data' => $returns]);
    }
}
```

- [ ] **Step 4: Add the routes**

In `Modules/Checkout/routes/api.php`, inside the existing `Route::middleware(['auth:sanctum'])->prefix('v1')->group(...)`, add:

```php
    Route::post('/orders/{id}/returns', [\Modules\Checkout\Http\Controllers\V1\Api\ReturnController::class, 'store']);
    Route::get('/returns', [\Modules\Checkout\Http\Controllers\V1\Api\ReturnController::class, 'index']);
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=CustomerReturnApiTest`
Expected: PASS (6 tests).

- [ ] **Step 6: Commit**

```bash
git add Modules/Checkout/app/Http/Controllers/V1/Api/ReturnController.php Modules/Checkout/routes/api.php tests/Feature/Returns/CustomerReturnApiTest.php
git commit -m "feat(returns): customer return request + list API"
```

---

### Task 4: Admin returns API (list + approve/reject/refund)

**Files:**
- Create: `Modules/Admin/app/Http/Controllers/AdminReturnController.php`
- Modify: `Modules/Admin/routes/web.php` (routes in the `admin.auth` group)
- Test: `tests/Feature/Returns/AdminReturnApiTest.php`

**Interfaces:**
- Consumes: `OrderReturn` (Task 1), `OrderRefundService` (Task 2).
- Produces: `GET /admin/returns`, `POST /admin/returns/{id}/approve|reject|refund`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Returns/AdminReturnApiTest.php`:

```php
<?php

namespace Tests\Feature\Returns;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Models\UserAdmin;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\Checkout\Models\OrderReturn;
use Tests\TestCase;

class AdminReturnApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $u = User::create(['name' => 'A', 'email' => 'a' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        UserAdmin::create(['user_id' => $u->id, 'is_active' => 1]);
        return $u;
    }

    private function returnFor(string $status = 'requested'): OrderReturn
    {
        $user = User::factory()->create();
        $addressId = DB::table('user_address')->insertGetId(['user_id' => $user->id, 'address' => '1 St', 'created_at' => now(), 'updated_at' => now()]);
        $vendorId = DB::table('vendors')->insertGetId(['store_name_in_arabic' => 'م', 'store_name_in_german' => 'L', 'user_id' => User::factory()->create()->id, 'created_at' => now(), 'updated_at' => now()]);
        $categoryId = DB::table('categories')->insertGetId(['name_arabic' => 'ف', 'name_german' => 'K', 'slug_arabic' => 'c-' . uniqid(), 'slug_german' => 'c-' . uniqid(), 'created_at' => now(), 'updated_at' => now()]);
        $productId = DB::table('products')->insertGetId(['name_arabic' => 'م', 'name_german' => 'P', 'slug_arabic' => 'p-' . uniqid(), 'slug_german' => 'p-' . uniqid(), 'category_id' => $categoryId, 'vendor_id' => $vendorId, 'is_active' => true, 'weight' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $variantId = DB::table('product_variants')->insertGetId(['product_id' => $productId, 'price' => 50, 'stock' => 5, 'sku' => 'SKU-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false, 'created_at' => now(), 'updated_at' => now()]);
        $order = Order::create(['user_id' => $user->id, 'address_id' => $addressId, 'order_number' => 'T-' . uniqid(), 'order_status' => 'pending', 'payment_status' => 'completed', 'total_amount' => 100, 'final_price' => 100]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $productId, 'product_variant_id' => $variantId, 'quantity' => 2, 'vendor_id' => $vendorId, 'unit_price' => 50, 'subtotal' => 100, 'final_price' => 100, 'vendor_earning' => 40]);
        return OrderReturn::create(['order_id' => $order->id, 'user_id' => $user->id, 'reason' => 'damaged', 'status' => $status, 'requested_at' => now()]);
    }

    public function test_approve_moves_requested_to_approved(): void
    {
        $r = $this->returnFor('requested');
        $this->actingAs($this->admin())->post(route('returns.approve', $r->id))->assertRedirect();
        $this->assertDatabaseHas('order_returns', ['id' => $r->id, 'status' => 'approved']);
    }

    public function test_reject_moves_requested_to_rejected(): void
    {
        $r = $this->returnFor('requested');
        $this->actingAs($this->admin())->post(route('returns.reject', $r->id), ['admin_note' => 'no'])->assertRedirect();
        $this->assertDatabaseHas('order_returns', ['id' => $r->id, 'status' => 'rejected']);
    }

    public function test_refund_from_approved_executes_refund(): void
    {
        $r = $this->returnFor('approved');
        $this->actingAs($this->admin())->post(route('returns.refund', $r->id))->assertRedirect();

        $r->refresh();
        $this->assertSame('refunded', $r->status);
        $this->assertEquals(100.00, (float) $r->refund_amount);
        $this->assertDatabaseHas('orders', ['id' => $r->order_id, 'payment_status' => 'refunded']);
        $this->assertEquals(100, DB::table('user_wallet')->where('user_id', $r->user_id)->value('balance'));
    }

    public function test_illegal_transition_rejected(): void
    {
        $r = $this->returnFor('rejected'); // terminal
        $this->actingAs($this->admin())->post(route('returns.approve', $r->id))->assertStatus(422);
        $this->assertDatabaseHas('order_returns', ['id' => $r->id, 'status' => 'rejected']);
    }

    public function test_refund_requires_approved(): void
    {
        $r = $this->returnFor('requested'); // not approved yet
        $this->actingAs($this->admin())->post(route('returns.refund', $r->id))->assertStatus(422);
    }
}
```

> Adjust the `user_wallets` table name if it differs (see Task 2 note).

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AdminReturnApiTest`
Expected: FAIL — routes not defined.

- [ ] **Step 3: Write the controller**

Create `Modules/Admin/app/Http/Controllers/AdminReturnController.php`:

```php
<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Checkout\Models\OrderReturn;
use Modules\Checkout\Services\OrderRefundService;

class AdminReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = OrderReturn::with(['order', 'user'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('requested_at')
            ->paginate(30);

        return view('admin::returns.index', compact('returns'));
    }

    public function approve(Request $request, $id)
    {
        $return = OrderReturn::findOrFail($id);
        if ($return->status !== 'requested') {
            return response()->json(['message' => 'Only requested returns can be approved'], 422);
        }
        $return->update(['status' => 'approved', 'admin_note' => $request->admin_note, 'resolved_at' => now()]);
        return redirect()->back()->with('success', 'Return approved');
    }

    public function reject(Request $request, $id)
    {
        $return = OrderReturn::findOrFail($id);
        if ($return->status !== 'requested') {
            return response()->json(['message' => 'Only requested returns can be rejected'], 422);
        }
        $return->update(['status' => 'rejected', 'admin_note' => $request->admin_note, 'resolved_at' => now()]);
        return redirect()->back()->with('success', 'Return rejected');
    }

    public function refund(Request $request, $id)
    {
        $return = OrderReturn::with('order')->findOrFail($id);
        if ($return->status !== 'approved') {
            return response()->json(['message' => 'Only approved returns can be refunded'], 422);
        }

        $amount = (new OrderRefundService())->refundWholeOrder(
            $return->order, 'Return: ' . $return->reason, Auth::id()
        );

        $return->update(['status' => 'refunded', 'refund_amount' => $amount, 'resolved_at' => now()]);

        return redirect()->back()->with('success', "Return refunded. IQD{$amount} to wallet.");
    }
}
```

> Note: `index` renders `admin::returns.index` — that view is created in Task 6. For this task's tests (which only hit approve/reject/refund + expect redirects/422), `index` is not exercised; Task 6 adds the view. If you prefer, stub a minimal view now, but the tests here don't need it.

- [ ] **Step 4: Add the routes**

In `Modules/Admin/routes/web.php`, inside the `admin.auth` group, add:

```php
    Route::get('returns', [\Modules\Admin\Http\Controllers\AdminReturnController::class, 'index'])->name('returns.index');
    Route::post('returns/{id}/approve', [\Modules\Admin\Http\Controllers\AdminReturnController::class, 'approve'])->name('returns.approve');
    Route::post('returns/{id}/reject', [\Modules\Admin\Http\Controllers\AdminReturnController::class, 'reject'])->name('returns.reject');
    Route::post('returns/{id}/refund', [\Modules\Admin\Http\Controllers\AdminReturnController::class, 'refund'])->name('returns.refund');
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=AdminReturnApiTest`
Expected: PASS (5 tests).

- [ ] **Step 6: Commit**

```bash
git add Modules/Admin/app/Http/Controllers/AdminReturnController.php Modules/Admin/routes/web.php tests/Feature/Returns/AdminReturnApiTest.php
git commit -m "feat(returns): admin approve/reject/refund API with state guards"
```

---

### Task 5: ReturnAnalyticsService

**Files:**
- Create: `Modules/Admin/app/Services/ReturnAnalyticsService.php`
- Test: `tests/Feature/Returns/ReturnAnalyticsServiceTest.php`

**Interfaces:**
- Consumes: `DateRange`, `OrderReturn`, `Order`.
- Produces `Modules\Admin\Services\ReturnAnalyticsService` with `summary(DateRange): array` (requested, approved, rejected, refunded, return_rate, total_refunded) and `byReason(DateRange): Collection` (reason, count).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Returns/ReturnAnalyticsServiceTest.php`:

```php
<?php

namespace Tests\Feature\Returns;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\ReturnAnalyticsService;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderReturn;
use Tests\TestCase;

class ReturnAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private function range(): DateRange
    {
        return new DateRange(now()->subDays(30)->startOfDay(), now()->endOfDay());
    }

    private function order(): Order
    {
        $user = User::factory()->create();
        $addressId = DB::table('user_address')->insertGetId(['user_id' => $user->id, 'address' => '1 St', 'created_at' => now(), 'updated_at' => now()]);
        return Order::create(['user_id' => $user->id, 'address_id' => $addressId, 'order_number' => 'T-' . uniqid(), 'order_status' => 'pending', 'payment_status' => 'completed', 'total_amount' => 100, 'final_price' => 100]);
    }

    private function ret(Order $o, string $status, string $reason, ?float $refund = null): void
    {
        OrderReturn::create(['order_id' => $o->id, 'user_id' => $o->user_id, 'reason' => $reason, 'status' => $status, 'refund_amount' => $refund, 'requested_at' => now()]);
    }

    public function test_summary_counts_and_rate(): void
    {
        $o1 = $this->order();
        $o2 = $this->order();
        $o3 = $this->order(); // no return
        $this->ret($o1, 'refunded', 'damaged', 100);
        $this->ret($o2, 'requested', 'wrong_item');

        $s = (new ReturnAnalyticsService())->summary($this->range());
        $this->assertSame(2, $s['requested'] + $s['refunded']); // 2 returns total across statuses
        $this->assertSame(1, $s['refunded']);
        $this->assertEquals(100.00, $s['total_refunded']);
        $this->assertEquals(round(2 / 3, 4), $s['return_rate']); // 2 returns / 3 orders
    }

    public function test_by_reason(): void
    {
        $o1 = $this->order(); $o2 = $this->order();
        $this->ret($o1, 'requested', 'damaged');
        $this->ret($o2, 'requested', 'damaged');

        $rows = (new ReturnAnalyticsService())->byReason($this->range());
        $this->assertSame('damaged', $rows->first()['reason']);
        $this->assertSame(2, $rows->first()['count']);
    }

    public function test_no_orders_no_divide_by_zero(): void
    {
        $s = (new ReturnAnalyticsService())->summary($this->range());
        $this->assertEquals(0.0, $s['return_rate']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ReturnAnalyticsServiceTest`
Expected: FAIL — class not found.

- [ ] **Step 3: Write the service**

Create `Modules/Admin/app/Services/ReturnAnalyticsService.php`:

```php
<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Collection;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderReturn;

/** Read-only return analytics over order_returns + orders. */
class ReturnAnalyticsService
{
    public function summary(DateRange $range): array
    {
        $counts = OrderReturn::query()
            ->whereBetween('requested_at', [$range->from, $range->to])
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $totalReturns = (int) $counts->sum();
        $orders = Order::query()->whereBetween('created_at', [$range->from, $range->to])->count();
        $totalRefunded = (float) OrderReturn::query()
            ->whereBetween('requested_at', [$range->from, $range->to])
            ->where('status', 'refunded')
            ->sum('refund_amount');

        return [
            'requested' => (int) ($counts['requested'] ?? 0),
            'approved' => (int) ($counts['approved'] ?? 0),
            'rejected' => (int) ($counts['rejected'] ?? 0),
            'refunded' => (int) ($counts['refunded'] ?? 0),
            'total_returns' => $totalReturns,
            'return_rate' => $orders > 0 ? round($totalReturns / $orders, 4) : 0.0,
            'total_refunded' => round($totalRefunded, 2),
        ];
    }

    public function byReason(DateRange $range): Collection
    {
        return OrderReturn::query()
            ->whereBetween('requested_at', [$range->from, $range->to])
            ->selectRaw('reason, COUNT(*) as c')
            ->groupBy('reason')
            ->orderByDesc('c')
            ->get()
            ->map(fn ($r) => ['reason' => $r->reason, 'count' => (int) $r->c]);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ReturnAnalyticsServiceTest`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
git add Modules/Admin/app/Services/ReturnAnalyticsService.php tests/Feature/Returns/ReturnAnalyticsServiceTest.php
git commit -m "feat(returns): ReturnAnalyticsService — return rate + reason breakdown"
```

---

### Task 6: Admin Returns pages (management list + analytics) + sidebar

**Files:**
- Modify: `Modules/Admin/app/Http/Controllers/StatisticsController.php` (`returnStatistics` + import)
- Modify: `Modules/Admin/routes/web.php` (`statistics/returns` route)
- Create: `Modules/Admin/resources/views/statistics/returns.blade.php` (analytics)
- Create: `Modules/Admin/resources/views/returns/index.blade.php` (management list — renders `AdminReturnController::index` with approve/reject/refund forms)
- Modify: `resources/views/components/admin/sidebar.blade.php` (Returns link)
- Test: `tests/Feature/Returns/ReturnsPageTest.php`

**Interfaces:**
- Consumes: `ReturnAnalyticsService`, `DateRange`, and the Task-4 `returns.approve/reject/refund` routes (for the management view's forms).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Returns/ReturnsPageTest.php`:

```php
<?php

namespace Tests\Feature\Returns;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Tests\TestCase;

class ReturnsPageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $u = User::create(['name' => 'A', 'email' => 'a' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        UserAdmin::create(['user_id' => $u->id, 'is_active' => 1]);
        return $u;
    }

    public function test_admin_sees_returns_analytics_page(): void
    {
        $this->actingAs($this->admin())->get(route('statistics.returns'))
            ->assertOk()->assertSee('Returns')->assertSee('Return rate', false);
    }

    public function test_admin_sees_returns_management_list(): void
    {
        $this->actingAs($this->admin())->get(route('returns.index'))
            ->assertOk()->assertSee('Returns');
    }

    public function test_guest_cannot_reach_returns(): void
    {
        $this->get(route('statistics.returns'))->assertRedirect();
        $this->get(route('returns.index'))->assertRedirect();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ReturnsPageTest`
Expected: FAIL — `statistics.returns` route not defined (and `returns.index` view missing).

- [ ] **Step 3: Analytics route + controller**

Add to `Modules/Admin/routes/web.php` (in `admin.auth`, next to the other `statistics/*`):

```php
    Route::get('statistics/returns', [StatisticsController::class, 'returnStatistics'])->name('statistics.returns');
```

Import in `StatisticsController.php`: `use Modules\Admin\Services\ReturnAnalyticsService;`
Method:

```php
    public function returnStatistics(Request $request)
    {
        $range = DateRange::fromRequest($request);
        $service = new ReturnAnalyticsService();

        return view('admin::statistics.returns', [
            'summary' => $service->summary($range),
            'byReason' => $service->byReason($range),
            'from' => $range->from->toDateString(),
            'to' => $range->to->toDateString(),
        ]);
    }
```

- [ ] **Step 4: Analytics view**

Create `Modules/Admin/resources/views/statistics/returns.blade.php` (`<x-admin-layout>`, date filter, headline cards for return rate / total refunded / status counts, and a `@forelse` reason table). Follow the `payments.blade.php` structure. Must render the strings "Returns" and "Return rate". All dynamic values `{{ }}` escaped.

- [ ] **Step 5: Management view**

Create `Modules/Admin/resources/views/returns/index.blade.php` (`<x-admin-layout>`) — a table of `$returns` (order number, customer, reason, status, requested_at) with action forms: for `requested` rows, Approve (POST `returns.approve`) + Reject (POST `returns.reject`, with an `admin_note` input); for `approved` rows, Refund (POST `returns.refund`). Use `@csrf`. Must render the string "Returns". Escape all output.

- [ ] **Step 6: Sidebar link**

In `resources/views/components/admin/sidebar.blade.php`, after the "Payments" `<li>`, add a "Returns" link to `route('returns.index')` (the management page — the operational entry point), mirroring the block markup with a distinct icon.

- [ ] **Step 7: Run test + full analytics/returns suite + commit**

Run: `php artisan test --filter=ReturnsPageTest` then `php artisan test --filter=Returns`
Expected: PASS. If `<x-admin-layout>` 500s, report BLOCKED — do not stub the layout.

```bash
git add Modules/Admin/app/Http/Controllers/StatisticsController.php Modules/Admin/routes/web.php Modules/Admin/resources/views/statistics/returns.blade.php Modules/Admin/resources/views/returns/index.blade.php resources/views/components/admin/sidebar.blade.php tests/Feature/Returns/ReturnsPageTest.php
git commit -m "feat(returns): admin Returns management + analytics pages, sidebar link"
```

---

## Definition of done (Returns backend)

- `php artisan test --filter=Returns` green; full suite green except the pre-existing `AuthenticationTest > users_can_logout`.
- Customer can request a return (guarded: owner, paid, no duplicate, valid reason); can list own returns.
- Admin can approve/reject a requested return and refund an approved one; illegal transitions 422; refund credits the wallet exactly once (idempotent) via the shared `OrderRefundService`.
- The legacy admin refund button still works, now delegating to `OrderRefundService` — behavior unchanged.
- Admin Returns management + analytics pages render; return rate + reason breakdown reported.
- One migration (`order_returns`). Whole-order returns only; `order_item_id` nullable for future per-item.

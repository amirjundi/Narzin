# Vendor Payouts Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Track what each vendor is owed (earning per sold item, snapshotted at order time), maintain a ledger through collect → return → payout, and let an admin record manual payouts with per-vendor statements.

**Architecture:** Per-vendor commission % and discount-absorption % (with global defaults in a `payout_settings` singleton). A pure `VendorEarningCalculator` computes the four snapshot fields stored on each `order_item` at creation. A `VendorLedgerService` is the sole writer of a `vendor_transactions` ledger (earning/reversal/payout/adjustment); balance = SUM(amount). Triggers: snapshot on order placement, credit on collect-from-vendor, reverse on return/cancel, manual payout via an admin controller + Blade UI.

**Tech Stack:** Laravel 11 (modular, nwidart), MySQL (prod) / sqlite `:memory:` (tests), Blade admin panel, PHPUnit.

## Global Constraints

- All money is in the **stored price currency**; the exchange rate is customer-display only and never touches payouts.
- Per-vendor rate = vendor override if set, else the global default. `null` on the vendor means "use default".
- Earning per item (rounded to 2 decimals):
  `vendor_base_subtotal = base_price × qty`;
  `vendor_commission_amount = round(vendor_base_subtotal × commission% / 100, 2)`;
  `order_coupon_discount = orders.total_amount − orders.price_after_discount`;
  `item_discount_allocated = order_total > 0 ? order_coupon_discount × (order_item.subtotal / order_total) : 0`;
  `vendor_discount_absorbed = round(item_discount_allocated × absorption% / 100, 2)`;
  `vendor_earning = vendor_base_subtotal − vendor_commission_amount − vendor_discount_absorbed`.
- The four computed values are **immutable** once snapshotted on the order item.
- Ledger amounts are **signed**: earning `+`, reversal/payout `−`. Balance = `SUM(amount)`.
- Earning is credited **only** when an item's `collection_status` becomes `collected`, and **idempotently** (one `earning` row per order item). It is reversed when the item leaves `collected` (→ `pending`/`unavailable`) or its order is cancelled.
- Wallet credit never reduces a vendor's earning.
- A payout amount may not exceed the vendor's current payable balance.
- New columns are nullable; the feature is additive and backward-compatible.
- Migration filenames use the `2026_06_28_*` prefix so they sort after existing migrations.
- Tests run on sqlite with foreign keys enforced — create required parent rows (a `User` for the vendor, a `Vendor`, `Order`, `OrderItem`) and never rely on MySQL-only SQL.

---

### Task 1: Payout settings + per-vendor rate overrides + rate resolver

**Files:**
- Create: `narzinapp-main/Modules/Admin/database/migrations/2026_06_28_000001_create_payout_settings_table.php`
- Create: `narzinapp-main/Modules/Admin/app/Models/PayoutSetting.php`
- Create: `narzinapp-main/Modules/Vendor/database/migrations/2026_06_28_000002_add_payout_rates_to_vendors_table.php`
- Modify: `narzinapp-main/Modules/Vendor/app/Models/Vendor.php` (add two `$fillable` entries)
- Create: `narzinapp-main/Modules/Vendor/app/Services/VendorRateResolver.php`
- Test: `narzinapp-main/tests/Unit/VendorRateResolverTest.php`

**Interfaces:**
- Produces:
  - `PayoutSetting::current(): PayoutSetting` — latest row, creating a zero-default row if none exists.
  - `VendorRateResolver::commission(Vendor $v): float` and `::absorption(Vendor $v): float` — vendor override or global default, as a float percentage.

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/VendorRateResolverTest.php`:

```php
<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\PayoutSetting;
use Modules\Vendor\Models\Vendor;
use Modules\Vendor\Services\VendorRateResolver;
use Tests\TestCase;

class VendorRateResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_global_default_when_vendor_override_is_null(): void
    {
        PayoutSetting::create(['default_commission_percentage' => 10, 'default_discount_absorption_percentage' => 40]);
        $vendor = new Vendor(['commission_percentage' => null, 'discount_absorption_percentage' => null]);

        $r = new VendorRateResolver();
        $this->assertSame(10.0, $r->commission($vendor));
        $this->assertSame(40.0, $r->absorption($vendor));
    }

    public function test_uses_vendor_override_when_set(): void
    {
        PayoutSetting::create(['default_commission_percentage' => 10, 'default_discount_absorption_percentage' => 40]);
        $vendor = new Vendor(['commission_percentage' => 5, 'discount_absorption_percentage' => 0]);

        $r = new VendorRateResolver();
        $this->assertSame(5.0, $r->commission($vendor));
        $this->assertSame(0.0, $r->absorption($vendor));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzinapp-main && php artisan test --filter=VendorRateResolverTest`
Expected: FAIL — classes/tables do not exist.

- [ ] **Step 3: Create the payout_settings migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payout_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('default_commission_percentage', 5, 2)->default(0);
            $table->decimal('default_discount_absorption_percentage', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_settings');
    }
};
```

- [ ] **Step 4: Create the PayoutSetting model**

`Modules/Admin/app/Models/PayoutSetting.php`:

```php
<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutSetting extends Model
{
    protected $table = 'payout_settings';

    protected $fillable = [
        'default_commission_percentage',
        'default_discount_absorption_percentage',
    ];

    public static function current(): self
    {
        return static::latest('id')->first()
            ?? static::create(['default_commission_percentage' => 0, 'default_discount_absorption_percentage' => 0]);
    }
}
```

- [ ] **Step 5: Create the vendors migration**

`Modules/Vendor/database/migrations/2026_06_28_000002_add_payout_rates_to_vendors_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('commission_percentage', 5, 2)->nullable();
            $table->decimal('discount_absorption_percentage', 5, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['commission_percentage', 'discount_absorption_percentage']);
        });
    }
};
```

- [ ] **Step 6: Add the two fields to Vendor `$fillable`**

In `Modules/Vendor/app/Models/Vendor.php`, add to the `$fillable` array (after `'markup_percentage',`):

```php
        'commission_percentage',
        'discount_absorption_percentage',
```

- [ ] **Step 7: Create the resolver**

`Modules/Vendor/app/Services/VendorRateResolver.php`:

```php
<?php

namespace Modules\Vendor\Services;

use Modules\Admin\Models\PayoutSetting;
use Modules\Vendor\Models\Vendor;

class VendorRateResolver
{
    public function commission(Vendor $vendor): float
    {
        return $vendor->commission_percentage !== null
            ? (float) $vendor->commission_percentage
            : (float) PayoutSetting::current()->default_commission_percentage;
    }

    public function absorption(Vendor $vendor): float
    {
        return $vendor->discount_absorption_percentage !== null
            ? (float) $vendor->discount_absorption_percentage
            : (float) PayoutSetting::current()->default_discount_absorption_percentage;
    }
}
```

- [ ] **Step 8: Run test to verify it passes**

Run: `cd narzinapp-main && php artisan test --filter=VendorRateResolverTest`
Expected: PASS (2 tests).

- [ ] **Step 9: Commit**

```bash
git add narzinapp-main/Modules/Admin/database/migrations/2026_06_28_000001_create_payout_settings_table.php \
        narzinapp-main/Modules/Admin/app/Models/PayoutSetting.php \
        narzinapp-main/Modules/Vendor/database/migrations/2026_06_28_000002_add_payout_rates_to_vendors_table.php \
        narzinapp-main/Modules/Vendor/app/Models/Vendor.php \
        narzinapp-main/Modules/Vendor/app/Services/VendorRateResolver.php \
        narzinapp-main/tests/Unit/VendorRateResolverTest.php
git commit -m "feat(payouts): payout settings, per-vendor rate overrides, resolver"
```

---

### Task 2: VendorEarningCalculator + order_items snapshot columns

**Files:**
- Create: `narzinapp-main/Modules/Checkout/database/migrations/2026_06_28_000003_add_vendor_earning_to_order_items_table.php`
- Modify: `narzinapp-main/Modules/Checkout/app/Models/OrderItem.php` (add four `$fillable` entries)
- Create: `narzinapp-main/Modules/Vendor/app/Services/VendorEarningCalculator.php`
- Test: `narzinapp-main/tests/Unit/VendorEarningCalculatorTest.php`

**Interfaces:**
- Consumes: nothing from Task 1 (pure calculator; rates are passed in).
- Produces: `VendorEarningCalculator::compute(float $basePrice, int $qty, float $itemSubtotal, float $orderCouponDiscount, float $orderTotal, float $commissionPct, float $absorptionPct): array` returning
  `['vendor_base_subtotal'=>float, 'vendor_commission_amount'=>float, 'vendor_discount_absorbed'=>float, 'vendor_earning'=>float]`.

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/VendorEarningCalculatorTest.php`:

```php
<?php

namespace Tests\Unit;

use Modules\Vendor\Services\VendorEarningCalculator;
use Tests\TestCase;

class VendorEarningCalculatorTest extends TestCase
{
    private VendorEarningCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new VendorEarningCalculator();
    }

    public function test_commission_only_no_discount(): void
    {
        // base 100 x 1, commission 10%, no discount
        $r = $this->calc->compute(100, 1, 120.0, 0.0, 120.0, 10.0, 50.0);
        $this->assertSame(100.0, $r['vendor_base_subtotal']);
        $this->assertSame(10.0, $r['vendor_commission_amount']);
        $this->assertSame(0.0, $r['vendor_discount_absorbed']);
        $this->assertSame(90.0, $r['vendor_earning']);
    }

    public function test_discount_absorbed_by_ratio(): void
    {
        // one item, subtotal 120 of order total 120, coupon discount 30, absorption 50%
        $r = $this->calc->compute(100, 1, 120.0, 30.0, 120.0, 10.0, 50.0);
        // discount allocated = 30 * (120/120) = 30; absorbed = 30 * 50% = 15
        $this->assertSame(15.0, $r['vendor_discount_absorbed']);
        $this->assertSame(75.0, $r['vendor_earning']); // 100 - 10 - 15
    }

    public function test_absorption_zero_means_platform_absorbs(): void
    {
        $r = $this->calc->compute(100, 1, 120.0, 30.0, 120.0, 10.0, 0.0);
        $this->assertSame(0.0, $r['vendor_discount_absorbed']);
        $this->assertSame(90.0, $r['vendor_earning']);
    }

    public function test_zero_order_total_avoids_division_by_zero(): void
    {
        $r = $this->calc->compute(100, 1, 0.0, 30.0, 0.0, 10.0, 50.0);
        $this->assertSame(0.0, $r['vendor_discount_absorbed']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzinapp-main && php artisan test --filter=VendorEarningCalculatorTest`
Expected: FAIL — class not found.

- [ ] **Step 3: Create the calculator**

`Modules/Vendor/app/Services/VendorEarningCalculator.php`:

```php
<?php

namespace Modules\Vendor\Services;

class VendorEarningCalculator
{
    public function compute(
        float $basePrice,
        int $qty,
        float $itemSubtotal,
        float $orderCouponDiscount,
        float $orderTotal,
        float $commissionPct,
        float $absorptionPct
    ): array {
        $baseSubtotal = round($basePrice * $qty, 2);
        $commissionAmount = round($baseSubtotal * $commissionPct / 100, 2);

        $allocatedDiscount = $orderTotal > 0
            ? $orderCouponDiscount * ($itemSubtotal / $orderTotal)
            : 0.0;
        $discountAbsorbed = round($allocatedDiscount * $absorptionPct / 100, 2);

        return [
            'vendor_base_subtotal' => $baseSubtotal,
            'vendor_commission_amount' => $commissionAmount,
            'vendor_discount_absorbed' => $discountAbsorbed,
            'vendor_earning' => round($baseSubtotal - $commissionAmount - $discountAbsorbed, 2),
        ];
    }
}
```

- [ ] **Step 4: Create the order_items migration**

`Modules/Checkout/database/migrations/2026_06_28_000003_add_vendor_earning_to_order_items_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('vendor_base_subtotal', 10, 2)->nullable();
            $table->decimal('vendor_commission_amount', 10, 2)->nullable();
            $table->decimal('vendor_discount_absorbed', 10, 2)->nullable();
            $table->decimal('vendor_earning', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['vendor_base_subtotal', 'vendor_commission_amount', 'vendor_discount_absorbed', 'vendor_earning']);
        });
    }
};
```

- [ ] **Step 5: Add the four fields to OrderItem `$fillable`**

In `Modules/Checkout/app/Models/OrderItem.php`, add to `$fillable`:

```php
        'vendor_base_subtotal',
        'vendor_commission_amount',
        'vendor_discount_absorbed',
        'vendor_earning',
```

- [ ] **Step 6: Run test to verify it passes**

Run: `cd narzinapp-main && php artisan test --filter=VendorEarningCalculatorTest`
Expected: PASS (4 tests).

- [ ] **Step 7: Commit**

```bash
git add narzinapp-main/Modules/Checkout/database/migrations/2026_06_28_000003_add_vendor_earning_to_order_items_table.php \
        narzinapp-main/Modules/Checkout/app/Models/OrderItem.php \
        narzinapp-main/Modules/Vendor/app/Services/VendorEarningCalculator.php \
        narzinapp-main/tests/Unit/VendorEarningCalculatorTest.php
git commit -m "feat(payouts): earning calculator + order_items earning snapshot columns"
```

---

### Task 3: Ledger + payouts tables, models, and VendorLedgerService

**Files:**
- Create: `narzinapp-main/Modules/Vendor/database/migrations/2026_06_28_000004_create_vendor_payouts_table.php`
- Create: `narzinapp-main/Modules/Vendor/database/migrations/2026_06_28_000005_create_vendor_transactions_table.php`
- Create: `narzinapp-main/Modules/Vendor/app/Models/VendorPayout.php`
- Create: `narzinapp-main/Modules/Vendor/app/Models/VendorTransaction.php`
- Create: `narzinapp-main/Modules/Vendor/app/Services/VendorLedgerService.php`
- Test: `narzinapp-main/tests/Feature/VendorLedgerServiceTest.php`

**Interfaces:**
- Consumes: `OrderItem` with a non-null `vendor_earning` and `vendor_id` (Task 2).
- Produces `VendorLedgerService` methods:
  - `creditEarning(OrderItem $item): void` — idempotent; one `earning` row per item.
  - `reverseEarning(OrderItem $item): void` — one `reversal` per item if an un-reversed earning exists.
  - `recordPayout(int $vendorId, float $amount, ?string $method, ?string $reference, ?string $notes, ?int $adminId): VendorPayout` — throws `\InvalidArgumentException` if `amount <= 0` or `amount > payableBalance`.
  - `adjust(int $vendorId, float $amount, string $description, ?int $adminId): void`
  - `payableBalance(int $vendorId): float` (= SUM(amount))
  - `pendingEarnings(int $vendorId): float` (sum of `vendor_earning` for that vendor's order items with `collection_status != 'collected'`)
  - `totalPaid(int $vendorId): float` (sum of payout amounts)

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/VendorLedgerServiceTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\Vendor\Models\Vendor;
use Modules\Vendor\Services\VendorLedgerService;
use Tests\TestCase;

class VendorLedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    private function vendor(): Vendor
    {
        $u = User::create(['name' => 'V', 'email' => 'v' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        return Vendor::create([
            'user_id' => $u->id, 'store_name_in_arabic' => 'م', 'store_name_in_german' => 'L', 'status' => 'Active',
        ]);
    }

    private function orderItem(Vendor $vendor, float $earning, string $collection = 'collected'): OrderItem
    {
        $userId = User::create(['name' => 'C', 'email' => 'c' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()])->id;
        $order = Order::create([
            'user_id' => $userId, 'order_number' => 'O' . uniqid(), 'payment_id' => 'P' . uniqid(),
            'total_amount' => 100, 'final_price' => 100, 'payment_status' => 'processing', 'order_status' => 'confirmed',
        ]);
        return OrderItem::create([
            'order_id' => $order->id, 'product_id' => 1, 'product_variant_id' => 1, 'vendor_id' => $vendor->id,
            'quantity' => 1, 'unit_price' => 100, 'subtotal' => 100, 'vendor_earning' => $earning,
            'collection_status' => $collection,
        ]);
    }

    public function test_credit_earning_is_idempotent(): void
    {
        $svc = new VendorLedgerService();
        $vendor = $this->vendor();
        $item = $this->orderItem($vendor, 90.0);

        $svc->creditEarning($item);
        $svc->creditEarning($item); // again — must not double-credit

        $this->assertSame(90.0, $svc->payableBalance($vendor->id));
        $this->assertSame(1, DB::table('vendor_transactions')->where('order_item_id', $item->id)->where('type', 'earning')->count());
    }

    public function test_reverse_earning_flips_balance(): void
    {
        $svc = new VendorLedgerService();
        $vendor = $this->vendor();
        $item = $this->orderItem($vendor, 90.0);

        $svc->creditEarning($item);
        $svc->reverseEarning($item);

        $this->assertSame(0.0, $svc->payableBalance($vendor->id));
    }

    public function test_record_payout_reduces_balance_and_caps_at_payable(): void
    {
        $svc = new VendorLedgerService();
        $vendor = $this->vendor();
        $svc->creditEarning($this->orderItem($vendor, 90.0));

        $payout = $svc->recordPayout($vendor->id, 50.0, 'bank', 'REF1', null, null);
        $this->assertSame(50.0, (float) $payout->amount);
        $this->assertSame(40.0, $svc->payableBalance($vendor->id));

        $this->expectException(\InvalidArgumentException::class);
        $svc->recordPayout($vendor->id, 999.0, 'bank', 'REF2', null, null); // over balance
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzinapp-main && php artisan test --filter=VendorLedgerServiceTest`
Expected: FAIL — service/tables do not exist.

- [ ] **Step 3: Create the vendor_payouts migration**

`Modules/Vendor/database/migrations/2026_06_28_000004_create_vendor_payouts_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vendor_payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->index();
            $table->decimal('amount', 10, 2);
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('paid_at');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_payouts');
    }
};
```

- [ ] **Step 4: Create the vendor_transactions migration**

`Modules/Vendor/database/migrations/2026_06_28_000005_create_vendor_transactions_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vendor_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->index();
            $table->enum('type', ['earning', 'reversal', 'payout', 'adjustment']);
            $table->decimal('amount', 10, 2); // signed: + earning, - reversal/payout
            $table->unsignedBigInteger('order_item_id')->nullable()->index();
            $table->unsignedBigInteger('payout_id')->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_transactions');
    }
};
```

- [ ] **Step 5: Create the models**

`Modules/Vendor/app/Models/VendorPayout.php`:

```php
<?php

namespace Modules\Vendor\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPayout extends Model
{
    protected $table = 'vendor_payouts';

    protected $fillable = ['vendor_id', 'amount', 'method', 'reference', 'notes', 'paid_at', 'created_by'];

    protected $casts = ['paid_at' => 'datetime', 'amount' => 'decimal:2'];
}
```

`Modules/Vendor/app/Models/VendorTransaction.php`:

```php
<?php

namespace Modules\Vendor\Models;

use Illuminate\Database\Eloquent\Model;

class VendorTransaction extends Model
{
    protected $table = 'vendor_transactions';

    protected $fillable = ['vendor_id', 'type', 'amount', 'order_item_id', 'payout_id', 'description', 'created_by'];

    protected $casts = ['amount' => 'decimal:2'];
}
```

- [ ] **Step 6: Create the ledger service**

`Modules/Vendor/app/Services/VendorLedgerService.php`:

```php
<?php

namespace Modules\Vendor\Services;

use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\OrderItem;
use Modules\Vendor\Models\VendorPayout;
use Modules\Vendor\Models\VendorTransaction;

class VendorLedgerService
{
    public function creditEarning(OrderItem $item): void
    {
        if ($item->vendor_id === null || $item->vendor_earning === null) {
            return;
        }
        $exists = VendorTransaction::where('order_item_id', $item->id)->where('type', 'earning')->exists();
        if ($exists) {
            return; // idempotent
        }
        VendorTransaction::create([
            'vendor_id' => $item->vendor_id,
            'type' => 'earning',
            'amount' => (float) $item->vendor_earning,
            'order_item_id' => $item->id,
            'description' => 'Earning for order item #' . $item->id,
        ]);
    }

    public function reverseEarning(OrderItem $item): void
    {
        $earning = VendorTransaction::where('order_item_id', $item->id)->where('type', 'earning')->first();
        if (!$earning) {
            return;
        }
        $alreadyReversed = VendorTransaction::where('order_item_id', $item->id)->where('type', 'reversal')->exists();
        if ($alreadyReversed) {
            return;
        }
        VendorTransaction::create([
            'vendor_id' => $earning->vendor_id,
            'type' => 'reversal',
            'amount' => -1 * (float) $earning->amount,
            'order_item_id' => $item->id,
            'description' => 'Reversal for order item #' . $item->id,
        ]);
    }

    public function recordPayout(int $vendorId, float $amount, ?string $method, ?string $reference, ?string $notes, ?int $adminId): VendorPayout
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payout amount must be positive.');
        }
        if ($amount > $this->payableBalance($vendorId) + 0.001) {
            throw new \InvalidArgumentException('Payout exceeds the payable balance.');
        }

        return DB::transaction(function () use ($vendorId, $amount, $method, $reference, $notes, $adminId) {
            $payout = VendorPayout::create([
                'vendor_id' => $vendorId, 'amount' => $amount, 'method' => $method,
                'reference' => $reference, 'notes' => $notes, 'paid_at' => now(), 'created_by' => $adminId,
            ]);
            VendorTransaction::create([
                'vendor_id' => $vendorId, 'type' => 'payout', 'amount' => -1 * $amount,
                'payout_id' => $payout->id, 'created_by' => $adminId,
                'description' => 'Payout #' . $payout->id,
            ]);
            return $payout;
        });
    }

    public function adjust(int $vendorId, float $amount, string $description, ?int $adminId): void
    {
        VendorTransaction::create([
            'vendor_id' => $vendorId, 'type' => 'adjustment', 'amount' => $amount,
            'description' => $description, 'created_by' => $adminId,
        ]);
    }

    public function payableBalance(int $vendorId): float
    {
        return (float) VendorTransaction::where('vendor_id', $vendorId)->sum('amount');
    }

    public function pendingEarnings(int $vendorId): float
    {
        return (float) OrderItem::where('vendor_id', $vendorId)
            ->where('collection_status', '!=', 'collected')
            ->sum('vendor_earning');
    }

    public function totalPaid(int $vendorId): float
    {
        return (float) VendorPayout::where('vendor_id', $vendorId)->sum('amount');
    }
}
```

- [ ] **Step 7: Run test to verify it passes**

Run: `cd narzinapp-main && php artisan test --filter=VendorLedgerServiceTest`
Expected: PASS (3 tests).

- [ ] **Step 8: Commit**

```bash
git add narzinapp-main/Modules/Vendor/database/migrations/2026_06_28_000004_create_vendor_payouts_table.php \
        narzinapp-main/Modules/Vendor/database/migrations/2026_06_28_000005_create_vendor_transactions_table.php \
        narzinapp-main/Modules/Vendor/app/Models/VendorPayout.php \
        narzinapp-main/Modules/Vendor/app/Models/VendorTransaction.php \
        narzinapp-main/Modules/Vendor/app/Services/VendorLedgerService.php \
        narzinapp-main/tests/Feature/VendorLedgerServiceTest.php
git commit -m "feat(payouts): vendor ledger + payouts tables, models, ledger service"
```

---

### Task 4: Wire the triggers (snapshot on order, credit on collect, reverse on return/cancel)

**Files:**
- Modify: `narzinapp-main/Modules/Checkout/app/Http/Controllers/V1/Api/CheckoutController.php` (the `OrderItem::create` loop in `placeOrder`, ~line 287)
- Modify: `narzinapp-main/Modules/Admin/app/Http/Controllers/ShipmentController.php` (`collectItem` ~line 296-313, `collectVendor` ~line 367-373, `markUnavailable` ~line 437-443)
- Modify: `narzinapp-main/Modules/Admin/app/Http/Controllers/OrderController.php` (`updateStatus`, cancel branch ~line 189)
- Test: `narzinapp-main/tests/Feature/VendorPayoutTriggersTest.php`

**Interfaces:**
- Consumes: `VendorEarningCalculator`, `VendorRateResolver` (Tasks 1-2), `VendorLedgerService` (Task 3), `PayoutSetting`.
- Produces: order items created with snapshot fields populated; ledger rows created/reversed on collection/return transitions.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/VendorPayoutTriggersTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\Vendor\Models\Vendor;
use Modules\Vendor\Services\VendorLedgerService;
use Tests\TestCase;

class VendorPayoutTriggersTest extends TestCase
{
    use RefreshDatabase;

    public function test_collected_item_credits_then_cancel_reverses(): void
    {
        $u = User::create(['name' => 'V', 'email' => 'v' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        $vendor = Vendor::create(['user_id' => $u->id, 'store_name_in_arabic' => 'م', 'store_name_in_german' => 'L', 'status' => 'Active']);
        $cust = User::create(['name' => 'C', 'email' => 'c' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        $order = Order::create([
            'user_id' => $cust->id, 'order_number' => 'O' . uniqid(), 'payment_id' => 'P' . uniqid(),
            'total_amount' => 100, 'final_price' => 100, 'payment_status' => 'processing', 'order_status' => 'confirmed',
        ]);
        $item = OrderItem::create([
            'order_id' => $order->id, 'product_id' => 1, 'product_variant_id' => 1, 'vendor_id' => $vendor->id,
            'quantity' => 1, 'unit_price' => 100, 'subtotal' => 100, 'vendor_earning' => 90, 'collection_status' => 'pending',
        ]);

        $svc = new VendorLedgerService();
        $this->assertSame(0.0, $svc->payableBalance($vendor->id));

        // simulate collection transition handler
        $item->update(['collection_status' => 'collected']);
        $svc->creditEarning($item);
        $this->assertSame(90.0, $svc->payableBalance($vendor->id));

        // simulate cancel/return reversal
        $svc->reverseEarning($item);
        $this->assertSame(0.0, $svc->payableBalance($vendor->id));
    }
}
```

(This locks the ledger behavior the wiring must call. The wiring itself is verified by the existing `PlaceOrderTest` still passing plus manual/integration checks; keep this test focused on the credit/reverse contract.)

- [ ] **Step 2: Run test to verify it fails or passes against Task 3**

Run: `cd narzinapp-main && php artisan test --filter=VendorPayoutTriggersTest`
Expected: PASS (it exercises Task 3's service). If it fails, fix Task 3 wiring before proceeding.

- [ ] **Step 3: Snapshot earnings when creating order items**

In `CheckoutController@placeOrder`, replace the `OrderItem::create([...])` block (~line 287) so it also computes and stores the snapshot. Add `use` imports at the top of the file:

```php
use Modules\Vendor\Services\VendorEarningCalculator;
use Modules\Vendor\Services\VendorRateResolver;
```

Then in the loop (the surrounding `$vendor`, `$markup`, `$basePrice`, `$markedUpUnitPrice`, `$itemSubtotal`, `$discountAmount`, `$totalAmount` are already in scope):

```php
                    $resolver = new VendorRateResolver();
                    $calc = new VendorEarningCalculator();
                    $earning = $calc->compute(
                        (float) $basePrice,
                        (int) $item->quantity,
                        (float) $itemSubtotal,
                        (float) $discountAmount,   // order coupon discount
                        (float) $totalAmount,      // order pre-discount total
                        $vendor ? $resolver->commission($vendor) : 0.0,
                        $vendor ? $resolver->absorption($vendor) : 0.0
                    );

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'vendor_id' => $item->product->vendor_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $markedUpUnitPrice,
                        'subtotal' => $itemSubtotal,
                        'final_price' => $itemSubtotal,
                        'vendor_base_subtotal' => $earning['vendor_base_subtotal'],
                        'vendor_commission_amount' => $earning['vendor_commission_amount'],
                        'vendor_discount_absorbed' => $earning['vendor_discount_absorbed'],
                        'vendor_earning' => $earning['vendor_earning'],
                    ]);
```

- [ ] **Step 4: Credit on collect, reverse on un-collect**

In `ShipmentController`, add at the top:

```php
use Modules\Vendor\Services\VendorLedgerService;
```

In `collectItem`, after `$item->orderItem->update(['collection_status' => 'collected']);` (~line 304):

```php
            (new VendorLedgerService())->creditEarning($item->orderItem->fresh());
```

In `collectItem`, after the toggle-back `$item->orderItem->update(['collection_status' => 'pending']);` (~line 313):

```php
            (new VendorLedgerService())->reverseEarning($item->orderItem->fresh());
```

In `collectVendor`, after `$item->orderItem->update(['collection_status' => 'collected']);` (~line 373):

```php
            (new VendorLedgerService())->creditEarning($item->orderItem->fresh());
```

In `markUnavailable`, after `$orderItem->update(['collection_status' => 'unavailable']);` (~line 443):

```php
            (new VendorLedgerService())->reverseEarning($orderItem->fresh());
```

- [ ] **Step 5: Reverse on order cancel**

In `OrderController@updateStatus`, inside the cancel branch (`if ($request->order_status === 'cancelled' ...)`, ~line 189), after the order is marked cancelled, reverse each of its items:

```php
            $ledger = new \Modules\Vendor\Services\VendorLedgerService();
            foreach ($order->items as $orderItem) {
                $ledger->reverseEarning($orderItem);
            }
```

(Place this where `$order->items` is loaded; eager-load with `$order->load('items')` first if not already loaded.)

- [ ] **Step 6: Run tests**

Run: `cd narzinapp-main && php artisan test --filter="VendorPayoutTriggersTest|PlaceOrderTest"`
Expected: PASS (trigger contract + existing order placement still works).

- [ ] **Step 7: Commit**

```bash
git add narzinapp-main/Modules/Checkout/app/Http/Controllers/V1/Api/CheckoutController.php \
        narzinapp-main/Modules/Admin/app/Http/Controllers/ShipmentController.php \
        narzinapp-main/Modules/Admin/app/Http/Controllers/OrderController.php \
        narzinapp-main/tests/Feature/VendorPayoutTriggersTest.php
git commit -m "feat(payouts): snapshot earnings on order, credit on collect, reverse on return/cancel"
```

---

### Task 5: Admin UI — payouts index, statement, record payout, settings, vendor overrides

**Files:**
- Create: `narzinapp-main/Modules/Admin/app/Http/Controllers/VendorPayoutController.php`
- Modify: `narzinapp-main/Modules/Admin/routes/web.php` (add routes under `admin.auth`)
- Create: `narzinapp-main/Modules/Admin/resources/views/vendor-payouts/index.blade.php`
- Create: `narzinapp-main/Modules/Admin/resources/views/vendor-payouts/show.blade.php`
- Create: `narzinapp-main/Modules/Admin/resources/views/vendor-payouts/settings.blade.php`
- Modify: `narzinapp-main/Modules/Admin/resources/views/vendors/edit.blade.php` (add two override inputs)
- Test: `narzinapp-main/tests/Feature/VendorPayoutAdminTest.php`

**Interfaces:**
- Consumes: `VendorLedgerService`, `PayoutSetting`, `Vendor`, `VendorTransaction`, `VendorPayout`.
- Produces: admin routes `vendor-payouts.index`, `vendor-payouts.show`, `vendor-payouts.payout`, `vendor-payouts.adjust`, `vendor-payouts.settings`, `vendor-payouts.settings.save`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/VendorPayoutAdminTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\Vendor\Models\Vendor;
use Modules\Vendor\Services\VendorLedgerService;
use Tests\TestCase;

class VendorPayoutAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $a = User::create(['name' => 'A', 'email' => 'a' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        UserAdmin::create(['user_id' => $a->id, 'is_active' => 1]);
        return $a;
    }

    private function vendorWithBalance(float $earning): Vendor
    {
        $u = User::create(['name' => 'V', 'email' => 'v' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        $vendor = Vendor::create(['user_id' => $u->id, 'store_name_in_arabic' => 'م', 'store_name_in_german' => 'L', 'status' => 'Active']);
        $cust = User::create(['name' => 'C', 'email' => 'c' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        $order = Order::create(['user_id' => $cust->id, 'order_number' => 'O' . uniqid(), 'payment_id' => 'P' . uniqid(), 'total_amount' => 100, 'final_price' => 100, 'payment_status' => 'processing', 'order_status' => 'confirmed']);
        $item = OrderItem::create(['order_id' => $order->id, 'product_id' => 1, 'product_variant_id' => 1, 'vendor_id' => $vendor->id, 'quantity' => 1, 'unit_price' => 100, 'subtotal' => 100, 'vendor_earning' => $earning, 'collection_status' => 'collected']);
        (new VendorLedgerService())->creditEarning($item);
        return $vendor;
    }

    public function test_admin_can_record_payout_within_balance(): void
    {
        $vendor = $this->vendorWithBalance(90.0);
        $this->actingAs($this->admin())
            ->post(route('vendor-payouts.payout', $vendor->id), ['amount' => 50, 'method' => 'bank', 'reference' => 'R1'])
            ->assertRedirect();
        $this->assertSame(40.0, (new VendorLedgerService())->payableBalance($vendor->id));
    }

    public function test_payout_over_balance_is_rejected(): void
    {
        $vendor = $this->vendorWithBalance(90.0);
        $this->actingAs($this->admin())
            ->post(route('vendor-payouts.payout', $vendor->id), ['amount' => 999, 'method' => 'bank'])
            ->assertSessionHasErrors();
        $this->assertSame(90.0, (new VendorLedgerService())->payableBalance($vendor->id));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzinapp-main && php artisan test --filter=VendorPayoutAdminTest`
Expected: FAIL — routes/controller do not exist.

- [ ] **Step 3: Create the controller**

`Modules/Admin/app/Http/Controllers/VendorPayoutController.php`:

```php
<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\Models\PayoutSetting;
use Modules\Vendor\Models\Vendor;
use Modules\Vendor\Models\VendorPayout;
use Modules\Vendor\Models\VendorTransaction;
use Modules\Vendor\Services\VendorLedgerService;

class VendorPayoutController extends Controller
{
    public function __construct(private VendorLedgerService $ledger)
    {
    }

    public function index()
    {
        $vendors = Vendor::all()->map(function ($v) {
            return [
                'vendor' => $v,
                'pending' => $this->ledger->pendingEarnings($v->id),
                'payable' => $this->ledger->payableBalance($v->id),
                'paid' => $this->ledger->totalPaid($v->id),
            ];
        });
        return view('admin::vendor-payouts.index', compact('vendors'));
    }

    public function show($vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);
        $entries = VendorTransaction::where('vendor_id', $vendorId)->latest('id')->get();
        $payable = $this->ledger->payableBalance($vendorId);
        return view('admin::vendor-payouts.show', compact('vendor', 'entries', 'payable'));
    }

    public function payout(Request $request, $vendorId)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'nullable|string|max:100',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        try {
            $this->ledger->recordPayout($vendorId, (float) $data['amount'], $data['method'] ?? null, $data['reference'] ?? null, $data['notes'] ?? null, Auth::id());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }
        return redirect()->route('vendor-payouts.show', $vendorId)->with('success', 'Payout recorded.');
    }

    public function adjust(Request $request, $vendorId)
    {
        $data = $request->validate([
            'amount' => 'required|numeric',
            'description' => 'required|string|max:255',
        ]);
        $this->ledger->adjust($vendorId, (float) $data['amount'], $data['description'], Auth::id());
        return redirect()->route('vendor-payouts.show', $vendorId)->with('success', 'Adjustment recorded.');
    }

    public function settings()
    {
        $settings = PayoutSetting::current();
        return view('admin::vendor-payouts.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'default_commission_percentage' => 'required|numeric|min:0|max:100',
            'default_discount_absorption_percentage' => 'required|numeric|min:0|max:100',
        ]);
        PayoutSetting::create($data);
        return back()->with('success', 'Defaults updated.');
    }
}
```

- [ ] **Step 4: Add routes**

In `Modules/Admin/routes/web.php`, inside the existing `Route::middleware(['admin.auth'])->group(...)`, add:

```php
    Route::get('vendor-payouts', [\Modules\Admin\Http\Controllers\VendorPayoutController::class, 'index'])->name('vendor-payouts.index');
    Route::get('vendor-payouts/settings', [\Modules\Admin\Http\Controllers\VendorPayoutController::class, 'settings'])->name('vendor-payouts.settings');
    Route::post('vendor-payouts/settings', [\Modules\Admin\Http\Controllers\VendorPayoutController::class, 'saveSettings'])->name('vendor-payouts.settings.save');
    Route::get('vendor-payouts/{vendor}', [\Modules\Admin\Http\Controllers\VendorPayoutController::class, 'show'])->name('vendor-payouts.show');
    Route::post('vendor-payouts/{vendor}/payout', [\Modules\Admin\Http\Controllers\VendorPayoutController::class, 'payout'])->name('vendor-payouts.payout');
    Route::post('vendor-payouts/{vendor}/adjust', [\Modules\Admin\Http\Controllers\VendorPayoutController::class, 'adjust'])->name('vendor-payouts.adjust');
```

(Place the static `settings` routes before the `{vendor}` route so they aren't captured by the wildcard.)

- [ ] **Step 5: Create the Blade views**

Follow the existing admin layout used by `Modules/Admin/resources/views/platform-markup/*` and `vendors/*` (extend the same layout, e.g. `@extends('admin::layouts.app')` — confirm the exact layout name from a neighboring view). Minimum content:

`vendor-payouts/index.blade.php` — a table: vendor name, pending earnings, payable balance, total paid, and a link to `route('vendor-payouts.show', $row['vendor']->id)`. Loop over `$vendors`.

`vendor-payouts/show.blade.php` — show `$vendor` name and `$payable`; a "Record payout" `<form method="POST" action="{{ route('vendor-payouts.payout', $vendor->id) }}">` with `@csrf`, an `amount` input (default `{{ $payable }}`), `method`, `reference`, `notes`; render `$errors`. Then a table of `$entries` (type, amount, description, created_at). Optionally an adjustment form posting to `vendor-payouts.adjust`.

`vendor-payouts/settings.blade.php` — a `<form method="POST" action="{{ route('vendor-payouts.settings.save') }}">` with `@csrf`, two number inputs bound to `$settings->default_commission_percentage` and `$settings->default_discount_absorption_percentage`.

- [ ] **Step 6: Add the two override inputs to the vendor edit page**

In `Modules/Admin/resources/views/vendors/edit.blade.php`, near the existing `markup_percentage` field, add two number inputs named `commission_percentage` and `discount_absorption_percentage`, pre-filled from `$vendor->commission_percentage` / `$vendor->discount_absorption_percentage` (leave blank for null). Ensure the vendor update controller validates them as `nullable|numeric|min:0|max:100` and they are in `$fillable` (done in Task 1). If the vendor update path strips unknown fields, add these to its validation + save.

- [ ] **Step 7: Run tests**

Run: `cd narzinapp-main && php artisan test --filter=VendorPayoutAdminTest`
Expected: PASS (2 tests).

- [ ] **Step 8: Verify Blade compiles**

Run: `cd narzinapp-main && php artisan view:cache && php artisan view:clear`
Expected: "Blade templates cached successfully." with no error.

- [ ] **Step 9: Commit**

```bash
git add narzinapp-main/Modules/Admin/app/Http/Controllers/VendorPayoutController.php \
        narzinapp-main/Modules/Admin/routes/web.php \
        narzinapp-main/Modules/Admin/resources/views/vendor-payouts/ \
        narzinapp-main/Modules/Admin/resources/views/vendors/edit.blade.php \
        narzinapp-main/tests/Feature/VendorPayoutAdminTest.php
git commit -m "feat(payouts): admin payouts UI, statements, record payout, settings, vendor overrides"
```

---

## Deployment

After merge to `main`, the CI runs the five migrations via `deploy-api.sh`. Post-deploy: set the global **default commission %** and **default discount-absorption %** on the new settings page, and set any per-vendor overrides on the vendor edit pages.

## Self-review notes

- **Spec coverage:** per-vendor settings + global defaults (Task 1); earning snapshot + calculator (Task 2); ledger + payouts tables/service (Task 3); triggers — snapshot/credit/reverse (Task 4); admin UI + statements + settings + overrides (Task 5). Wallet-excluded and single-currency are honored by the calculator inputs (only coupon discount is passed).
- **Naming consistency:** `vendor_earning`, `vendor_base_subtotal`, `vendor_commission_amount`, `vendor_discount_absorbed` are identical across the migration, model fillable, calculator output keys, and the OrderItem::create. Service methods (`creditEarning`, `reverseEarning`, `recordPayout`, `adjust`, `payableBalance`, `pendingEarnings`, `totalPaid`) match between Task 3's definition and Tasks 4-5's usage.
- **Tests** run on sqlite with FKs; every test creates the parent `User`/`Vendor`/`Order`/`OrderItem` rows it needs.

# Promotions Engine (v1) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add admin-managed, auto-applied single-threshold promotions (free shipping over a cart amount; auto % or fixed cart discount) that integrate with checkout and the vendor-earning calculation.

**Architecture:** A `promotions` table of single-threshold rules + a pure `PromotionEvaluator` service that, given the cart subtotal and any coupon discount, returns the winning discount (best-one-wins vs the coupon), a free-shipping flag, and the funding absorption %. Wired into `CheckoutController@placeOrder` and the existing `VendorEarningCalculator` snapshot. Admin CRUD mirrors the coupons screen.

**Tech Stack:** Laravel 11 (modular, nwidart), MySQL (prod) / sqlite `:memory:` (tests, FKs enforced), Blade admin, PHPUnit.

## Global Constraints

- **Discounts are best-one-wins:** the larger of {best qualifying discount promo, applied coupon discount} is used; they never stack. **On a tie, the promotion wins** (so its absorption % applies).
- **Free shipping is separate** and always applies on top when any qualifying `free_shipping` promo exists; it sets `shippingCost = 0`. Free shipping is a **platform cost with zero vendor-earning impact** — `absorbed_by_vendor_percentage` is ignored for `free_shipping` promos.
- **Discount promo funding:** a `percentage`/`fixed` promo's discount is allocated to order items proportionally by item subtotal and absorbed by the **promo's** `absorbed_by_vendor_percentage` in the earning calc — *replacing* the vendor's own `discount_absorption_percentage` for that order. A coupon keeps using the vendor's own setting.
- **At most one discount source** (coupon OR promotion) applies per order.
- `percentage` value is a percent (≤ 100); `fixed` value is a € amount, **capped at the subtotal** (never negative total). `free_shipping` ignores `value`.
- "Active and in-window" = `is_active = true` AND (`start_date` null or ≤ today) AND (`end_date` null or ≥ today).
- All new columns are nullable; the feature is additive. Migrations use the `2026_06_28_*` prefix so they sort after existing migrations.
- Money is in the stored price currency. Tests run on sqlite with foreign keys enforced — create required parent rows.

---

### Task 1: `promotions` table, `Promotion` model, and `orders` columns

**Files:**
- Create: `narzinapp-main/Modules/Checkout/database/migrations/2026_06_28_000010_create_promotions_table.php`
- Create: `narzinapp-main/Modules/Checkout/database/migrations/2026_06_28_000011_add_promotion_columns_to_orders_table.php`
- Create: `narzinapp-main/Modules/Checkout/app/Models/Promotion.php`
- Modify: `narzinapp-main/Modules/Checkout/app/Models/Order.php` (add two `$fillable` entries)
- Test: `narzinapp-main/tests/Feature/PromotionModelTest.php`

**Interfaces:**
- Produces: `Promotion` model with `$fillable` and a query scope `scopeActive($query)` returning only active, in-window promotions. Columns: `name`, `type` (`free_shipping`|`percentage`|`fixed`), `value`, `minimum_cart_amount`, `absorbed_by_vendor_percentage`, `start_date`, `end_date`, `is_active`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/PromotionModelTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Checkout\Models\Promotion;
use Tests\TestCase;

class PromotionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_scope_returns_only_active_in_window_promotions(): void
    {
        Promotion::create(['name' => 'Live', 'type' => 'percentage', 'value' => 10, 'minimum_cart_amount' => 50, 'is_active' => true]);
        Promotion::create(['name' => 'Disabled', 'type' => 'percentage', 'value' => 10, 'minimum_cart_amount' => 50, 'is_active' => false]);
        Promotion::create(['name' => 'Expired', 'type' => 'fixed', 'value' => 5, 'minimum_cart_amount' => 50, 'is_active' => true, 'end_date' => now()->subDay()->toDateString()]);
        Promotion::create(['name' => 'Future', 'type' => 'fixed', 'value' => 5, 'minimum_cart_amount' => 50, 'is_active' => true, 'start_date' => now()->addDay()->toDateString()]);

        $names = Promotion::active()->pluck('name')->all();

        $this->assertSame(['Live'], $names);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzinapp-main && php artisan test --filter=PromotionModelTest`
Expected: FAIL — `Promotion` class / table does not exist.

- [ ] **Step 3: Create the promotions migration**

`Modules/Checkout/database/migrations/2026_06_28_000010_create_promotions_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['free_shipping', 'percentage', 'fixed']);
            $table->decimal('value', 8, 2)->nullable();
            $table->decimal('minimum_cart_amount', 8, 2)->default(0);
            $table->decimal('absorbed_by_vendor_percentage', 5, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
```

- [ ] **Step 4: Create the orders columns migration**

`Modules/Checkout/database/migrations/2026_06_28_000011_add_promotion_columns_to_orders_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->unsignedBigInteger('free_shipping_promotion_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['promotion_id', 'free_shipping_promotion_id']);
        });
    }
};
```

- [ ] **Step 5: Create the Promotion model**

`Modules/Checkout/app/Models/Promotion.php`:

```php
<?php

namespace Modules\Checkout\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $table = 'promotions';

    protected $fillable = [
        'name', 'type', 'value', 'minimum_cart_amount',
        'absorbed_by_vendor_percentage', 'start_date', 'end_date', 'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_cart_amount' => 'decimal:2',
        'absorbed_by_vendor_percentage' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            });
    }
}
```

- [ ] **Step 6: Add the two columns to `Order` `$fillable`**

In `Modules/Checkout/app/Models/Order.php`, add to the `$fillable` array:

```php
        'promotion_id',
        'free_shipping_promotion_id',
```

- [ ] **Step 7: Run test to verify it passes**

Run: `cd narzinapp-main && php artisan test --filter=PromotionModelTest`
Expected: PASS (1 test).

- [ ] **Step 8: Commit**

```bash
git add narzinapp-main/Modules/Checkout/database/migrations/2026_06_28_000010_create_promotions_table.php \
        narzinapp-main/Modules/Checkout/database/migrations/2026_06_28_000011_add_promotion_columns_to_orders_table.php \
        narzinapp-main/Modules/Checkout/app/Models/Promotion.php \
        narzinapp-main/Modules/Checkout/app/Models/Order.php \
        narzinapp-main/tests/Feature/PromotionModelTest.php
git commit -m "feat(promotions): promotions table, model with active scope, order columns"
```

---

### Task 2: `PromotionEvaluator` service + `PromotionResult`

**Files:**
- Create: `narzinapp-main/Modules/Checkout/app/Services/PromotionResult.php`
- Create: `narzinapp-main/Modules/Checkout/app/Services/PromotionEvaluator.php`
- Test: `narzinapp-main/tests/Feature/PromotionEvaluatorTest.php`

**Interfaces:**
- Consumes: `Promotion` model + its `active()` scope (Task 1).
- Produces:
  - `PromotionResult` — a readonly DTO with public props: `float $discountAmount`, `string $discountSource` (`coupon`|`promotion`|`none`), `?int $promotionId`, `bool $freeShipping`, `?int $freeShippingPromotionId`, `?float $absorbedByVendorPercentage`.
  - `PromotionEvaluator::evaluate(float $subtotal, float $couponDiscount): PromotionResult`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/PromotionEvaluatorTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Checkout\Models\Promotion;
use Modules\Checkout\Services\PromotionEvaluator;
use Tests\TestCase;

class PromotionEvaluatorTest extends TestCase
{
    use RefreshDatabase;

    private PromotionEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new PromotionEvaluator();
    }

    public function test_no_qualifying_promotion_returns_none(): void
    {
        Promotion::create(['name' => 'High bar', 'type' => 'percentage', 'value' => 10, 'minimum_cart_amount' => 500, 'is_active' => true]);
        $r = $this->evaluator->evaluate(100.0, 0.0);
        $this->assertSame('none', $r->discountSource);
        $this->assertSame(0.0, $r->discountAmount);
        $this->assertFalse($r->freeShipping);
    }

    public function test_percentage_promo_applies_when_threshold_met(): void
    {
        Promotion::create(['name' => '10% over 75', 'type' => 'percentage', 'value' => 10, 'minimum_cart_amount' => 75, 'absorbed_by_vendor_percentage' => 40, 'is_active' => true]);
        $r = $this->evaluator->evaluate(100.0, 0.0);
        $this->assertSame('promotion', $r->discountSource);
        $this->assertSame(10.0, $r->discountAmount);
        $this->assertSame(40.0, $r->absorbedByVendorPercentage);
    }

    public function test_fixed_promo_is_capped_at_subtotal(): void
    {
        Promotion::create(['name' => '200 off', 'type' => 'fixed', 'value' => 200, 'minimum_cart_amount' => 10, 'is_active' => true]);
        $r = $this->evaluator->evaluate(50.0, 0.0);
        $this->assertSame(50.0, $r->discountAmount);
    }

    public function test_best_one_wins_coupon_beats_promo(): void
    {
        Promotion::create(['name' => '5 off', 'type' => 'fixed', 'value' => 5, 'minimum_cart_amount' => 10, 'is_active' => true]);
        $r = $this->evaluator->evaluate(100.0, 15.0); // coupon 15 > promo 5
        $this->assertSame('coupon', $r->discountSource);
        $this->assertSame(15.0, $r->discountAmount);
        $this->assertNull($r->absorbedByVendorPercentage);
    }

    public function test_best_one_wins_promo_beats_coupon_and_ties_go_to_promo(): void
    {
        Promotion::create(['name' => '20 off', 'type' => 'fixed', 'value' => 20, 'minimum_cart_amount' => 10, 'absorbed_by_vendor_percentage' => 0, 'is_active' => true]);
        $r = $this->evaluator->evaluate(100.0, 20.0); // tie -> promo wins
        $this->assertSame('promotion', $r->discountSource);
        $this->assertSame(20.0, $r->discountAmount);
    }

    public function test_free_shipping_flag_is_independent_of_discount(): void
    {
        Promotion::create(['name' => 'Free ship over 100', 'type' => 'free_shipping', 'minimum_cart_amount' => 100, 'is_active' => true]);
        $r = $this->evaluator->evaluate(120.0, 15.0); // coupon still wins the discount slot
        $this->assertTrue($r->freeShipping);
        $this->assertSame('coupon', $r->discountSource);
        $this->assertSame(15.0, $r->discountAmount);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzinapp-main && php artisan test --filter=PromotionEvaluatorTest`
Expected: FAIL — service/DTO classes do not exist.

- [ ] **Step 3: Create the PromotionResult DTO**

`Modules/Checkout/app/Services/PromotionResult.php`:

```php
<?php

namespace Modules\Checkout\Services;

class PromotionResult
{
    public function __construct(
        public readonly float $discountAmount,
        public readonly string $discountSource,        // 'coupon' | 'promotion' | 'none'
        public readonly ?int $promotionId,
        public readonly bool $freeShipping,
        public readonly ?int $freeShippingPromotionId,
        public readonly ?float $absorbedByVendorPercentage,
    ) {
    }
}
```

- [ ] **Step 4: Create the PromotionEvaluator**

`Modules/Checkout/app/Services/PromotionEvaluator.php`:

```php
<?php

namespace Modules\Checkout\Services;

use Modules\Checkout\Models\Promotion;

class PromotionEvaluator
{
    public function evaluate(float $subtotal, float $couponDiscount): PromotionResult
    {
        $promos = Promotion::active()
            ->where('minimum_cart_amount', '<=', $subtotal)
            ->get();

        // Free shipping: any qualifying free_shipping promo applies (platform cost).
        $freeShipPromo = $promos->firstWhere('type', 'free_shipping');

        // Best discount promo (largest value).
        $bestPromo = null;
        $bestPromoValue = 0.0;
        foreach ($promos as $p) {
            if ($p->type === 'percentage') {
                $v = round($subtotal * (float) $p->value / 100, 2);
            } elseif ($p->type === 'fixed') {
                $v = min((float) $p->value, $subtotal);
            } else {
                continue; // free_shipping carries no discount
            }
            if ($v > $bestPromoValue) {
                $bestPromoValue = $v;
                $bestPromo = $p;
            }
        }

        // Best-one-wins vs the coupon; tie goes to the promotion.
        if ($bestPromo !== null && $bestPromoValue >= $couponDiscount && $bestPromoValue > 0) {
            return new PromotionResult(
                $bestPromoValue, 'promotion', $bestPromo->id,
                $freeShipPromo !== null, $freeShipPromo?->id,
                (float) $bestPromo->absorbed_by_vendor_percentage
            );
        }

        if ($couponDiscount > 0) {
            return new PromotionResult(
                $couponDiscount, 'coupon', null,
                $freeShipPromo !== null, $freeShipPromo?->id, null
            );
        }

        return new PromotionResult(
            0.0, 'none', null,
            $freeShipPromo !== null, $freeShipPromo?->id, null
        );
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `cd narzinapp-main && php artisan test --filter=PromotionEvaluatorTest`
Expected: PASS (6 tests).

- [ ] **Step 6: Commit**

```bash
git add narzinapp-main/Modules/Checkout/app/Services/PromotionResult.php \
        narzinapp-main/Modules/Checkout/app/Services/PromotionEvaluator.php \
        narzinapp-main/tests/Feature/PromotionEvaluatorTest.php
git commit -m "feat(promotions): PromotionEvaluator (best-one-wins, free shipping) + result DTO"
```

---

### Task 3: Wire promotions into checkout + earning calc

**Files:**
- Modify: `narzinapp-main/Modules/Checkout/app/Http/Controllers/V1/Api/CheckoutController.php` (`placeOrder`: after the coupon block ~L227-229, the shipping calc ~L237, `Order::create` ~L261, and the earning-snapshot loop ~L291-301)
- Test: `narzinapp-main/tests/Feature/PromotionCheckoutTest.php`

**Interfaces:**
- Consumes: `PromotionEvaluator::evaluate(float, float): PromotionResult` (Task 2), `Promotion` (Task 1), the existing `VendorEarningCalculator` / `VendorRateResolver`.
- Produces: orders persisted with `promotion_id` / `free_shipping_promotion_id`; shipping zeroed on free-shipping; the earning snapshot using the promo's absorption % when the discount source is `promotion`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/PromotionCheckoutTest.php`. This test calls the `PromotionEvaluator` and the `VendorEarningCalculator` together to lock the integration contract (the wiring inserts exactly this logic into `placeOrder`):

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Checkout\Models\Promotion;
use Modules\Checkout\Services\PromotionEvaluator;
use Modules\Vendor\Services\VendorEarningCalculator;
use Tests\TestCase;

class PromotionCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_shipping_does_not_change_vendor_earning(): void
    {
        Promotion::create(['name' => 'FS', 'type' => 'free_shipping', 'minimum_cart_amount' => 100, 'is_active' => true]);
        $r = (new PromotionEvaluator())->evaluate(120.0, 0.0);

        $this->assertTrue($r->freeShipping);
        $this->assertSame('none', $r->discountSource);

        // earning with no discount: base 120, commission 10%, absorption irrelevant (no discount)
        $earn = (new VendorEarningCalculator())->compute(120, 1, 120.0, $r->discountAmount, 120.0, 10.0, 50.0);
        $this->assertSame(108.0, $earn['vendor_earning']); // 120 - 12 commission - 0 absorbed
    }

    public function test_promo_discount_uses_promo_absorption_not_vendor(): void
    {
        Promotion::create(['name' => '30 off', 'type' => 'fixed', 'value' => 30, 'minimum_cart_amount' => 50, 'absorbed_by_vendor_percentage' => 100, 'is_active' => true]);
        $r = (new PromotionEvaluator())->evaluate(120.0, 10.0); // promo 30 beats coupon 10

        $this->assertSame('promotion', $r->discountSource);
        $this->assertSame(30.0, $r->discountAmount);
        $this->assertSame(100.0, $r->absorbedByVendorPercentage);

        // absorption % comes from the promo (100), not the vendor's own setting
        $absorption = $r->discountSource === 'promotion' ? $r->absorbedByVendorPercentage : 0.0;
        $earn = (new VendorEarningCalculator())->compute(120, 1, 120.0, $r->discountAmount, 120.0, 0.0, $absorption);
        // base 120, commission 0, absorbed = 30 * (120/120) * 100% = 30 -> earning 90
        $this->assertSame(90.0, $earn['vendor_earning']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails or passes against Tasks 1-2**

Run: `cd narzinapp-main && php artisan test --filter=PromotionCheckoutTest`
Expected: PASS (it exercises Tasks 1-2's classes). If it fails, fix Tasks 1-2 before wiring. This test locks the contract the wiring must follow.

- [ ] **Step 3: Add the `use` import**

At the top of `Modules/Checkout/app/Http/Controllers/V1/Api/CheckoutController.php`, add:

```php
use Modules\Checkout\Services\PromotionEvaluator;
```

- [ ] **Step 4: Evaluate promotions right after the coupon discount is computed**

In `placeOrder`, the coupon block sets `$discountAmount` (it is `0` when no coupon). Immediately **after** that block and **before** the delivery/shipping calc (around line 231, where `$discountAmount` is final from the coupon), insert:

```php
                // Promotions: best-one-wins vs the coupon; free shipping rides on top.
                $promoResult = (new PromotionEvaluator())->evaluate((float) $totalAmount, (float) $discountAmount);
                $discountAmount = $promoResult->discountAmount;
```

- [ ] **Step 5: Zero shipping when free-shipping qualifies**

Just **after** the existing `$shippingCost = max($deliveryMethod->base_price, $calculatedPriceByKg);` line, insert:

```php
                if ($promoResult->freeShipping) {
                    $shippingCost = 0;
                }
```

(`$priceAfterDiscount = $totalAmount - $discountAmount;` and `$finalAmount = $priceAfterDiscount + $shippingCost;` already follow and pick up the new values.)

- [ ] **Step 6: Persist the applied promotions on the order**

In the `Order::create([...])` array, add:

```php
                    'promotion_id' => $promoResult->promotionId,
                    'free_shipping_promotion_id' => $promoResult->freeShippingPromotionId,
```

- [ ] **Step 7: Use the promo's absorption % in the earning snapshot**

In the `OrderItem::create` loop, the earning is computed via `$calc->compute(...)` with the last argument currently `$vendor ? $resolver->absorption($vendor) : 0.0`. Replace that final argument so the absorption source depends on the discount source. Just before the `$calc->compute(` call, add:

```php
                    $absorptionPct = $promoResult->discountSource === 'promotion'
                        ? (float) $promoResult->absorbedByVendorPercentage
                        : ($vendor ? $resolver->absorption($vendor) : 0.0);
```

Then change the final argument of `$calc->compute(...)` from
`$vendor ? $resolver->absorption($vendor) : 0.0` to `$absorptionPct`.

- [ ] **Step 8: Run tests**

Run: `cd narzinapp-main && php artisan test --filter="PromotionCheckoutTest|PlaceOrderTest|VendorPayoutTriggersTest"`
Expected: PASS (promotion contract + existing order placement + earning triggers all still green).

- [ ] **Step 9: Commit**

```bash
git add narzinapp-main/Modules/Checkout/app/Http/Controllers/V1/Api/CheckoutController.php \
        narzinapp-main/tests/Feature/PromotionCheckoutTest.php
git commit -m "feat(promotions): apply promotions in checkout (best-one-wins, free shipping, promo absorption)"
```

---

### Task 4: Admin Promotions CRUD

**Files:**
- Create: `narzinapp-main/Modules/Admin/app/Http/Controllers/PromotionController.php`
- Modify: `narzinapp-main/Modules/Admin/routes/web.php` (routes under `admin.auth`)
- Create: `narzinapp-main/Modules/Admin/resources/views/promotions/index.blade.php`
- Create: `narzinapp-main/Modules/Admin/resources/views/promotions/create.blade.php`
- Create: `narzinapp-main/Modules/Admin/resources/views/promotions/edit.blade.php`
- Test: `narzinapp-main/tests/Feature/PromotionAdminTest.php`

**Interfaces:**
- Consumes: `Promotion` (Task 1).
- Produces: admin routes `promotions.index`, `promotions.create`, `promotions.store`, `promotions.edit`, `promotions.update`, `promotions.destroy`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/PromotionAdminTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Modules\Checkout\Models\Promotion;
use Tests\TestCase;

class PromotionAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $a = User::create(['name' => 'A', 'email' => 'a' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        UserAdmin::create(['user_id' => $a->id, 'is_active' => 1]);
        return $a;
    }

    public function test_admin_can_create_a_percentage_promotion(): void
    {
        $this->actingAs($this->admin())
            ->post(route('promotions.store'), [
                'name' => '10% over 75', 'type' => 'percentage', 'value' => 10,
                'minimum_cart_amount' => 75, 'absorbed_by_vendor_percentage' => 40, 'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('promotions', ['name' => '10% over 75', 'type' => 'percentage', 'value' => 10]);
    }

    public function test_free_shipping_promotion_does_not_require_value(): void
    {
        $this->actingAs($this->admin())
            ->post(route('promotions.store'), [
                'name' => 'Free ship 100', 'type' => 'free_shipping',
                'minimum_cart_amount' => 100, 'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('promotions', ['name' => 'Free ship 100', 'type' => 'free_shipping']);
    }

    public function test_percentage_promotion_requires_value(): void
    {
        $this->actingAs($this->admin())
            ->post(route('promotions.store'), [
                'name' => 'Bad', 'type' => 'percentage', 'minimum_cart_amount' => 50, 'is_active' => 1,
            ])
            ->assertSessionHasErrors('value');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzinapp-main && php artisan test --filter=PromotionAdminTest`
Expected: FAIL — routes/controller do not exist.

- [ ] **Step 3: Create the controller**

`Modules/Admin/app/Http/Controllers/PromotionController.php`:

```php
<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Checkout\Models\Promotion;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::latest('id')->get();
        return view('admin::promotions.index', compact('promotions'));
    }

    public function create()
    {
        return view('admin::promotions.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Promotion::create($data);
        return redirect()->route('promotions.index')->with('success', 'Promotion created.');
    }

    public function edit($id)
    {
        $promotion = Promotion::findOrFail($id);
        return view('admin::promotions.edit', compact('promotion'));
    }

    public function update(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->update($this->validateData($request));
        return redirect()->route('promotions.index')->with('success', 'Promotion updated.');
    }

    public function destroy($id)
    {
        Promotion::findOrFail($id)->delete();
        return redirect()->route('promotions.index')->with('success', 'Promotion deleted.');
    }

    private function validateData(Request $request): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'type' => 'required|in:free_shipping,percentage,fixed',
            'value' => 'nullable|numeric|min:0|required_if:type,percentage,fixed',
            'minimum_cart_amount' => 'required|numeric|min:0',
            'absorbed_by_vendor_percentage' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ];
        if ($request->input('type') === 'percentage') {
            $rules['value'] = 'required|numeric|min:0|max:100';
        }

        $data = $request->validate($rules);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        if ($data['type'] === 'free_shipping') {
            $data['value'] = null;
            $data['absorbed_by_vendor_percentage'] = 0;
        }

        return $data;
    }
```

- [ ] **Step 4: Add routes**

In `Modules/Admin/routes/web.php`, inside the existing `Route::middleware(['admin.auth'])->group(...)`, add:

```php
    Route::get('promotions', [\Modules\Admin\Http\Controllers\PromotionController::class, 'index'])->name('promotions.index');
    Route::get('promotions/create', [\Modules\Admin\Http\Controllers\PromotionController::class, 'create'])->name('promotions.create');
    Route::post('promotions', [\Modules\Admin\Http\Controllers\PromotionController::class, 'store'])->name('promotions.store');
    Route::get('promotions/{id}/edit', [\Modules\Admin\Http\Controllers\PromotionController::class, 'edit'])->name('promotions.edit');
    Route::put('promotions/{id}', [\Modules\Admin\Http\Controllers\PromotionController::class, 'update'])->name('promotions.update');
    Route::delete('promotions/{id}', [\Modules\Admin\Http\Controllers\PromotionController::class, 'destroy'])->name('promotions.destroy');
```

- [ ] **Step 5: Create the Blade views**

Mirror the existing coupons views. First open `Modules/Admin/resources/views/coupons/index.blade.php` (and `create`/`edit`) to copy the exact `@extends('admin::layouts.<name>')` and section structure. Minimum content:

`promotions/index.blade.php` — a table of `$promotions` (name, type, value, minimum_cart_amount, absorbed_by_vendor_percentage, date window, is_active) with Edit (`route('promotions.edit', $p->id)`) and Delete (a `<form method="POST" action="{{ route('promotions.destroy', $p->id) }}">@csrf @method('DELETE')`) actions, and a "Create" button linking to `route('promotions.create')`.

`promotions/create.blade.php` — `<form method="POST" action="{{ route('promotions.store') }}">@csrf` with inputs: `name`, a `type` `<select>` (free_shipping / percentage / fixed), `value`, `minimum_cart_amount`, `absorbed_by_vendor_percentage`, `start_date`, `end_date`, `is_active` checkbox. Render `$errors`. (A small JS toggle that hides `value` + `absorbed_by_vendor_percentage` when type = free_shipping is a nice touch but optional — the controller already nulls them.)

`promotions/edit.blade.php` — same form posting to `route('promotions.update', $promotion->id)` with `@method('PUT')`, pre-filled from `$promotion`.

- [ ] **Step 6: Run tests**

Run: `cd narzinapp-main && php artisan test --filter=PromotionAdminTest`
Expected: PASS (3 tests).

- [ ] **Step 7: Verify Blade compiles**

Run: `cd narzinapp-main && php artisan view:cache && php artisan view:clear`
Expected: "Blade templates cached successfully." with no error.

- [ ] **Step 8: Commit**

```bash
git add narzinapp-main/Modules/Admin/app/Http/Controllers/PromotionController.php \
        narzinapp-main/Modules/Admin/routes/web.php \
        narzinapp-main/Modules/Admin/resources/views/promotions/ \
        narzinapp-main/tests/Feature/PromotionAdminTest.php
git commit -m "feat(promotions): admin promotions CRUD (routes, controller, views)"
```

---

## Deployment

After merge to `main`, CI runs the two migrations via `deploy-api.sh`. Post-deploy: create promotions in the new admin **Promotions** screen (e.g. a `free_shipping` promo with `minimum_cart_amount = 100`, and a `percentage` promo `value = 10`, `minimum_cart_amount = 75`, `absorbed_by_vendor_percentage` as agreed with vendors).

## Self-review notes

- **Spec coverage:** promotions table + model + active scope (Task 1); evaluator with best-one-wins, fixed-cap, free-shipping flag, absorption source (Task 2); checkout integration — discount override, shipping zeroing, order persistence, promo-vs-vendor absorption in the earning calc (Task 3); admin CRUD + validation incl. free_shipping-not-requiring-value (Task 4). Funding model (free shipping = platform, no earning impact; discount absorbed by promo %) is enforced in Tasks 2-3.
- **Naming consistency:** `PromotionResult` props (`discountAmount`, `discountSource`, `promotionId`, `freeShipping`, `freeShippingPromotionId`, `absorbedByVendorPercentage`) are identical across the DTO, the evaluator return, and the checkout usage. `Promotion` columns match across migration, model fillable, evaluator, and controller validation.
- **Tie rule** (`>=` → promotion) is implemented in the evaluator and asserted in `test_best_one_wins_promo_beats_coupon_and_ties_go_to_promo`.
- Tests run on sqlite with FKs; promotion/evaluator tests need no parent rows, and the checkout-contract test composes Tasks 1-2 without touching orders.

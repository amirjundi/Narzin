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

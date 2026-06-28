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

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

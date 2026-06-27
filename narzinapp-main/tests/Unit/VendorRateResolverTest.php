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

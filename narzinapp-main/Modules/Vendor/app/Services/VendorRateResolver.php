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

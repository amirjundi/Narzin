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

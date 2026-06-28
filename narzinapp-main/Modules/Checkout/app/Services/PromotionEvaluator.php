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
                $v = min(round($subtotal * (float) $p->value / 100, 2), $subtotal);
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

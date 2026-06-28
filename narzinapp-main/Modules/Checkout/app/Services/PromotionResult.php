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

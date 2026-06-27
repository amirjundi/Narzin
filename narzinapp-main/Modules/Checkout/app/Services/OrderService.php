<?php

namespace Modules\Checkout\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\Admin\Models\Coupon;
use Modules\Admin\Models\DeliveryPrice;
use Modules\UserWallet\Models\UserWallet;

class OrderService
{
    /**
     * Deduct stock for cart items and return the history.
     */
    public function deductStock($cartItems)
    {
        $stockChanges = [];
        foreach ($cartItems as $item) {
            $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);

            if (!$variant) {
                throw new \Exception("Product variant not found");
            }

            if ($variant->stock < $item->quantity) {
                throw new \Exception(
                    "Insufficient stock for '{$item->product->name_arabic}'. " .
                    "Available: {$variant->stock}, Requested: {$item->quantity}"
                );
            }

            $stockChanges[] = [
                'variant_id' => $variant->id,
                'product_name' => $item->product->name_arabic,
                'old_stock' => $variant->stock,
                'quantity_reserved' => $item->quantity,
                'new_stock' => $variant->stock - $item->quantity
            ];

            $variant->decrement('stock', $item->quantity);
        }

        return $stockChanges;
    }

    /**
     * Calculate discount amount if coupon is applied.
     */
    public function applyCoupon($couponCode, $totalAmount, $userId)
    {
        if (!$couponCode) {
            return ['coupon' => null, 'discountAmount' => 0];
        }

        $couponData = Coupon::where('code', $couponCode)->where('is_active', true)->first();

        if ($couponData) {
            $alreadyUsed = Order::where('user_id', $userId)
                ->where('coupon_id', $couponData->id)
                ->whereNotIn('payment_status', ['failed', 'expired'])
                ->exists();

            if ($alreadyUsed) {
                throw new \Exception('You have already used this coupon.');
            }

            if ($couponData->start_date && now()->lt($couponData->start_date)) {
                throw new \Exception('This coupon is not yet active.');
            }
            if ($couponData->end_date && now()->gt($couponData->end_date)) {
                throw new \Exception('This coupon has expired.');
            }
            if ($couponData->usage_limit && $couponData->used >= $couponData->usage_limit) {
                throw new \Exception('This coupon has reached its usage limit.');
            }
            if ($couponData->minimum_cart_amount && $totalAmount < $couponData->minimum_cart_amount) {
                throw new \Exception("Minimum order amount for this coupon is {$couponData->minimum_cart_amount}.");
            }

            $discountAmount = $couponData->discount_type === 'percentage'
                ? $totalAmount * ($couponData->discount_amount / 100)
                : min($couponData->discount_amount, $totalAmount);

            return ['coupon' => $couponData, 'discountAmount' => $discountAmount];
        }

        return ['coupon' => null, 'discountAmount' => 0];
    }
}

<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;

/**
 * Read-only coupon/promotion performance over existing orders.
 * Discount per order = total_amount - price_after_discount (exact).
 * No new table; groups on the order's own coupon_id/promotion_id.
 */
class DiscountService
{
    public function byCoupon(DateRange $range): Collection
    {
        return Order::query()
            ->whereBetween('orders.created_at', [$range->from, $range->to])
            ->whereNotNull('coupon_id')
            ->leftJoin('coupons', 'orders.coupon_id', '=', 'coupons.id')
            ->groupBy('orders.coupon_id', 'coupons.code')
            ->selectRaw('orders.coupon_id as coupon_id')
            ->selectRaw('coupons.code as code')
            ->selectRaw('COUNT(*) as redemptions')
            ->selectRaw('SUM(orders.total_amount - orders.price_after_discount) as discount_given')
            ->selectRaw('SUM(orders.total_amount) as placed_value')
            ->get()
            ->map(fn ($r) => $this->row([
                'code' => $r->code ?? '(deleted)',
                'coupon_id' => (int) $r->coupon_id,
            ], $r))
            ->sortByDesc('discount_given')->values();
    }

    public function byPromotion(DateRange $range): Collection
    {
        return Order::query()
            ->whereBetween('orders.created_at', [$range->from, $range->to])
            ->whereNotNull('promotion_id')
            ->leftJoin('promotions', 'orders.promotion_id', '=', 'promotions.id')
            ->groupBy('orders.promotion_id', 'promotions.name')
            ->selectRaw('orders.promotion_id as promotion_id')
            ->selectRaw('promotions.name as name')
            ->selectRaw('COUNT(*) as redemptions')
            ->selectRaw('SUM(orders.total_amount - orders.price_after_discount) as discount_given')
            ->selectRaw('SUM(orders.total_amount) as placed_value')
            ->get()
            ->map(fn ($r) => $this->row([
                'name' => $r->name ?? '(deleted)',
                'promotion_id' => (int) $r->promotion_id,
            ], $r))
            ->sortByDesc('discount_given')->values();
    }

    public function summary(DateRange $range): array
    {
        $orders = Order::query()->whereBetween('created_at', [$range->from, $range->to]);
        $total = (clone $orders)->count();
        $discounted = (clone $orders)
            ->where(fn ($q) => $q->whereNotNull('coupon_id')->orWhereNotNull('promotion_id'))
            ->count();
        $totalDiscount = (clone $orders)
            ->where(fn ($q) => $q->whereNotNull('coupon_id')->orWhereNotNull('promotion_id'))
            ->sum(DB::raw('total_amount - price_after_discount'));

        return [
            'discounted_orders' => $discounted,
            'total_orders' => $total,
            'discount_rate' => $total > 0 ? round($discounted / $total, 4) : 0.0,
            'total_discount' => round((float) $totalDiscount, 2),
        ];
    }

    private function row(array $keys, $r): array
    {
        $redemptions = (int) $r->redemptions;
        $discount = round((float) $r->discount_given, 2);
        $placed = round((float) $r->placed_value, 2);
        return $keys + [
            'redemptions' => $redemptions,
            'discount_given' => $discount,
            'placed_value' => $placed,
            'aov' => $redemptions > 0 ? round($placed / $redemptions, 2) : 0.0,
        ];
    }
}

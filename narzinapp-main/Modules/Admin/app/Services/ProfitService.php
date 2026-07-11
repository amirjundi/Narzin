<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Facades\DB;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\Vendor\Models\VendorTransaction;

/**
 * Read-only platform profit = product revenue - vendor earnings, over stored
 * order/order_item data. Revenue (from orders) and vendor_earnings (from
 * order_items) are summed in SEPARATE queries so a per-order revenue is never
 * multiplied by item count via a join. No migration; no capture change.
 *
 * Caveat: orders before the vendor-earning system (2026-06-28) have
 * vendor_earning NULL; COALESCE(...,0) treats vendor cost as 0, overstating
 * profit for those orders. Default range (last 30 days) is unaffected.
 */
class ProfitService
{
    public function summary(DateRange $range): array
    {
        return [
            'placed' => $this->computeSet($range, paidOnly: false),
            'paid' => $this->computeSet($range, paidOnly: true),
            'commission_collected' => round((float) $this->itemQuery($range, true)
                ->sum(DB::raw('COALESCE(order_items.vendor_commission_amount, 0)')), 2),
            'total_owed_to_vendors' => round((float) VendorTransaction::sum('amount'), 2),
        ];
    }

    private function computeSet(DateRange $range, bool $paidOnly): array
    {
        $orders = Order::query()->whereBetween('created_at', [$range->from, $range->to]);
        if ($paidOnly) {
            $orders->where('payment_status', 'completed');
        }

        $revenue = round((float) $orders->sum(DB::raw('COALESCE(price_after_discount, total_amount)')), 2);
        $count = (clone $orders)->count();
        $vendorEarnings = round((float) $this->itemQuery($range, $paidOnly)
            ->sum(DB::raw('COALESCE(order_items.vendor_earning, 0)')), 2);
        $profit = round($revenue - $vendorEarnings, 2);

        return [
            'revenue' => $revenue,
            'vendor_earnings' => $vendorEarnings,
            'platform_profit' => $profit,
            'margin' => $revenue > 0 ? round($profit / $revenue, 4) : 0.0,
            'orders' => $count,
        ];
    }

    /** order_items joined to their order, range-bound, optionally paid-only. */
    private function itemQuery(DateRange $range, bool $paidOnly)
    {
        $q = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$range->from, $range->to]);
        if ($paidOnly) {
            $q->where('orders.payment_status', 'completed');
        }
        return $q;
    }
}

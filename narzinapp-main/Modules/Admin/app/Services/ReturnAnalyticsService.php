<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Collection;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderReturn;

/** Read-only return analytics over order_returns + orders. */
class ReturnAnalyticsService
{
    public function summary(DateRange $range): array
    {
        $counts = OrderReturn::query()
            ->whereBetween('requested_at', [$range->from, $range->to])
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $totalReturns = (int) $counts->sum();
        $orders = Order::query()->whereBetween('created_at', [$range->from, $range->to])->count();
        $totalRefunded = (float) OrderReturn::query()
            ->whereBetween('requested_at', [$range->from, $range->to])
            ->where('status', 'refunded')
            ->sum('refund_amount');

        return [
            'requested' => (int) ($counts['requested'] ?? 0),
            'approved' => (int) ($counts['approved'] ?? 0),
            'rejected' => (int) ($counts['rejected'] ?? 0),
            'refunded' => (int) ($counts['refunded'] ?? 0),
            'total_returns' => $totalReturns,
            'return_rate' => $orders > 0 ? round($totalReturns / $orders, 4) : 0.0,
            'total_refunded' => round($totalRefunded, 2),
        ];
    }

    public function byReason(DateRange $range): Collection
    {
        return OrderReturn::query()
            ->whereBetween('requested_at', [$range->from, $range->to])
            ->selectRaw('reason, COUNT(*) as c')
            ->groupBy('reason')
            ->orderByDesc('c')
            ->get()
            ->map(fn ($r) => ['reason' => $r->reason, 'count' => (int) $r->c]);
    }
}

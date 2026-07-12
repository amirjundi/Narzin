<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Collection;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\PaymentAttempt;

/**
 * Read-only payment health. Order-level (payment_status, wallet_usage) works
 * over existing orders; attempt-level (payment_attempts) fills in going forward.
 */
class PaymentAnalyticsService
{
    public function orderPaymentSummary(DateRange $range): array
    {
        $counts = Order::query()
            ->whereBetween('created_at', [$range->from, $range->to])
            ->selectRaw('payment_status, COUNT(*) as c')
            ->groupBy('payment_status')
            ->pluck('c', 'payment_status');

        $completed = (int) ($counts['completed'] ?? 0);
        $failed = (int) ($counts['failed'] ?? 0);
        $expired = (int) ($counts['expired'] ?? 0);
        $resolved = $completed + $failed + $expired;

        return [
            'completed' => $completed,
            'failed' => $failed,
            'expired' => $expired,
            'processing' => (int) ($counts['processing'] ?? 0),
            'not_paid' => (int) ($counts['not_paid'] ?? 0),
            'success_rate' => $resolved > 0 ? round($completed / $resolved, 4) : 0.0,
        ];
    }

    public function methodMix(DateRange $range): array
    {
        $base = Order::query()->whereBetween('created_at', [$range->from, $range->to]);
        $wallet = (clone $base)->where('wallet_usage', '>', 0)->count();
        $total = (clone $base)->count();

        return [
            'wallet_involved' => $wallet,
            'gateway_only' => $total - $wallet,
        ];
    }

    public function attemptSummary(DateRange $range): array
    {
        $counts = PaymentAttempt::query()
            ->whereBetween('occurred_at', [$range->from, $range->to])
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $success = (int) ($counts['success'] ?? 0);
        $failed = (int) ($counts['failed'] ?? 0);
        $resolved = $success + $failed;

        return [
            'total' => $success + $failed + (int) ($counts['initiated'] ?? 0),
            'success' => $success,
            'failed' => $failed,
            'initiated' => (int) ($counts['initiated'] ?? 0),
            'gateway_success_rate' => $resolved > 0 ? round($success / $resolved, 4) : 0.0,
        ];
    }

    public function failureReasons(DateRange $range): Collection
    {
        return PaymentAttempt::query()
            ->whereBetween('occurred_at', [$range->from, $range->to])
            ->where('status', 'failed')
            ->selectRaw('response_code, COUNT(*) as c')
            ->groupBy('response_code')
            ->orderByDesc('c')
            ->get()
            ->map(fn ($r) => ['response_code' => $r->response_code ?? '(none)', 'count' => (int) $r->c]);
    }
}

<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Collection;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderAudit;

/** Read-only fulfillment SLA (from order_audits) + cancellation breakdown. */
class FulfillmentService
{
    public function slaSummary(DateRange $range): array
    {
        // Orders placed in the window, with their placed timestamp.
        $orders = Order::query()
            ->whereBetween('created_at', [$range->from, $range->to])
            ->get(['id', 'created_at'])
            ->keyBy('id');

        if ($orders->isEmpty()) {
            return $this->emptySla();
        }

        // ONE audit query for all these orders; earliest row per (order,new_status).
        $audits = OrderAudit::query()
            ->whereIn('order_id', $orders->keys())
            ->whereIn('new_order_status', ['confirmed', 'shipped', 'delivered'])
            ->orderBy('created_at')
            ->get(['order_id', 'new_order_status', 'created_at']);

        // stamps[order_id][status] = first timestamp seen (Carbon).
        $stamps = [];
        foreach ($audits as $a) {
            $stamps[$a->order_id][$a->new_order_status] ??= $a->created_at;
        }

        $confirmToShip = [];
        $shipToDeliver = [];
        $placedToShip = [];

        foreach ($orders as $id => $order) {
            $s = $stamps[$id] ?? [];
            $confirmed = $s['confirmed'] ?? $order->created_at;
            $shipped = $s['shipped'] ?? null;
            $delivered = $s['delivered'] ?? null;

            if ($shipped) {
                $confirmToShip[] = $this->hours($confirmed, $shipped);
                $placedToShip[] = $this->hours($order->created_at, $shipped);
            }
            if ($shipped && $delivered) {
                $shipToDeliver[] = $this->hours($shipped, $delivered);
            }
        }

        $slaHours = (int) config('telemetry.fulfillment_sla_hours', 48);
        $breaches = array_filter($placedToShip, fn ($h) => $h > $slaHours);
        $breachRate = count($placedToShip) > 0
            ? round(count($breaches) / count($placedToShip), 4)
            : 0.0;

        return [
            'stages' => [
                'confirm_to_ship' => $this->stageStats($confirmToShip),
                'ship_to_deliver' => $this->stageStats($shipToDeliver),
                'placed_to_ship' => $this->stageStats($placedToShip),
            ],
            'breach_rate' => $breachRate,
            'sla_hours' => $slaHours,
        ];
    }

    public function cancellations(DateRange $range): array
    {
        $totalOrders = Order::query()
            ->whereBetween('created_at', [$range->from, $range->to])
            ->count();

        $byReason = Order::query()
            ->whereBetween('created_at', [$range->from, $range->to])
            ->whereIn('order_status', ['cancelled', 'canceled'])
            ->selectRaw("COALESCE(cancellation_reason, '(unspecified)') as reason, COUNT(*) as c")
            ->groupBy('reason')
            ->orderByDesc('c')
            ->get()
            ->map(fn ($r) => ['reason' => $r->reason, 'count' => (int) $r->c]);

        $totalCancelled = (int) $byReason->sum('count');

        return [
            'by_reason' => $byReason,
            'total_cancelled' => $totalCancelled,
            'total_orders' => $totalOrders,
            'cancellation_rate' => $totalOrders > 0
                ? round($totalCancelled / $totalOrders, 4)
                : 0.0,
        ];
    }

    /** Duration in hours between two datetimes (float, 2dp). */
    private function hours($from, $to): float
    {
        // diffInSeconds is order-independent (abs) on older Carbon; these are always from<=to.
        return round($from->diffInSeconds($to) / 3600, 2);
    }

    /** avg/median/p90 over a list of hour-durations, all computed in PHP. */
    private function stageStats(array $vals): array
    {
        $n = count($vals);
        if ($n === 0) {
            return ['count' => 0, 'avg_hours' => 0.0, 'median_hours' => 0.0, 'p90_hours' => 0.0];
        }
        sort($vals);
        return [
            'count' => $n,
            'avg_hours' => round(array_sum($vals) / $n, 2),
            'median_hours' => $this->percentile($vals, 0.5),
            'p90_hours' => $this->percentile($vals, 0.9),
        ];
    }

    /** Nearest-rank percentile on a pre-sorted array. */
    private function percentile(array $sorted, float $p): float
    {
        $n = count($sorted);
        $idx = (int) ceil($p * $n) - 1;
        $idx = max(0, min($idx, $n - 1));
        return round($sorted[$idx], 2);
    }

    private function emptySla(): array
    {
        $empty = ['count' => 0, 'avg_hours' => 0.0, 'median_hours' => 0.0, 'p90_hours' => 0.0];
        return [
            'stages' => [
                'confirm_to_ship' => $empty,
                'ship_to_deliver' => $empty,
                'placed_to_ship' => $empty,
            ],
            'breach_rate' => 0.0,
            'sla_hours' => (int) config('telemetry.fulfillment_sla_hours', 48),
        ];
    }
}

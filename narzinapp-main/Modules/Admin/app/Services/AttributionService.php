<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Collection;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;

/**
 * Read-only revenue attribution over orders' snapshotted UTM columns.
 * Uses COALESCE(col,'(none)') — portable across MySQL/Postgres/SQLite.
 */
class AttributionService
{
    public function byChannel(DateRange $range): Collection
    {
        return Order::query()
            ->whereBetween('created_at', [$range->from, $range->to])
            ->selectRaw("COALESCE(utm_source, '(none)') as source")
            ->selectRaw("COALESCE(utm_medium, '(none)') as medium")
            ->selectRaw("COUNT(*) as orders")
            ->selectRaw("SUM(total_amount) as revenue")
            ->groupByRaw("COALESCE(utm_source, '(none)'), COALESCE(utm_medium, '(none)')")
            ->get()
            ->map(fn ($r) => $this->row(['source' => $r->source, 'medium' => $r->medium], $r))
            ->sortByDesc('revenue')->values();
    }

    public function byCampaign(DateRange $range): Collection
    {
        return Order::query()
            ->whereBetween('created_at', [$range->from, $range->to])
            ->selectRaw("COALESCE(utm_campaign, '(none)') as campaign")
            ->selectRaw("COUNT(*) as orders")
            ->selectRaw("SUM(total_amount) as revenue")
            ->groupByRaw("COALESCE(utm_campaign, '(none)')")
            ->get()
            ->map(fn ($r) => $this->row(['campaign' => $r->campaign], $r))
            ->sortByDesc('revenue')->values();
    }

    private function row(array $keys, $r): array
    {
        $orders = (int) $r->orders;
        $revenue = round((float) $r->revenue, 2);
        return $keys + [
            'orders' => $orders,
            'revenue' => $revenue,
            'aov' => $orders > 0 ? round($revenue / $orders, 2) : 0.0,
        ];
    }
}

<?php

namespace Modules\Admin\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Support\DateRange;
use Modules\Telemetry\Models\CartEvent;
use Modules\Telemetry\Models\CheckoutEvent;
use Modules\Telemetry\Models\UserProductView;
use Modules\Telemetry\Models\VisitSession;

/**
 * Read-only conversion funnel over the Phase 1 capture tables. Each stage is a
 * count of distinct "actors" (session_id, else user:{user_id}) in the window.
 */
class FunnelService
{
    public function funnel(DateRange $range): array
    {
        $counts = [
            'sessions'       => $this->distinctActors(VisitSession::query()->whereBetween('first_seen_at', [$range->from, $range->to])),
            'product_view'   => $this->distinctActors(UserProductView::query()->whereBetween('created_at', [$range->from, $range->to])),
            'cart_add'       => $this->distinctActors(CartEvent::query()->where('action', 'add')->whereBetween('occurred_at', [$range->from, $range->to])),
            'checkout_start' => $this->distinctActors(CheckoutEvent::query()->where('step', 'checkout_start')->whereBetween('occurred_at', [$range->from, $range->to])),
            'placed'         => $this->distinctActors(CheckoutEvent::query()->where('step', 'placed')->whereBetween('occurred_at', [$range->from, $range->to])),
        ];

        $labels = [
            'sessions' => 'Sessions',
            'product_view' => 'Product View',
            'cart_add' => 'Add to Cart',
            'checkout_start' => 'Checkout Started',
            'placed' => 'Order Placed',
        ];

        $stages = [];
        $prev = null;
        foreach ($counts as $key => $count) {
            $stages[] = [
                'key' => $key,
                'label' => $labels[$key],
                'count' => $count,
                'conversion_from_prev' => $prev === null ? null : ($prev > 0 ? round($count / $prev, 4) : 0.0),
            ];
            $prev = $count;
        }

        $overall = $counts['sessions'] > 0 ? round($counts['placed'] / $counts['sessions'], 4) : 0.0;

        return ['stages' => $stages, 'overall_conversion' => $overall];
    }

    /**
     * Distinct actor count. Actor = session_id, else "user:{user_id}", else
     * excluded. Computed in PHP (not SQL) so it is portable across MySQL,
     * Postgres, and the SQLite test DB, which disagree on string concatenation.
     * ponytail: pulls distinct (session_id,user_id) pairs — bounded by actor
     * count; revisit with a keyed rollup table if the event tables get huge.
     */
    private function distinctActors(Builder $query): int
    {
        return $query->distinct()
            ->get(['session_id', 'user_id'])
            ->map(fn ($r) => $r->session_id ?? ($r->user_id !== null ? 'user:' . $r->user_id : null))
            ->filter()
            ->unique()
            ->count();
    }
}

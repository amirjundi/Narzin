<?php

namespace Modules\Admin\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Admin\Support\DateRange;
use Modules\Telemetry\Models\CartEvent;
use Modules\Telemetry\Models\CheckoutEvent;

/**
 * Read-only abandoned-cart report. A session is abandoned when, within the
 * range, it added to cart, never placed an order, and its last cart activity is
 * older than the window. Cart value = net cart state (last-write-wins per line,
 * removes drop the line).
 */
class AbandonedCartService
{
    public function abandoned(DateRange $range, ?int $windowHours = null): Collection
    {
        $windowHours = $windowHours ?? (int) config('telemetry.abandoned_cart_hours', 24);
        $cutoff = now()->subHours($windowHours);

        // Candidate sessions: added to cart within the range.
        $candidates = CartEvent::query()
            ->where('action', 'add')
            ->whereBetween('occurred_at', [$range->from, $range->to])
            ->whereNotNull('session_id')
            ->distinct()
            ->pluck('session_id')
            ->all();

        if (empty($candidates)) {
            return collect();
        }

        // Exclude any session (or its known user) that placed an order.
        $placedSessions = CheckoutEvent::query()
            ->where('step', 'placed')
            ->whereIn('session_id', $candidates)
            ->pluck('session_id')
            ->filter()
            ->all();
        $placedUsers = CheckoutEvent::query()
            ->where('step', 'placed')
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique()
            ->all();

        $rows = collect();

        foreach ($candidates as $sessionId) {
            if (in_array($sessionId, $placedSessions, true)) {
                continue;
            }

            $events = CartEvent::query()
                ->where('session_id', $sessionId)
                ->orderBy('occurred_at')
                ->get(['product_id', 'variant_id', 'action', 'quantity', 'unit_price', 'user_id', 'occurred_at']);

            $userId = $events->pluck('user_id')->filter()->first();
            if ($userId !== null && in_array($userId, $placedUsers, true)) {
                continue;
            }

            $lastActivity = $events->max('occurred_at');
            if ($lastActivity === null || $lastActivity->greaterThanOrEqualTo($cutoff)) {
                continue; // still active within the window
            }

            [$value, $items] = $this->netCart($events);
            if ($value <= 0 || $items <= 0) {
                continue; // fully removed cart isn't abandoned
            }

            $user = $userId !== null ? User::find($userId) : null;

            $rows->push([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'user_name' => $user?->name,
                'user_email' => $user?->email,
                'cart_value' => round($value, 2),
                'item_count' => $items,
                'last_activity_at' => $lastActivity,
                'age_hours' => (int) $lastActivity->diffInHours(now()),
            ]);
        }

        return $rows->sortByDesc('cart_value')->values();
    }

    /**
     * Net cart state from a session's ordered cart events. Last-write-wins on
     * quantity per (product, variant); a 'remove' drops the line.
     * Returns [value, itemUnits].
     */
    private function netCart(Collection $events): array
    {
        $state = [];
        foreach ($events as $e) {
            $key = $e->product_id . ':' . ($e->variant_id ?? '0');
            if ($e->action === 'remove') {
                unset($state[$key]);
                continue;
            }
            $state[$key] = ['qty' => (int) $e->quantity, 'price' => (float) ($e->unit_price ?? 0)];
        }

        $value = 0.0;
        $items = 0;
        foreach ($state as $line) {
            if ($line['qty'] > 0) {
                $value += $line['qty'] * $line['price'];
                $items += $line['qty'];
            }
        }
        return [$value, $items];
    }
}

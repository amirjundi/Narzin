<?php

namespace Modules\Telemetry\Services;

use Illuminate\Support\Facades\Log;
use Modules\Telemetry\Models\CartEvent;
use Modules\Telemetry\Models\CheckoutEvent;
use Modules\Telemetry\Models\SearchLog;
use Modules\Telemetry\Models\VisitSession;

/**
 * Best-effort behavioral capture. Every method swallows exceptions (logs and
 * continues) so a capture failure can NEVER break checkout, search, or login.
 */
class CaptureService
{
    public static function recordSession(string $sessionId, ?int $userId, array $attribution): void
    {
        try {
            $session = VisitSession::firstOrNew(['session_id' => $sessionId]);
            if (!$session->exists) {
                $session->utm_source   = $attribution['utm_source']   ?? null;
                $session->utm_medium   = $attribution['utm_medium']   ?? null;
                $session->utm_campaign = $attribution['utm_campaign'] ?? null;
                $session->utm_term     = $attribution['utm_term']     ?? null;
                $session->utm_content  = $attribution['utm_content']  ?? null;
                $session->referrer     = $attribution['referrer']     ?? null;
                $session->landing_url  = $attribution['landing_url']  ?? null;
                $session->first_seen_at = now();
            }
            if ($userId !== null) {
                $session->user_id = $userId;
            }
            $session->last_seen_at = now();
            $session->save();
        } catch (\Throwable $e) {
            Log::warning('CaptureService::recordSession failed', ['error' => $e->getMessage()]);
        }
    }

    public static function backfillUser(string $sessionId, int $userId): void
    {
        try {
            VisitSession::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->update(['user_id' => $userId, 'last_seen_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('CaptureService::backfillUser failed', ['error' => $e->getMessage()]);
        }
    }

    public static function recordCartEvent(string $sessionId, ?int $userId, int $productId, ?int $variantId, string $action, int $quantity, ?float $unitPrice): void
    {
        try {
            CartEvent::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'action' => $action,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('CaptureService::recordCartEvent failed', ['error' => $e->getMessage()]);
        }
    }

    public static function recordCheckoutEvent(?string $sessionId, ?int $userId, string $step, ?int $orderId): void
    {
        try {
            CheckoutEvent::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'step' => $step,
                'order_id' => $orderId,
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('CaptureService::recordCheckoutEvent failed', ['error' => $e->getMessage()]);
        }
    }

    public static function recordSearch(?string $sessionId, ?int $userId, string $query, int $resultsCount): void
    {
        try {
            $normalized = mb_strtolower(trim($query));
            if ($normalized === '') {
                return;
            }
            SearchLog::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'query' => mb_substr(trim($query), 0, 255),
                'normalized_query' => mb_substr($normalized, 0, 255),
                'results_count' => $resultsCount,
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('CaptureService::recordSearch failed', ['error' => $e->getMessage()]);
        }
    }
}

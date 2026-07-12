<?php

namespace Modules\Checkout\Services;

use Illuminate\Support\Facades\Log;
use Modules\Checkout\Models\PaymentAttempt;

/**
 * Best-effort payment-attempt capture. Swallows all exceptions (logs and
 * continues) so a capture failure can NEVER break checkout or payment.
 */
class PaymentAttemptRecorder
{
    public static function record(
        ?int $orderId,
        ?int $userId,
        string $gateway,
        string $status,
        ?string $responseCode,
        ?float $amount
    ): void {
        try {
            PaymentAttempt::create([
                'order_id' => $orderId,
                'user_id' => $userId,
                'gateway' => $gateway,
                'status' => $status,
                'response_code' => $responseCode,
                'amount' => $amount,
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('PaymentAttemptRecorder::record failed', ['error' => $e->getMessage()]);
        }
    }
}

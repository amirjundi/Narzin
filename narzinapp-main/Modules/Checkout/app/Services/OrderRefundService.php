<?php

namespace Modules\Checkout\Services;

use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderAudit;
use Modules\Checkout\Models\UserWallet;
use Modules\Checkout\Models\WalletTransaction;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\Vendor\Services\VendorLedgerService;

/**
 * Whole-order refund to wallet, extracted from OrderController::refundToWallet
 * so both the legacy admin button and the returns flow share one path.
 * Behavior-identical: transactional wallet credit + stock refill + vendor-ledger
 * reversal + audit + status → refunded/cancelled. Idempotent: a no-op (0.0) if
 * the order is already refunded.
 */
class OrderRefundService
{
    public function refundWholeOrder(Order $order, string $reason, ?int $adminId): float
    {
        if ($order->payment_status === 'refunded') {
            return 0.0; // already refunded — never double-credit
        }

        DB::beginTransaction();
        try {
            $wallet = UserWallet::firstOrCreate(['user_id' => $order->user_id], ['balance' => 0]);
            $refundAmount = (float) $order->final_price;
            $wallet->increment('balance', $refundAmount);

            WalletTransaction::create([
                'user_id' => $order->user_id,
                'wallet_id' => $wallet->id,
                'type' => 'order',
                'amount' => $refundAmount,
                'order_id' => $order->id,
            ]);

            $order->load('items');
            foreach ($order->items as $item) {
                ProductVariant::where('id', $item->product_variant_id)->increment('stock', $item->quantity);
            }

            $oldPaymentStatus = $order->payment_status;
            $oldOrderStatus = $order->order_status;
            $order->update([
                'payment_status' => 'refunded',
                'order_status' => 'cancelled',
                'notes' => ($order->notes ?? '') . ' | Refunded: ' . $reason,
            ]);

            $ledger = new VendorLedgerService();
            foreach ($order->items as $orderItem) {
                $ledger->reverseEarning($orderItem);
            }

            OrderAudit::create([
                'order_id' => $order->id,
                'action' => 'refunded',
                'old_payment_status' => $oldPaymentStatus,
                'new_payment_status' => 'refunded',
                'old_order_status' => $oldOrderStatus,
                'new_order_status' => 'cancelled',
                'triggered_by' => $adminId ? 'admin' : 'system',
                'user_id' => $adminId,
                'data' => ['refund_amount' => $refundAmount, 'reason' => $reason],
                'notes' => 'Order refunded to wallet',
                'created_at' => now(),
            ]);

            DB::commit();
            return $refundAmount;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

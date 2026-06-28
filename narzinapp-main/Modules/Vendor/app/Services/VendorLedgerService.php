<?php

namespace Modules\Vendor\Services;

use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\OrderItem;
use Modules\Vendor\Models\VendorPayout;
use Modules\Vendor\Models\VendorTransaction;

class VendorLedgerService
{
    public function creditEarning(OrderItem $item): void
    {
        if ($item->vendor_id === null || $item->vendor_earning === null) {
            return;
        }
        $exists = VendorTransaction::where('order_item_id', $item->id)->where('type', 'earning')->exists();
        if ($exists) {
            return; // idempotent
        }
        try {
            VendorTransaction::create([
                'vendor_id' => $item->vendor_id,
                'type' => 'earning',
                'amount' => (float) $item->vendor_earning,
                'order_item_id' => $item->id,
                'description' => 'Earning for order item #' . $item->id,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // already credited by a concurrent request — safe to ignore
        }
    }

    public function removeEarning(OrderItem $item): void
    {
        VendorTransaction::where('order_item_id', $item->id)
            ->whereIn('type', ['earning', 'reversal'])
            ->delete();
    }

    public function reverseEarning(OrderItem $item): void
    {
        $earning = VendorTransaction::where('order_item_id', $item->id)->where('type', 'earning')->first();
        if (!$earning) {
            return;
        }
        $alreadyReversed = VendorTransaction::where('order_item_id', $item->id)->where('type', 'reversal')->exists();
        if ($alreadyReversed) {
            return;
        }
        try {
            VendorTransaction::create([
                'vendor_id' => $earning->vendor_id,
                'type' => 'reversal',
                'amount' => -1 * (float) $earning->amount,
                'order_item_id' => $item->id,
                'description' => 'Reversal for order item #' . $item->id,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // already reversed by a concurrent request — safe to ignore
        }
    }

    public function recordPayout(int $vendorId, float $amount, ?string $method, ?string $reference, ?string $notes, ?int $adminId): VendorPayout
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payout amount must be positive.');
        }

        return DB::transaction(function () use ($vendorId, $amount, $method, $reference, $notes, $adminId) {
            $balance = (float) VendorTransaction::where('vendor_id', $vendorId)->lockForUpdate()->sum('amount');
            if (round($amount, 2) > round($balance, 2)) {
                throw new \InvalidArgumentException('Payout exceeds the payable balance.');
            }
            $payout = VendorPayout::create([
                'vendor_id' => $vendorId, 'amount' => $amount, 'method' => $method,
                'reference' => $reference, 'notes' => $notes, 'paid_at' => now(), 'created_by' => $adminId,
            ]);
            VendorTransaction::create([
                'vendor_id' => $vendorId, 'type' => 'payout', 'amount' => -1 * $amount,
                'payout_id' => $payout->id, 'created_by' => $adminId,
                'description' => 'Payout #' . $payout->id,
            ]);
            return $payout;
        });
    }

    public function adjust(int $vendorId, float $amount, string $description, ?int $adminId): void
    {
        VendorTransaction::create([
            'vendor_id' => $vendorId, 'type' => 'adjustment', 'amount' => $amount,
            'description' => $description, 'created_by' => $adminId,
        ]);
    }

    public function payableBalance(int $vendorId): float
    {
        return (float) VendorTransaction::where('vendor_id', $vendorId)->sum('amount');
    }

    public function pendingEarnings(int $vendorId): float
    {
        return (float) OrderItem::where('order_items.vendor_id', $vendorId)
            ->whereNotIn('order_items.collection_status', ['collected', 'unavailable'])
            ->whereHas('order', function ($q) {
                $q->whereNotIn('order_status', ['cancelled', 'expired']);
            })
            ->sum('vendor_earning');
    }

    public function totalPaid(int $vendorId): float
    {
        return (float) VendorPayout::where('vendor_id', $vendorId)->sum('amount');
    }
}

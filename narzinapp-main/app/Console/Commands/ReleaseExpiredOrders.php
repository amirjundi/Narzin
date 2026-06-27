<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderAudit;
use Modules\ProductManagement\Models\ProductVariant;

class ReleaseExpiredOrders extends Command
{
    protected $signature = 'orders:release-expired';
    protected $description = 'Release stock for unpaid orders older than 15 minutes';

    public function handle(): int
    {
        $this->info('Starting expired orders cleanup...');

        $expiredOrders = Order::with('items')
            ->whereIn('payment_status', ['not_paid', 'failed'])
            ->where('created_at', '<', now()->subMinutes(15))
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('No expired orders found.');
            return Command::SUCCESS;
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($expiredOrders as $order) {
            DB::beginTransaction();

            try {
                $oldPaymentStatus = $order->payment_status;
                $oldOrderStatus = $order->order_status;
                $stockChanges = [];

                // Refill stock
                foreach ($order->items as $item) {
                    $variant = ProductVariant::find($item->product_variant_id);
                    
                    if ($variant) {
                        $oldStock = $variant->stock;
                        $variant->increment('stock', $item->quantity);

                        $stockChanges[] = [
                            'variant_id' => $variant->id,
                            'product_name' => $item->product->name_arabic ?? 'Unknown',
                            'old_stock' => $oldStock,
                            'quantity_released' => $item->quantity,
                            'new_stock' => $oldStock + $item->quantity
                        ];
                    }
                }

                // Mark as expired
                $order->update([
                    'payment_status' => 'expired',
                    'order_status' => 'cancelled',
                ]);

                // AUDIT: Order expired by cron
                OrderAudit::create([
                    'order_id' => $order->id,
                    'action' => 'order_expired_by_cron',
                    'old_payment_status' => $oldPaymentStatus,
                    'new_payment_status' => 'expired',
                    'old_order_status' => $oldOrderStatus,
                    'new_order_status' => 'cancelled',
                    'data' => [
                        'stock_released' => $stockChanges,
                        'expired_after_minutes' => now()->diffInMinutes($order->created_at),
                        'order_created_at' => $order->created_at->toISOString()
                    ],
                    'triggered_by' => 'cron',
                    'user_id' => null,
                    'ip_address' => null,
                    'notes' => 'Order expired due to non-payment within 15 minutes. Stock released back to inventory.',
                    'created_at' => now()
                ]);

                DB::commit();
                $successCount++;

                Log::info('Expired order stock released', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number
                ]);

                $this->line("✓ Released: Order #{$order->order_number}");

            } catch (\Exception $e) {
                DB::rollBack();
                $errorCount++;

                Log::error('Failed to release expired order', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);

                $this->error("✗ Failed: Order #{$order->order_number}");
            }
        }

        $this->newLine();
        $this->info("Completed: {$successCount} released, {$errorCount} errors");

        return Command::SUCCESS;
    }
}
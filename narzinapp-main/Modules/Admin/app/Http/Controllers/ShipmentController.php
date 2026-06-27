<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Admin\Models\ShipmentBatch;
use Modules\Admin\Models\ShipmentBatchItem;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderAudit;
use Modules\Checkout\Models\OrderItem;
use Modules\Checkout\Models\UserWallet;
use Modules\Checkout\Models\WalletTransaction;
use Modules\Vendor\Models\Vendor;
use Modules\Vendor\Services\VendorLedgerService;

class ShipmentController extends Controller
{
    /**
     * List all shipment batches with filters
     */
    public function index(Request $request)
    {
        $query = ShipmentBatch::with(['admin', 'items']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Stats
        $stats = [
            'total_batches' => ShipmentBatch::count(),
            'active_batches' => ShipmentBatch::active()->count(),
            'today_batches' => ShipmentBatch::today()->count(),
            'pending_orders' => Order::whereIn('payment_status', ['processing', 'completed'])
                ->where('order_status', 'confirmed')
                ->count(),
        ];

        $batches = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin::shipments.index', compact('batches', 'stats'));
    }

    /**
     * Today's daily summary — which vendors to visit
     */
    public function dailySummary()
    {
        $todayBatches = ShipmentBatch::with(['items.vendor', 'items.orderItem.product', 'admin'])
            ->today()
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all uncollected items across today's batches, grouped by vendor
        $vendorSummary = ShipmentBatchItem::whereHas('batch', function ($q) {
                $q->whereDate('created_at', today())
                  ->whereIn('status', ['pending', 'collecting']);
            })
            ->with(['vendor', 'orderItem.product'])
            ->get()
            ->groupBy('vendor_id')
            ->map(function ($items, $vendorId) {
                $vendor = $items->first()->vendor;
                return [
                    'vendor' => $vendor,
                    'total_items' => $items->count(),
                    'collected' => $items->where('collection_status', 'collected')->count(),
                    'pending' => $items->where('collection_status', 'pending')->count(),
                    'unavailable' => $items->where('collection_status', 'unavailable')->count(),
                ];
            });

        // Count orders ready for batching (confirmed + paid, not yet in any active batch)
        $unbatchedOrderCount = $this->getUnbatchedOrderCount();

        return view('admin::shipments.daily', compact('todayBatches', 'vendorSummary', 'unbatchedOrderCount'));
    }

    /**
     * Show create batch form — lists confirmed orders with uncollected items
     */
    public function create()
    {
        // Get confirmed, paid orders that have at least one item NOT in an active batch
        $orders = Order::with([
                'items.product',
                'items.product.images',
                'items.product.vendor',
                'items.productVariant',
                'items.productVariant.variantValues',
                'items.productVariant.variantValues.variantAttribute',
                'user',
                'address',
            ])
            ->whereIn('payment_status', ['processing', 'completed'])
            ->where('order_status', 'confirmed')
            ->orderBy('created_at', 'asc')
            ->get();

        // Filter to only orders that have unbatched items
        $activeItemIds = ShipmentBatchItem::whereHas('batch', function ($q) {
            $q->whereIn('status', ['pending', 'collecting', 'collected']);
        })->pluck('order_item_id')->toArray();

        $orders = $orders->filter(function ($order) use ($activeItemIds) {
            return $order->items->contains(function ($item) use ($activeItemIds) {
                return !in_array($item->id, $activeItemIds);
            });
        });

        // Group items by vendor for preview
        $vendorGroups = collect();
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if (in_array($item->id, $activeItemIds)) continue;

                $vendorId = $item->vendor_id ?? 0;
                if (!$vendorGroups->has($vendorId)) {
                    $vendor = $item->product->vendor ?? null;
                    $vendorGroups[$vendorId] = [
                        'vendor' => $vendor,
                        'items' => collect(),
                        'order_count' => 0,
                    ];
                }
                $vendorGroups[$vendorId]['items']->push([
                    'order_item' => $item,
                    'order' => $order,
                ]);
            }
        }

        // Count unique orders per vendor
        foreach ($vendorGroups as $vendorId => &$group) {
            $group['order_count'] = $group['items']->pluck('order.id')->unique()->count();
        }

        return view('admin::shipments.create', compact('orders', 'vendorGroups', 'activeItemIds'));
    }

    /**
     * Store a new shipment batch
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Get active batch item IDs to prevent duplicates
        $activeItemIds = ShipmentBatchItem::whereHas('batch', function ($q) {
            $q->whereIn('status', ['pending', 'collecting', 'collected']);
        })->pluck('order_item_id')->toArray();

        DB::beginTransaction();

        try {
            $batch = ShipmentBatch::create([
                'batch_number' => ShipmentBatch::generateBatchNumber(),
                'status' => 'pending',
                'admin_id' => Auth::id(),
                'notes' => $request->notes,
            ]);

            $itemCount = 0;

            foreach ($request->order_ids as $orderId) {
                $order = Order::with('items')->find($orderId);

                if (!$order || !in_array($order->payment_status, ['processing', 'completed']) || $order->order_status !== 'confirmed') {
                    continue;
                }

                foreach ($order->items as $item) {
                    // Skip if already in an active batch
                    if (in_array($item->id, $activeItemIds)) {
                        continue;
                    }

                    ShipmentBatchItem::create([
                        'shipment_batch_id' => $batch->id,
                        'order_item_id' => $item->id,
                        'order_id' => $order->id,
                        'vendor_id' => $item->vendor_id,
                        'collection_status' => 'pending',
                    ]);

                    $itemCount++;
                }
            }

            if ($itemCount === 0) {
                DB::rollBack();
                return redirect()->back()->with('error', 'No eligible items found for batching. Items may already be in another batch.');
            }

            $batch->update(['total_items' => $itemCount]);

            DB::commit();

            return redirect()->route('shipments.show', $batch->id)
                ->with('success', "Batch {$batch->batch_number} created with {$itemCount} items.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch creation failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to create batch: ' . $e->getMessage());
        }
    }

    /**
     * Show shipment batch — the main fulfillment dashboard
     */
    public function show($id)
    {
        $batch = ShipmentBatch::with(['admin'])->findOrFail($id);

        // Get items grouped by vendor
        $vendorGroups = $batch->items()
            ->with([
                'vendor',
                'orderItem.product',
                'orderItem.product.images',
                'orderItem.productVariant',
                'orderItem.productVariant.variantValues',
                'orderItem.productVariant.variantValues.variantAttribute',
                'order.user',
                'order.address',
                'order.address.city',
                'order.address.country',
            ])
            ->get()
            ->groupBy('vendor_id');

        // Get items grouped by order for customer packing
        $customerGroups = $batch->items()
            ->with([
                'vendor',
                'orderItem.product',
                'orderItem.product.images',
                'orderItem.productVariant',
                'orderItem.productVariant.variantValues',
                'orderItem.productVariant.variantValues.variantAttribute',
                'order.user',
                'order.address',
                'order.address.city',
                'order.address.country',
            ])
            ->get()
            ->groupBy('order_id');

        // Vendor stats
        $vendorStats = $vendorGroups->map(function ($items, $vendorId) {
            return [
                'total' => $items->count(),
                'collected' => $items->where('collection_status', 'collected')->count(),
                'pending' => $items->where('collection_status', 'pending')->count(),
                'unavailable' => $items->where('collection_status', 'unavailable')->count(),
            ];
        });

        return view('admin::shipments.show', compact('batch', 'vendorGroups', 'customerGroups', 'vendorStats'));
    }

    /**
     * Toggle a single item's collection status (AJAX)
     */
    public function collectItem(Request $request, $id)
    {
        $request->validate([
            'item_id' => 'required|exists:shipment_batch_items,id',
        ]);

        $batch = ShipmentBatch::findOrFail($id);

        if (!in_array($batch->status, ['pending', 'collecting'])) {
            return response()->json(['status' => false, 'message' => 'Batch is not in collecting state'], 400);
        }

        $item = ShipmentBatchItem::where('shipment_batch_id', $batch->id)
            ->where('id', $request->item_id)
            ->firstOrFail();

        // Toggle between pending and collected
        if ($item->collection_status === 'pending') {
            $item->update([
                'collection_status' => 'collected',
                'collected_at' => now(),
                'collected_by' => Auth::id(),
            ]);

            // Sync to order item
            $item->orderItem->update(['collection_status' => 'collected']);
            (new VendorLedgerService())->creditEarning($item->orderItem->fresh());
        } elseif ($item->collection_status === 'collected') {
            $item->update([
                'collection_status' => 'pending',
                'collected_at' => null,
                'collected_by' => null,
            ]);

            // Sync to order item
            $item->orderItem->update(['collection_status' => 'pending']);
            (new VendorLedgerService())->reverseEarning($item->orderItem->fresh());
        }

        // Update batch status to collecting if it was pending
        if ($batch->status === 'pending') {
            $batch->update([
                'status' => 'collecting',
                'started_at' => now(),
            ]);
        }

        $batch->recalculateCounters();

        // Check if batch is fully resolved
        if ($batch->is_complete) {
            $batch->update([
                'status' => 'collected',
                'completed_at' => now(),
            ]);
        }

        return response()->json([
            'status' => true,
            'item_status' => $item->fresh()->collection_status,
            'batch_progress' => $batch->fresh()->progress_percentage,
            'batch_collected' => $batch->fresh()->collected_items,
            'batch_total' => $batch->total_items,
            'batch_status' => $batch->fresh()->status,
            'is_complete' => $batch->fresh()->is_complete,
        ]);
    }

    /**
     * Mark all items from a vendor as collected (AJAX)
     */
    public function collectVendor(Request $request, $id)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        $batch = ShipmentBatch::findOrFail($id);

        if (!in_array($batch->status, ['pending', 'collecting'])) {
            return response()->json(['status' => false, 'message' => 'Batch is not in collecting state'], 400);
        }

        $items = ShipmentBatchItem::where('shipment_batch_id', $batch->id)
            ->where('vendor_id', $request->vendor_id)
            ->where('collection_status', 'pending')
            ->get();

        foreach ($items as $item) {
            $item->update([
                'collection_status' => 'collected',
                'collected_at' => now(),
                'collected_by' => Auth::id(),
            ]);

            // Sync to order item
            $item->orderItem->update(['collection_status' => 'collected']);
            (new VendorLedgerService())->creditEarning($item->orderItem->fresh());
        }

        // Update batch status
        if ($batch->status === 'pending') {
            $batch->update([
                'status' => 'collecting',
                'started_at' => now(),
            ]);
        }

        $batch->recalculateCounters();

        if ($batch->is_complete) {
            $batch->update([
                'status' => 'collected',
                'completed_at' => now(),
            ]);
        }

        return response()->json([
            'status' => true,
            'collected_count' => $items->count(),
            'batch_progress' => $batch->fresh()->progress_percentage,
            'batch_collected' => $batch->fresh()->collected_items,
            'batch_total' => $batch->total_items,
            'batch_status' => $batch->fresh()->status,
            'is_complete' => $batch->fresh()->is_complete,
        ]);
    }

    /**
     * Mark item as unavailable + auto-refund to customer wallet (AJAX)
     */
    public function markUnavailable(Request $request, $id)
    {
        $request->validate([
            'item_id' => 'required|exists:shipment_batch_items,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $batch = ShipmentBatch::findOrFail($id);

        if (!in_array($batch->status, ['pending', 'collecting'])) {
            return response()->json(['status' => false, 'message' => 'Batch is not in collecting state'], 400);
        }

        $batchItem = ShipmentBatchItem::where('shipment_batch_id', $batch->id)
            ->where('id', $request->item_id)
            ->with(['orderItem', 'order'])
            ->firstOrFail();

        if ($batchItem->collection_status === 'unavailable') {
            return response()->json(['status' => false, 'message' => 'Item is already marked as unavailable'], 400);
        }

        DB::beginTransaction();

        try {
            $orderItem = $batchItem->orderItem;
            $refundAmount = (float) $orderItem->final_price;

            // 1. Mark batch item as unavailable
            $batchItem->update([
                'collection_status' => 'unavailable',
                'refund_amount' => $refundAmount,
                'notes' => $request->notes ?? 'Item unavailable at vendor',
            ]);

            // 2. Sync to order item
            $orderItem->update(['collection_status' => 'unavailable']);
            (new VendorLedgerService())->reverseEarning($orderItem->fresh());

            // 3. Refund to customer wallet
            $order = $batchItem->order;
            $wallet = UserWallet::firstOrCreate(
                ['user_id' => $order->user_id],
                ['balance' => 0]
            );

            $wallet->increment('balance', $refundAmount);

            WalletTransaction::create([
                'user_id' => $order->user_id,
                'wallet_id' => $wallet->id,
                'type' => 'refund',
                'amount' => $refundAmount,
                'order_id' => $order->id,
            ]);

            // 4. Update order total
            $order->update([
                'final_price' => max(0, $order->final_price - $refundAmount),
                'notes' => ($order->notes ?? '') . ' | Item refunded: ' . ($orderItem->product->name_arabic ?? 'Product') . ' (IQD' . number_format($refundAmount, 2) . ')',
            ]);

            // 5. Refill stock for the unavailable item
            $variant = $orderItem->productVariant;
            if ($variant) {
                $variant->increment('stock', $orderItem->quantity);
            }

            // 6. Log audit
            OrderAudit::create([
                'order_id' => $order->id,
                'action' => 'item_unavailable_refunded',
                'triggered_by' => 'admin',
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'data' => [
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'order_item_id' => $orderItem->id,
                    'product_name' => $orderItem->product->name_arabic ?? 'Product',
                    'refund_amount' => $refundAmount,
                    'notes' => $request->notes,
                ],
                'notes' => 'Item marked unavailable during collection, refunded to wallet',
                'created_at' => now(),
            ]);

            // 7. Update batch status
            if ($batch->status === 'pending') {
                $batch->update([
                    'status' => 'collecting',
                    'started_at' => now(),
                ]);
            }

            $batch->recalculateCounters();

            if ($batch->is_complete) {
                $batch->update([
                    'status' => 'collected',
                    'completed_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Item marked unavailable. IQD' . number_format($refundAmount, 2) . ' refunded to customer wallet.',
                'refund_amount' => $refundAmount,
                'batch_progress' => $batch->fresh()->progress_percentage,
                'batch_collected' => $batch->fresh()->collected_items,
                'batch_total' => $batch->total_items,
                'batch_status' => $batch->fresh()->status,
                'is_complete' => $batch->fresh()->is_complete,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mark unavailable failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update batch status (mark as shipped, delivered, etc.)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,collecting,collected,shipped,delivered',
            'notes' => 'nullable|string|max:500',
        ]);

        $batch = ShipmentBatch::findOrFail($id);
        $newStatus = $request->status;

        // Validation: can't ship if items are still pending
        if ($newStatus === 'shipped') {
            $pendingCount = $batch->items()->where('collection_status', 'pending')->count();
            if ($pendingCount > 0) {
                return redirect()->back()->with('error', "Cannot ship: {$pendingCount} items are still pending collection. Complete the checklist first.");
            }
        }

        DB::beginTransaction();

        try {
            $updateData = [
                'status' => $newStatus,
                'notes' => $request->notes ? (($batch->notes ?? '') . ' | ' . $request->notes) : $batch->notes,
            ];

            if ($newStatus === 'shipped') {
                $updateData['shipped_at'] = now();
            }

            if ($newStatus === 'collected' && !$batch->completed_at) {
                $updateData['completed_at'] = now();
            }

            $batch->update($updateData);

            // When marking as shipped, update all orders in this batch
            if ($newStatus === 'shipped') {
                $orderIds = $batch->items()->pluck('order_id')->unique();

                foreach ($orderIds as $orderId) {
                    $order = Order::find($orderId);
                    if ($order && in_array($order->order_status, ['confirmed', 'processing'])) {
                        $oldStatus = $order->order_status;
                        $order->update(['order_status' => 'shipped']);

                        // Mark collected items as completed
                        $order->items()
                            ->where('collection_status', 'collected')
                            ->update(['status' => 'completed']);

                        // Log audit
                        OrderAudit::create([
                            'order_id' => $order->id,
                            'action' => 'shipped_via_batch',
                            'old_order_status' => $oldStatus,
                            'new_order_status' => 'shipped',
                            'triggered_by' => 'admin',
                            'user_id' => Auth::id(),
                            'ip_address' => $request->ip(),
                            'data' => [
                                'batch_id' => $batch->id,
                                'batch_number' => $batch->batch_number,
                            ],
                            'notes' => "Order shipped via batch {$batch->batch_number}",
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with('success', "Batch status updated to {$newStatus}.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch status update failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to update batch status.');
        }
    }

    /**
     * Printable pickup list
     */
    public function printPickupList($id)
    {
        $batch = ShipmentBatch::with(['admin'])->findOrFail($id);

        $vendorGroups = $batch->items()
            ->with([
                'vendor',
                'orderItem.product',
                'orderItem.product.images',
                'orderItem.productVariant',
                'orderItem.productVariant.variantValues',
                'orderItem.productVariant.variantValues.variantAttribute',
                'order.user',
                'order.address',
            ])
            ->get()
            ->groupBy('vendor_id');

        $customerGroups = $batch->items()
            ->with([
                'vendor',
                'orderItem.product',
                'orderItem.productVariant',
                'order.user',
                'order.address',
                'order.address.city',
                'order.address.country',
            ])
            ->get()
            ->groupBy('order_id');

        return view('admin::shipments.print', compact('batch', 'vendorGroups', 'customerGroups'));
    }

    /**
     * Helper: count orders ready for batching
     */
    private function getUnbatchedOrderCount(): int
    {
        $activeItemIds = ShipmentBatchItem::whereHas('batch', function ($q) {
            $q->whereIn('status', ['pending', 'collecting', 'collected']);
        })->pluck('order_item_id')->toArray();

        return Order::whereIn('payment_status', ['processing', 'completed'])
            ->where('order_status', 'confirmed')
            ->whereHas('items', function ($q) use ($activeItemIds) {
                $q->whereNotIn('id', $activeItemIds);
            })
            ->count();
    }
}

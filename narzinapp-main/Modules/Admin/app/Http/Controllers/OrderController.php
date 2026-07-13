<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Admin\Models\Status;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderAudit;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\UserAddress\Models\UserAddress;

class OrderController extends Controller
{
    /**
     * Display all orders with filters
     */
    public function index(Request $request)
    {
        $query = Order::with(['address', 'address.country', 'address.city', 'user', 'items.product', 'items.productVariant']);

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by order status
        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by order number or user
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('payment_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Get statistics
        $stats = [
            'total_orders' => Order::count(),
            'pending_payment' => Order::where('payment_status', 'not_paid')->count(),
            'processing' => Order::where('payment_status', 'processing')->count(),
            'completed' => Order::where('payment_status', 'completed')->count(),
            'expired' => Order::where('payment_status', 'expired')->count(),
            'failed' => Order::where('payment_status', 'failed')->count(),
            'total_revenue' => Order::whereIn('payment_status', ['processing', 'completed'])->sum('final_price'),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::whereDate('created_at', today())
                ->whereIn('payment_status', ['processing', 'completed'])
                ->sum('final_price'),
        ];

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin::orders.index', compact('orders', 'stats'));
    }

    /**
     * Show pending orders (not paid, waiting)
     */
    public function pendingOrders()
    {
        $orders = Order::with(['address', 'user', 'items'])
            ->where('payment_status', 'not_paid')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $title = 'Pending Payment Orders';
        $subtitle = 'Orders waiting for payment (within 15 min window)';

        return view('admin::orders.filtered', compact('orders', 'title', 'subtitle'));
    }

    /**
     * Show expired orders
     */
    public function expiredOrders()
    {
        $orders = Order::with(['address', 'user', 'items'])
            ->where('payment_status', 'expired')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $title = 'Expired Orders';
        $subtitle = 'Orders that were not paid within 15 minutes';

        return view('admin::orders.filtered', compact('orders', 'title', 'subtitle'));
    }

    /**
     * Show confirmed orders ready for processing
     */
    public function confirmedOrders()
    {
        $orders = Order::with(['address', 'user', 'items'])
            ->whereIn('payment_status', ['processing', 'completed'])
            ->where('order_status', 'confirmed')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $title = 'Confirmed Orders';
        $subtitle = 'Paid orders ready for processing';

        return view('admin::orders.filtered', compact('orders', 'title', 'subtitle'));
    }

    /**
     * Show shipped orders
     */
    public function shippedOrders()
    {
        $orders = Order::with(['address', 'user', 'items'])
            ->where('order_status', 'shipped')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        $title = 'Shipped Orders';
        $subtitle = 'Orders that have been shipped';

        return view('admin::orders.filtered', compact('orders', 'title', 'subtitle'));
    }

    /**
     * Show single order details
     */
    public function show($id)
    {
        $order = Order::with([
            'address',
            'address.country',
            'address.city',
            'user',
            'items.product',
            'items.product.images',
            'items.productVariant',
            'items.productVariant.variantValues',
            'items.productVariant.variantValues.variantAttribute',
            'items.vendor',
            'coupon'
        ])->findOrFail($id);

        // Get audit history
        $audits = OrderAudit::where('order_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin::orders.show', compact('order', 'audits'));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'order_status' => 'required|in:pending_payment,confirmed,processing,shipped,delivered,cancelled',
            'notes' => 'nullable|string|max:500',
            'cancellation_reason' => 'nullable|in:out_of_stock,customer_request,fraud_suspected,pricing_error,other',
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->order_status;

        // If cancelling, refill stock and reverse vendor ledger entries
        if ($request->order_status === 'cancelled' && !in_array($oldStatus, ['cancelled', 'expired'])) {
            $this->refillOrderStock($order);
            $order->load('items');
            $ledger = new \Modules\Vendor\Services\VendorLedgerService();
            foreach ($order->items as $orderItem) {
                $ledger->reverseEarning($orderItem);
            }
        }

        $updates = [
            'order_status' => $request->order_status,
            'notes' => $request->notes ? ($order->notes . ' | Admin: ' . $request->notes) : $order->notes
        ];
        if ($request->order_status === 'cancelled') {
            $updates['cancellation_reason'] = $request->cancellation_reason;
        }
        $order->update($updates);

        // Log audit
        OrderAudit::create([
            'order_id' => $order->id,
            'action' => 'status_updated_by_admin',
            'old_order_status' => $oldStatus,
            'new_order_status' => $request->order_status,
            'triggered_by' => 'admin',
            'user_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'notes' => $request->notes ?? 'Order status updated by admin',
            'data' => $request->order_status === 'cancelled'
                ? ['cancellation_reason' => $request->cancellation_reason]
                : null,
            'created_at' => now()
        ]);

        return redirect()->back()->with('success', 'Order status updated successfully');
    }

    /**
     * Bulk update orders to shipped
     */
    public function bulkShipped(Request $request)
    {
            if (is_string($request->order_ids)) {
        $request->merge([
            'order_ids' => json_decode($request->order_ids, true)
        ]);
    }
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id'
        ]);


        $count = 0;

        foreach ($request->order_ids as $orderId) {
            $order = Order::find($orderId);

            if ($order && in_array($order->order_status, ['confirmed', 'processing'])) {
                $oldStatus = $order->order_status;

                $order->update(['order_status' => 'shipped']);

                // Update all items to completed
                $order->items()->update(['status' => 'completed']);

                // Log audit
                OrderAudit::create([
                    'order_id' => $order->id,
                    'action' => 'bulk_shipped_by_admin',
                    'old_order_status' => $oldStatus,
                    'new_order_status' => 'shipped',
                    'triggered_by' => 'admin',
                    'user_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                    'notes' => 'Bulk shipped by admin',
                    'created_at' => now()
                ]);

                $count++;
            }
        }

        return redirect()->back()->with('success', "{$count} orders marked as shipped");
    }

    /**
     * Bulk update orders to processing
     */
    public function bulkProcessing(Request $request)
    {
            if (is_string($request->order_ids)) {
        $request->merge([
            'order_ids' => json_decode($request->order_ids, true)
        ]);
    }
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id'
        ]);

        $count = 0;

        foreach ($request->order_ids as $orderId) {
            $order = Order::find($orderId);

            if ($order && $order->order_status === 'confirmed') {
                $order->update(['order_status' => 'processing']);

                OrderAudit::create([
                    'order_id' => $order->id,
                    'action' => 'bulk_processing_by_admin',
                    'old_order_status' => 'confirmed',
                    'new_order_status' => 'processing',
                    'triggered_by' => 'admin',
                    'user_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                    'notes' => 'Bulk set to processing by admin',
                    'created_at' => now()
                ]);

                $count++;
            }
        }

        return redirect()->back()->with('success', "{$count} orders marked as processing");
    }

    /**
     * Print order
     */
    public function printOrder($id)
    {
        $order = Order::with([
            'items.product',
            'items.productVariant',
            'items.productVariant.variantValues',
            'items.productVariant.variantValues.variantAttribute',
            'items.vendor',
            'user',
            'address',
            'address.country',
            'address.city'
        ])->findOrFail($id);

        return view('admin::orders.print', compact('order'));
    }

    /**
     * Export orders to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = Order::with(['address', 'user']);

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('admin::orders.pdf', compact('orders'));

        return $pdf->download('orders-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export orders to CSV
     */
    public function exportCsv(Request $request)
    {
        $query = Order::with(['address', 'user']);

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $fileName = 'orders-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Order Number',
                'Payment ID',
                'Customer Name',
                'Customer Email',
                'Address',
                'Phone',
                'Total Amount',
                'Discount',
                'Shipping',
                'Final Price',
                'Payment Status',
                'Order Status',
                'Shipping Type',
                'Created At',
                'Paid At'
            ]);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->payment_id,
                    $order->user->name ?? 'N/A',
                    $order->user->email ?? 'N/A',
                    $order->address->address ?? 'N/A',
                    $order->address->phone_number ?? 'N/A',
                    number_format($order->total_amount, 2),
                    number_format($order->total_amount - $order->price_after_discount, 2),
                    number_format($order->shipping_cost, 2),
                    number_format($order->final_price, 2),
                    $order->payment_status,
                    $order->order_status,
                    $order->shipping_type,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Refund order to wallet
     */
    public function refundToWallet(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if (!in_array($order->payment_status, ['processing', 'completed'])) {
            return redirect()->back()->with('error', 'Only paid orders can be refunded');
        }

        try {
            $amount = (new \Modules\Checkout\Services\OrderRefundService())
                ->refundWholeOrder($order, $request->reason ?? 'No reason provided', Auth::id());
            return redirect()->back()->with('success', "Order refunded. IQD{$amount} added to customer wallet.");
        } catch (\Throwable $e) {
            Log::error('Refund failed', ['order_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Refund failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Refill stock for order
     */
    private function refillOrderStock(Order $order): void
    {
        foreach ($order->items as $item) {
            ProductVariant::where('id', $item->product_variant_id)
                ->increment('stock', $item->quantity);
        }
    }
}
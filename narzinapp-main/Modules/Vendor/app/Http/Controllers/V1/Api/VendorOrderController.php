<?php

namespace Modules\Vendor\Http\Controllers\V1\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\Vendor\Models\Vendor;

class VendorOrderController extends Controller
{
    public function getOrders(Request $request)
    {
        try {
            $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();

            $query = Order::with(['items' => function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id)
                    ->with(['product', 'product.images', 'productVariant', 'productVariant.variantValues', 'productVariant.variantValues.variantAttribute']);
            }, 'user', 'address', 'status'])
                ->whereHas('items', function ($q) use ($vendor) {
                    $q->where('vendor_id', $vendor->id);
                })
                ->latest();

            // sort=dec
            if ($request->has('sort')) {
                $sort = $request->sort;
                if ($sort == 'desc') {
                    $query->orderBy('created_at', 'desc');
                } elseif ($sort == 'asc') {
                    $query->orderBy('created_at', 'asc');
                } else {
                    $query->orderBy('created_at', 'asc');
                }
            }

            if ($request->has('status')) {
                $query->whereHas('items', function ($q) use ($request) {
                    $q->where('status', $request->status);
                });
            }

            $orders = $query->paginate(10);
            $totalRevenue = DB::table('order_items')
                ->where('vendor_id', $vendor->id)
                ->sum('subtotal');

            // Update response with corrected revenue
            return response()->json([
                'status' => true,
                'data' => [
                    'orders' => $orders,
                    'total_revenue' => $totalRevenue
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateOrderStatus(Request $request, $orderItemId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,completed,rejected'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $vendorId   = Vendor::where('user_id', Auth::id())->firstOrFail()->id;

            $orderItem = OrderItem::where('id', $orderItemId)
                ->where('vendor_id', $vendorId)
                ->first();



            $oldStatus = $orderItem->getOriginal('status');
            $orderItem->status = $request->status;
            $orderItem->save();

            // Send notification to user
            $order = $orderItem->order;
            if ($order && $order->user) {
                $order->user->notify(new \App\Notifications\OrderStatusChangedNotification($order, $oldStatus, $request->status));
            }

            return response()->json([
                'status' => true,
                'message' => 'Order status updated successfully',
                'data' => $orderItem->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getOrderStatistics(Request $request)
    {
        try {
            $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
            $vendorId = $vendor->id;
            // Get counts for different statuses
            $pendingCount = OrderItem::where('vendor_id', $vendorId)
                ->where('status', 'pending')
                ->count();

            $processingCount = OrderItem::where('vendor_id', $vendorId)
                ->where('status', 'processing')
                ->count();

            $shippedCount = OrderItem::where('vendor_id', $vendorId)
                ->where('status', 'shipped')
                ->count();

            $deliveredCount = OrderItem::where('vendor_id', $vendorId)
                ->where('status', 'delivered')
                ->count();

            $totalRevenue = OrderItem::where('vendor_id', $vendorId)
                ->where('status', '!=', 'cancelled')
                ->sum('subtotal');

            $totalCost = OrderItem::where('vendor_id', $vendorId)
                ->where('status', '!=', 'cancelled')
                ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
                ->sum(DB::raw('order_items.quantity * product_variants.cost'));

            $recentOrders = OrderItem::where('vendor_id', $vendorId)
                ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
                ->count();

                // // weekly=true&daily=true&monthly=true
                // if($request->has('daily')) {

                // }
                

            $statistics = [
                'pending' => $pendingCount,
                'processing' => $processingCount,
                'shipped' => $shippedCount,
                'delivered' => $deliveredCount,
                'total_revenue' => $totalRevenue,
                'total_cost' => $totalCost,
                'total_profit' => $totalRevenue - $totalCost,
                'recent_orders' => $recentOrders,
            ];

            return response()->json([
                'status' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

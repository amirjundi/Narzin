<?php

namespace Modules\Checkout\Http\Controllers\V1\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderReturn;

class ReturnController extends Controller
{
    private const REASONS = ['damaged', 'wrong_item', 'not_as_described', 'no_longer_needed', 'other'];

    public function store(Request $request, $orderId)
    {
        $request->validate([
            'reason' => 'required|in:' . implode(',', self::REASONS),
            'note' => 'nullable|string|max:1000',
        ]);

        $order = Order::findOrFail($orderId);

        if ((int) $order->user_id !== (int) Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Not your order'], 403);
        }
        if (!in_array($order->payment_status, ['completed', 'processing'])) {
            return response()->json(['status' => false, 'message' => 'Only paid orders can be returned'], 422);
        }
        $active = OrderReturn::where('order_id', $order->id)
            ->whereIn('status', ['requested', 'approved', 'refunded'])->exists();
        if ($active) {
            return response()->json(['status' => false, 'message' => 'A return already exists for this order'], 422);
        }

        $return = OrderReturn::create([
            'order_id' => $order->id,
            'order_item_id' => null,
            'user_id' => Auth::id(),
            'reason' => $request->reason,
            'status' => 'requested',
            'admin_note' => $request->note,
            'requested_at' => now(),
        ]);

        return response()->json(['status' => true, 'data' => $return], 201);
    }

    public function index()
    {
        $returns = OrderReturn::where('user_id', Auth::id())
            ->with('order')
            ->orderByDesc('requested_at')
            ->get();

        return response()->json(['status' => true, 'data' => $returns]);
    }
}

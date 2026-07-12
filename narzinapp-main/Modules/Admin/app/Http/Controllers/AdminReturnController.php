<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Checkout\Models\OrderReturn;
use Modules\Checkout\Services\OrderRefundService;

class AdminReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = OrderReturn::with(['order', 'user'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('requested_at')
            ->paginate(30);

        return view('admin::returns.index', compact('returns'));
    }

    public function approve(Request $request, $id)
    {
        $return = OrderReturn::findOrFail($id);
        if ($return->status !== 'requested') {
            return response()->json(['message' => 'Only requested returns can be approved'], 422);
        }
        $return->update(['status' => 'approved', 'admin_note' => $request->admin_note, 'resolved_at' => now()]);
        return redirect()->back()->with('success', 'Return approved');
    }

    public function reject(Request $request, $id)
    {
        $return = OrderReturn::findOrFail($id);
        if ($return->status !== 'requested') {
            return response()->json(['message' => 'Only requested returns can be rejected'], 422);
        }
        $return->update(['status' => 'rejected', 'admin_note' => $request->admin_note, 'resolved_at' => now()]);
        return redirect()->back()->with('success', 'Return rejected');
    }

    public function refund(Request $request, $id)
    {
        $return = OrderReturn::with('order')->findOrFail($id);
        if ($return->status !== 'approved') {
            return response()->json(['message' => 'Only approved returns can be refunded'], 422);
        }

        $amount = (new OrderRefundService())->refundWholeOrder(
            $return->order, 'Return: ' . $return->reason, Auth::id()
        );

        $return->update(['status' => 'refunded', 'refund_amount' => $amount, 'resolved_at' => now()]);

        return redirect()->back()->with('success', "Return refunded. IQD{$amount} to wallet.");
    }
}

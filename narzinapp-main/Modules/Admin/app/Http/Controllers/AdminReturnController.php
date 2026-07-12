<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $notApproved = false;
        $amount = 0.0;

        DB::transaction(function () use ($id, $request, &$notApproved, &$amount, &$return) {
            $locked = OrderReturn::with('order')->where('id', $id)->lockForUpdate()->first();

            if ($locked->status !== 'approved') {
                $notApproved = true;
                return;
            }

            $amount = (new OrderRefundService())->refundWholeOrder(
                $locked->order, 'Return: ' . $locked->reason, Auth::id()
            );

            $locked->update([
                'status' => 'refunded',
                'refund_amount' => $amount > 0 ? $amount : $locked->refund_amount,
                'resolved_at' => now(),
            ]);

            $return = $locked;
        });

        if ($notApproved) {
            return response()->json(['message' => 'Only approved returns can be refunded'], 422);
        }

        return redirect()->back()->with('success', "Return refunded. IQD{$amount} to wallet.");
    }
}

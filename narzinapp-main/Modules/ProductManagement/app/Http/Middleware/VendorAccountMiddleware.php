<?php

namespace Modules\ProductManagement\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Vendor\Models\Vendor;

class VendorAccountMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();

        if (!$vendor) {
            return response()->json([
                'status' => false,
                'message' => 'You must have a vendor account to perform this action'
            ], 403);
        }

        // The vendors table ships an approval workflow (status defaults to
        // 'Waiting Approve'). Enforce it: only admin-approved vendors may
        // perform vendor actions such as creating products / uploading images.
        if ($vendor->status !== 'Active') {
            return response()->json([
                'status' => false,
                'message' => $vendor->status === 'Rejected'
                    ? 'Your vendor account has been rejected.'
                    : 'Your vendor account is pending approval.'
            ], 403);
        }

        // Add vendor to request for later use
        $request->merge(['current_vendor' => $vendor]);

        return $next($request);
    }
}
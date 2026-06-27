<?php

namespace Modules\ProductManagement\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\ProductManagement\Models\Product;
use Modules\Vendor\Models\Vendor;

class VendorProductOwnershipMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $productId = $request->route('id'); // or 'product' depending on your route parameter name
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor account not found'
            ], 403);
        }

        $product = Product::find($productId);
        
        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        if ($product->vendor_id !== $vendor->id) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to modify this product'
            ], 403);
        }

        // Add vendor to request for later use
        $request->merge(['current_vendor' => $vendor]);
        
        return $next($request);
    }
}
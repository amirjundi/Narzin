<?php

namespace Modules\Wishlist\Http\Controllers\V1\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Wishlist\Models\Wishlist;

class WishlistController extends Controller
{
    public function index()
    {
        try {
            $wishlistItems = Wishlist::with(['product', 'product.images'])
                ->where('user_id', Auth::id())
                ->paginate(20);

            return response()->json([
                'status' => true,
                'data' => $wishlistItems
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $existingItem = Wishlist::where('user_id', Auth::id())
                ->where('product_id', $request->product_id)
                ->first();

            if ($existingItem) {
                return response()->json([
                    'status' => false,
                    'message' => 'This product is already in your wishlist'
                ], 400);
            }

            $wishlistItem = Wishlist::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Product added to wishlist successfully',
                'data' => $wishlistItem
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $wishlistItem = Wishlist::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$wishlistItem) {
                return response()->json([
                    'status' => false,
                    'message' => 'Wishlist item not found'
                ], 404);
            }

            $wishlistItem->delete();

            return response()->json([
                'status' => true,
                'message' => 'Product removed from wishlist successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function clearWishlist()
    {
        try {
            Wishlist::where('user_id', Auth::id())->delete();

            return response()->json([
                'status' => true,
                'message' => 'Wishlist cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
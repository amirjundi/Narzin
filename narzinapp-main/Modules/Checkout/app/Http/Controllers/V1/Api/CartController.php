<?php

namespace Modules\Checkout\Http\Controllers\V1\Api;

use App\Http\Controllers\Controller;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Admin\Models\PriceExchange;
use Modules\Admin\Models\Status;
use Modules\Checkout\Models\Cart;
use Modules\ProductManagement\Models\ProductVariant;

class CartController extends Controller
{
    public function index()
    {
        try {
            // Get the latest exchange rate and global markup
            $latestExchange = PriceExchange::latest('created_at')->first();
            $exchangeRate = $latestExchange->price_rate ?? 1;
            $globalMarkup = \Modules\Admin\Models\PlatformMarkup::getLatest();

            $cartItems = Cart::with([
                'product',
                'product.vendor',
                'productVariant',
                'product.images',
                'productVariant.variantValues',
                'productVariant.variantValues.variantAttribute'
            ])
                ->where('user_id', Auth::id())
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'no cart items'
                ]);
            }

            $cartItems->each(function ($item) use ($exchangeRate, $globalMarkup) {
                // Determine effective markup: vendor override or global
                $vendor = $item->product->vendor ?? null;
                $markup = ($vendor && $vendor->markup_percentage !== null)
                    ? (float) $vendor->markup_percentage
                    : (float) $globalMarkup;

                // Apply markup then convert currency
                $basePrice = $item->productVariant->price;
                $markedUpPrice = $basePrice * (1 + $markup / 100);
                $convertedPrice = round($markedUpPrice / $exchangeRate, 2);

                // Total price for the quantity
                $item->price = $convertedPrice * $item->quantity;

                // Mark as out of stock if needed
                $item->out_of_stock = $item->productVariant->stock < $item->quantity;
            });

            return response()->json([
                'status' => true,
                'data' => $cartItems
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
                'product_id' => 'required|exists:products,id',
                'product_variant_id' => 'required|exists:product_variants,id',
                'quantity' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $existingCartItem = Cart::where('user_id', Auth::id())
                ->where('product_id', $request->product_id)
                ->where('product_variant_id', $request->product_variant_id)
                ->first();

            if ($existingCartItem) {
                return response()->json([
                    'status' => false,
                    'message' => 'This item is already in your cart'
                ], 400);
            }

            $productVariant = ProductVariant::find($request->product_variant_id);

            if ($request->quantity > $productVariant->stock) {
                return response()->json([
                    'status' => false,
                    'message' => 'Requested quantity exceeds available stock'
                ], 400);
            }

            $cartItem = Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'product_variant_id' => $request->product_variant_id,
                'quantity' => $request->quantity
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Item added to cart successfully',
                'data' => $cartItem
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cartItem = Cart::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$cartItem) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cart item not found'
                ], 404);
            }

            $productVariant = ProductVariant::find($cartItem->product_variant_id);

            if ($request->quantity > $productVariant->stock) {
                return response()->json([
                    'status' => false,
                    'message' => 'Requested quantity exceeds available stock'
                ], 400);
            }

            $cartItem->quantity = $request->quantity;
            $cartItem->save();

            return response()->json([
                'status' => true,
                'message' => 'Cart updated successfully',
                'data' => $cartItem->fresh()
            ]);
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
            $cartItem = Cart::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$cartItem) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cart item not found'
                ], 404);
            }

            $cartItem->delete();

            return response()->json([
                'status' => true,
                'message' => 'Item removed from cart successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function clearCart()
    {
        try {
            $cartItems = Cart::where('user_id', Auth::id())->delete();

            return response()->json([
                'status' => true,
                'message' => 'Cart cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

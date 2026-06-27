<?php

namespace Modules\Reviews\Http\Controllers\V1\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Reviews\Models\Review;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Review::with(['user']);

            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            $reviews = $query->get();

            return response()->json([
                'status' => true,
                'data' => $reviews
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function myReviews()
    {
        try {
            $reviews = Review::with(['product'])
                ->where('user_id', Auth::id())
                ->paginate(10);

            return response()->json([
                'status' => true,
                'data' => $reviews
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
                'review' => 'required|string',
                'rating' => 'required|integer|min:1|max:5',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Only customers who actually paid for this product may review it.
            $hasPurchased = \Modules\Checkout\Models\OrderItem::where('product_id', $request->product_id)
                ->whereHas('order', function ($q) {
                    $q->where('user_id', Auth::id())
                      ->whereIn('payment_status', ['processing', 'completed']);
                })
                ->exists();

            if (!$hasPurchased) {
                return response()->json([
                    'status' => false,
                    'message' => 'You can only review products you have purchased.'
                ], 403);
            }

            // One review per product per customer.
            $alreadyReviewed = Review::where('product_id', $request->product_id)
                ->where('user_id', Auth::id())
                ->exists();

            if ($alreadyReviewed) {
                return response()->json([
                    'status' => false,
                    'message' => 'You have already reviewed this product.'
                ], 409);
            }

            $review = Review::create([
                'product_id' => $request->product_id,
                'user_id' => Auth::id(),
                'review' => $request->review,
                'rating' => $request->rating,
                'is_approved' => false
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Review submitted successfully',
                'data' => $review
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
                'review' => 'sometimes|required|string',
                'rating' => 'sometimes|required|integer|min:1|max:5',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $review = Review::find($id);
            if (!$review || $review->user_id !== Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized or review not found'
                ], 403);
            }

            $review->review = $request->review;
            $review->rating = $request->rating;
            $review->save();



            return response()->json([
                'status' => true,
                'message' => 'Review updated successfully',
                'data' => $review->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Update error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $review = Review::find($id);
            if (!$review || $review->user_id !== Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized or review not found'
                ], 403);
            }

            $review->delete();

            return response()->json([
                'status' => true,
                'message' => 'Review deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

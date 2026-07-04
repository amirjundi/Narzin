<?php

namespace Modules\Telemetry\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Telemetry\Models\UserProductView;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TelemetryController extends Controller
{
    /**
     * Record a product view or update the dwell time.
     * This endpoint should be called silently by the frontend React app.
     */
    public function trackView(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'session_id' => 'required|string|max:255',
            'dwell_time_seconds' => 'nullable|integer|min:0|max:3600', // max 1 hour per ping to prevent abuse
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid telemetry data', 'errors' => $validator->errors()], 422);
        }

        $userId = auth('sanctum')->id(); // Will be null for guests
        $productId = $request->input('product_id');
        $sessionId = $request->input('session_id');
        $dwellTime = $request->input('dwell_time_seconds', 0);

        try {
            DB::beginTransaction();

            // Find an existing view record for this user/session and product
            $query = UserProductView::where('product_id', $productId);
            
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->where('session_id', $sessionId)->whereNull('user_id');
            }

            $view = $query->first();

            if ($view) {
                // If they're viewing it again, update the updated_at timestamp
                // and increment the total dwell time.
                $view->dwell_time_seconds += $dwellTime;
                $view->touch(); // updates 'updated_at' to right now
                $view->save();
            } else {
                // First time viewing this product
                UserProductView::create([
                    'product_id' => $productId,
                    'user_id' => $userId,
                    'session_id' => $sessionId,
                    'dwell_time_seconds' => $dwellTime,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Telemetry tracked successfully'], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error tracking telemetry', 'error' => $e->getMessage()], 500);
        }
    }
}

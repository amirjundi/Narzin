<?php

namespace Modules\Telemetry\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Telemetry\Services\CaptureService;

class TrackingController extends Controller
{
    /** Thin client hook: cart add/remove/update. Always 200 (non-blocking). */
    public function cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string|max:255',
            'product_id' => 'required|integer',
            'variant_id' => 'nullable|integer',
            'action' => 'required|in:add,remove,update',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'ignored'], 200);
        }

        CaptureService::recordCartEvent(
            $request->input('session_id'),
            auth('sanctum')->id(),
            (int) $request->input('product_id'),
            $request->input('variant_id') !== null ? (int) $request->input('variant_id') : null,
            $request->input('action'),
            (int) $request->input('quantity'),
            $request->input('unit_price') !== null ? (float) $request->input('unit_price') : null,
        );

        return response()->json(['message' => 'ok'], 200);
    }

    /** Thin client hook: session bootstrap + UTM/referrer. Always 200. */
    public function session(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'ignored'], 200);
        }

        CaptureService::recordSession(
            $request->input('session_id'),
            auth('sanctum')->id(),
            $request->only(['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'referrer', 'landing_url']),
        );

        return response()->json(['message' => 'ok'], 200);
    }
}

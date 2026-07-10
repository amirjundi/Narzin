<?php

use Illuminate\Support\Facades\Route;
use Modules\Telemetry\Http\Controllers\TelemetryController;
use Modules\Telemetry\Http\Controllers\TrackingController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 */

// We want this endpoint to be accessible to both guests and authenticated users.
// Sanctum middleware is not required because we handle null user_id in the controller for guests.
Route::prefix('v1/telemetry')->group(function () {
    Route::post('/view', [TelemetryController::class, 'trackView']);
});

// Behavioral capture — guest-friendly, always 200 (non-blocking).
Route::prefix('v1/track')->group(function () {
    Route::post('/cart', [TrackingController::class, 'cart']);
    Route::post('/session', [TrackingController::class, 'session']);
});

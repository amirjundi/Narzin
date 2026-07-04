<?php

use Illuminate\Support\Facades\Route;
use Modules\Telemetry\Http\Controllers\TelemetryController;

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

<?php

use Illuminate\Support\Facades\Route;
use Modules\Reviews\Http\Controllers\ReviewsController;
use Modules\Reviews\Http\Controllers\V1\Api\ReviewController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

// Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
//     Route::apiResource('reviews', ReviewsController::class)->names('reviews');
// });



Route::prefix('v1')->group(function () {
    Route::post('products/get/reviews', [ReviewController::class, 'index']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('my-reviews', [ReviewController::class, 'myReviews']);
        
        Route::post('reviews', [ReviewController::class, 'store']);
        Route::put('reviews/{id}', [ReviewController::class, 'update']);
        Route::delete('reviews/{id}', [ReviewController::class, 'destroy']);
    });
});

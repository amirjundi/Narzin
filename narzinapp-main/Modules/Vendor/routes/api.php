<?php

use Illuminate\Support\Facades\Route;
use Modules\Vendor\Http\Controllers\V1\Api\VendorController;
use Modules\Vendor\Http\Controllers\V1\Api\VendorOrderController;

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
//     Route::apiResource('vendor', VendorController::class)->names('vendor');
// });


Route::prefix('v1')->group(function () {
    // Public routes (no auth required)
    Route::get('vendors', [VendorController::class, 'index']);
    Route::get('vendors/{id}', [VendorController::class, 'show']);
    
    // Protected routes (auth required)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('vendors', [VendorController::class, 'store']);
        Route::put('vendors/{id}', [VendorController::class, 'update'])->middleware('vendor.account');
        Route::delete('vendors/{id}', [VendorController::class, 'destroy'])->middleware('vendor.account');


        Route::prefix('vendor/orders')->group(function () {
            Route::get('/', [VendorOrderController::class, 'getOrders']);
            Route::get('/statistics', [VendorOrderController::class, 'getOrderStatistics']);
            Route::put('/{orderItemId}/status', [VendorOrderController::class, 'updateOrderStatus']);
        });



    });
});
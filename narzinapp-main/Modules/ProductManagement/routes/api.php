<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AttributeController;
use Modules\ProductManagement\Http\Controllers\V1\Api\CategoryController;
use Modules\ProductManagement\Http\Controllers\V1\Api\ProductController;

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
// Attribute 
// Public routes
Route::prefix('v1')->group(function () {
    Route::get('products/search', [ProductController::class, 'search']);
    Route::apiResource('products', ProductController::class)->only(['index', 'show']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('attributes', [AttributeController::class, 'index']);

    Route::get('vendors/{vendorId}/products', [ProductController::class, 'getProductsByVendorId']);
});


Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Route that only needs vendor account
    Route::get('colorTags', [AttributeController::class, 'getColorTags']);

    Route::post('products', [ProductController::class, 'store'])
        ->middleware('vendor.account');

        
        // Routes that need product ownership verification
        Route::middleware('vendor.product')->group(function () {
            Route::put('products/{id}', [ProductController::class, 'update']);
            Route::post('products/images/{id}', [ProductController::class, 'addProductImages']);
            Route::delete('products/images/{id}', [ProductController::class, 'deleteProductImages']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);
    });
});

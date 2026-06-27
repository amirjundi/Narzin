<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;
use Modules\Admin\Http\Controllers\CouponController;
use Modules\Admin\Http\Controllers\TagController;
use Modules\ProductManagement\Models\Product;

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

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Admin management must require an ACTIVE admin account, not just any
    // authenticated user. Without the admin.auth gate an ordinary customer
    // could call POST /api/v1/admin to create themselves an admin account,
    // or PUT/DELETE /api/v1/admin/{id} to take over or remove any account.
    Route::middleware('admin.auth')->group(function () {
        Route::apiResource('admin', AdminController::class)->names('admin');
    });

    Route::get('get-color-tags', [TagController::class, 'getColorTagsApi']);

    // User-facing wallet/coupon endpoints (already scoped to the authenticated user)
    Route::get('wallet'  , action: [CouponController::class , 'getUserWalletData']);
    Route::get('get-wallet-transactions' , [CouponController::class , 'getWalletTransactions']);
    Route::post('get-coupon' , [CouponController::class , 'getApi']);
});

Route::get('v1/products/{product}/variants', function (Product $product) {
    return response()->json($product->variants()->with(['variantValues.variantAttribute'])->get()->map(function($variant) {
        return [
            'id' => $variant->id,
            'price' => $variant->price,
            'variant_values' => $variant->variantValues->map(function($value) {
                return [
                    'value' => $value->value,
                    'variant_attribute' => [
                        'name_arabic' => $value->variantAttribute->name_arabic,
                        'name_german' => $value->variantAttribute->name_german,
                    ]
                ];
            })
        ];
    }));
});
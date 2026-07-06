<?php

use Illuminate\Support\Facades\Route;
use Modules\Checkout\Http\Controllers\V1\Api\CartController;
use Modules\Checkout\Http\Controllers\V1\Api\CheckoutController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    
    // Cart Routes
    Route::resource('cart', CartController::class)->names('cart');
    Route::get('clear/cart', [CartController::class, 'clearCart'])->name('cart.clear');

    // Checkout Routes
    Route::post('/place-order', [CheckoutController::class, 'placeOrder'])
        ->middleware('throttle:10,1'); // 10 requests per minute per user
    
    // Payment verification - called by frontend after redirect from Nass
    Route::post('/verify-payment', [CheckoutController::class, 'verifyPayment'])
        ->middleware('throttle:10,1'); // 10 requests per minute per user
    
    // Get user's pending unpaid orders
    Route::get('/pending-orders', [CheckoutController::class, 'getPendingOrders']);
    
    // Order history
    Route::get('/orders', [CheckoutController::class, 'getOrders']);
    Route::get('/orders/{id}', [CheckoutController::class, 'getOrder']);
    Route::get('/orders/{id}/invoice', [CheckoutController::class, 'getInvoice']);
    Route::get('/orders/{id}/audit', [CheckoutController::class, 'getOrderAudit']); // NEW
    Route::patch('/orders/{id}/change-status', [CheckoutController::class, 'updateOrderStatus']);
});

// Webhook - No auth required (called by Nass server)
Route::post('/nass/webhook', [CheckoutController::class, 'nassWebhook'])
    ->middleware('throttle:100,1'); // 100 requests per minute (for Nass server)
<?php

use App\Http\Controllers\V1\Api\Auth\LoginController;
use App\Http\Controllers\V1\Api\Auth\LogoutController;
use App\Http\Controllers\V1\Api\Auth\PasswordResetController;
use App\Http\Controllers\V1\Api\Auth\RegisterController;
use App\Http\Controllers\V1\Api\Auth\UpdateProfileController;
use App\Http\Controllers\V1\Api\Auth\VerificationController;
use App\Http\Controllers\V1\Api\Auth\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->group(function () {

    Route::middleware('api')->group(function () {
        // Rate-limited to mitigate brute-force / credential stuffing and
        // password-reset email spam (these endpoints are unauthenticated).
        Route::post('/register', RegisterController::class)->middleware('throttle:10,1');
        Route::post('/login', LoginController::class)->middleware('throttle:5,1');

        Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
            ->name('api.verification.verify');

        Route::post('/email/resend-verification/{id}', [VerificationController::class, 'resend'])
            ->middleware('throttle:5,1')
            ->name('verification.send');


        Route::get('/email/verify', [VerificationController::class, 'notice'])
            ->name('verification.notice');

        // Password reset routes
        Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])
            ->middleware('throttle:5,1')
            ->name('password.email');
        Route::post('/reset-password', [PasswordResetController::class, 'reset'])
            ->middleware('throttle:5,1')
            ->name('password.reset');

        Route::middleware(['auth:sanctum', 'verified'])->group(function () {

            // Authentication management routes (logout)
            Route::post('/logout', LogoutController::class);

            // Profile management
            Route::get('/profile', ProfileController::class);
            Route::post('/profile/update', UpdateProfileController::class );


            // Revoke single device
            Route::delete('/devices/{deviceId}', [ProfileController::class, 'revokeDevice']);

            // GDPR compliance
            Route::get('/account/export', [ProfileController::class, 'exportData']);
            Route::delete('/account', [ProfileController::class, 'deleteAccount']);
        });
    });
});

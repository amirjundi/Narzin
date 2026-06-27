<?php

use Illuminate\Support\Facades\Route;
use Modules\Banners\Http\Controllers\BannerController;
use Modules\Banners\Http\Controllers\BeforeNavController;

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



Route::prefix('v1')->group(function () {

    Route::get('/banners/mobile', [BannerController::class, 'indexMobile']);
    Route::get('/banners/web', [BannerController::class, 'indexWeb']);

    Route::get('/before-nav/current', [BeforeNavController::class, 'getCurrent']);

    
});


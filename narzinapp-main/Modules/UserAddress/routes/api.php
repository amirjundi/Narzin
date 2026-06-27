<?php

use Illuminate\Support\Facades\Route;
use Modules\UserAddress\Http\Controllers\UserAddressController as ControllersUserAddressController;
use Modules\UserAddress\Http\Controllers\V1\Api\CountryCityController;
use Modules\UserAddress\Http\Controllers\V1\Api\UserAddressController;

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
    Route::resource('address', UserAddressController::class)->names('user-address');
    Route::post('address/{id}/set-default', [UserAddressController::class , 'setDefault'])->name('address.set-default');
    Route::get('countries', [CountryCityController::class, 'countries'])->name('countries');
    Route::get('cities/{id}', [CountryCityController::class, 'cities'])->name('cities');
    Route::get('show-city/{id}', [CountryCityController::class, 'showCity'])->name('show-city');
    Route::get('show-country/{id}', [CountryCityController::class, 'showCountry'])->name('show-country');
    Route::get('get-delivery-zones', [UserAddressController::class, 'getDeliveryZones'])->name('get-delivery-zones');
});

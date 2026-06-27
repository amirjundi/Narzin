<?php

use Illuminate\Support\Facades\Route;
use Modules\VendorAccount\Http\Controllers\VendorAccountController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([], function () {
    Route::resource('vendoraccount', VendorAccountController::class)->names('vendoraccount');
});

Route::get('/vendor-dashboard',[VendorAccountController::class  , 'index'])->name('vendor.dashboard');

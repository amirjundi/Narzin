<?php

use Illuminate\Support\Facades\Route;
use Modules\HomeContent\Http\Controllers\HomeController;

Route::prefix('v1')->group(function () {
    Route::get('/home', [HomeController::class, 'index']);
});

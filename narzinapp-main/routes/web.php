<?php

use App\Http\Controllers\Main\LanguageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});


Route::get('language/{locale}', [LanguageController::class, 'switchLanguage'])->name('language.switch');


Route::get('/dashboard', function () {
    // Redirect to admin panel or show a proper dashboard
    return redirect('/orders');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth', 'admin.auth'])->group(function () {
    Route::post('push-notification', [NotificationController::class, 'sendPushNotification']);
});




require __DIR__.'/auth.php';

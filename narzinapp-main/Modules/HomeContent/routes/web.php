<?php

use Illuminate\Support\Facades\Route;
use Modules\HomeContent\Http\Controllers\HomeBlockAdminController;

Route::middleware(['admin.auth'])->group(function () {
    Route::post('home-blocks/reorder', [HomeBlockAdminController::class, 'reorder'])->name('home-blocks.reorder');
    Route::post('home-blocks/{home_block}/toggle', [HomeBlockAdminController::class, 'toggle'])->name('home-blocks.toggle');
    Route::get('home-blocks-search/products', [HomeBlockAdminController::class, 'searchProducts'])->name('home-blocks.search.products');
    Route::get('home-blocks-search/categories', [HomeBlockAdminController::class, 'searchCategories'])->name('home-blocks.search.categories');
    Route::post('home-blocks-rail-preview', [HomeBlockAdminController::class, 'railPreview'])->name('home-blocks.rail-preview');
    Route::resource('home-blocks', HomeBlockAdminController::class)->except(['show'])->names('home-blocks');
});

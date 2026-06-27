<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AddressController;
use Modules\Admin\Http\Controllers\AdminController;
use Modules\Admin\Http\Controllers\AttributeController;
use Modules\Admin\Http\Controllers\BannerController;
use Modules\Admin\Http\Controllers\BeforeNavController;
use Modules\Admin\Http\Controllers\CategoryController;
use Modules\Admin\Http\Controllers\CouponController;
use Modules\Admin\Http\Controllers\DeliveryPriceController;
use Modules\Admin\Http\Controllers\OrderController;
use Modules\Admin\Http\Controllers\PriceExchangeController;
use Modules\Admin\Http\Controllers\ProductController;
use Modules\Admin\Http\Controllers\ShipmentController;
use Modules\Admin\Http\Controllers\StatisticsController;
use Modules\Admin\Http\Controllers\SubCategoryController;
use Modules\Admin\Http\Controllers\UserController;
use Modules\Admin\Http\Controllers\VendorController;
use Modules\ProductManagement\Models\Product;
use Modules\UserAddress\Http\Controllers\V1\Api\UserAddressController;
use Modules\Admin\Http\Controllers\PlatformMarkupController;

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

// Route::group([], function () {
//     Route::resource('admin', AdminController::class)->names('admin');
// });



Route::middleware(['admin.auth'])->group(function () {
    Route::resource('users', UserController::class)->names('users');
    Route::resource('categories', CategoryController::class)->names('categories');
    Route::resource('sub-categories', SubCategoryController::class)->names('sub-categories');
    Route::resource('admins', AdminController::class)->names('admins');
    Route::resource('vendors', VendorController::class)->names('vendors');
    Route::post('vendors/{id}/change-status', [VendorController::class, 'vendorChangeStatues'])
        ->name('vendors.change-status');
    Route::get('vendors/waiting/action', [VendorController::class, 'indexNotActive'])->name('vendors.waiting-action');


    Route::resource('attributes', AttributeController::class)->names('attributes');
    Route::resource('banners', BannerController::class)->names('banners');
    Route::get('orders/finished/orders', [OrderController::class, 'indexFinishedOrders'])->name('orders.finished');
    Route::get('orders/remaining/orders', [OrderController::class, 'indexRemainingOrders'])->name('orders.remaining');
    Route::post('/admin/orders/set-shipped', [OrderController::class, 'setShipped'])->name('set.shipped');


    Route::resource('coupons', CouponController::class)->names('coupons');
    
    // Delivery Zones & Methods
    Route::resource('delivery-zones', \Modules\Admin\Http\Controllers\DeliveryZoneController::class)->names('delivery-zones');
    Route::post('delivery-zones/{deliveryZone}/methods', [\Modules\Admin\Http\Controllers\DeliveryMethodController::class, 'store'])->name('delivery-methods.store');
    Route::delete('delivery-zones/{deliveryZone}/methods/{deliveryMethod}', [\Modules\Admin\Http\Controllers\DeliveryMethodController::class, 'destroy'])->name('delivery-methods.destroy');
    
    Route::resource('before-nav', BeforeNavController::class)->names('before-nav');
    Route::post('/wallet/{user}/update-balance', [UserController::class, 'updateWallet'])->name('wallet.update-balance');


    Route::resource('address', AddressController::class)->names('address');
    Route::post('address/{id}', [AddressController::class, 'setDefault'])->name('address.set-default');



    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/pending', [OrderController::class, 'pendingOrders'])->name('orders.pending');
    Route::get('/orders/expired', [OrderController::class, 'expiredOrders'])->name('orders.expired');
    Route::get('/orders/confirmed', [OrderController::class, 'confirmedOrders'])->name('orders.confirmed');
    Route::get('/orders/shipped', [OrderController::class, 'shippedOrders'])->name('orders.shipped');

    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.update.status');
    Route::post('/orders/{id}/refund', [OrderController::class, 'refundToWallet'])->name('orders.refund');

    Route::get('/orders/{id}/print', [OrderController::class, 'printOrder'])->name('orders.print');

    // Bulk Actions
    Route::post('/orders/bulk/processing', [OrderController::class, 'bulkProcessing'])->name('orders.bulk.processing');
    Route::post('/orders/bulk/shipped', [OrderController::class, 'bulkShipped'])->name('orders.bulk.shipped');

    // Export
    Route::get('/orders-export/csv', [OrderController::class, 'exportCsv'])->name('orders.export.csv');
    Route::get('/orders-export/pdf', [OrderController::class, 'exportPdf'])->name('orders.export.pdf');

    // Shipments / Fulfillment
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/daily', [ShipmentController::class, 'dailySummary'])->name('shipments.daily');
    Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
    Route::get('/shipments/{id}', [ShipmentController::class, 'show'])->name('shipments.show');
    Route::post('/shipments/{id}/collect', [ShipmentController::class, 'collectItem'])->name('shipments.collect');
    Route::post('/shipments/{id}/collect-vendor', [ShipmentController::class, 'collectVendor'])->name('shipments.collect-vendor');
    Route::post('/shipments/{id}/unavailable', [ShipmentController::class, 'markUnavailable'])->name('shipments.unavailable');
    Route::patch('/shipments/{id}/status', [ShipmentController::class, 'updateStatus'])->name('shipments.update-status');
    Route::get('/shipments/{id}/print', [ShipmentController::class, 'printPickupList'])->name('shipments.print');




    Route::resource('products', ProductController::class)->names('products');

    Route::get('/products/{product}/variants', function (Product $product) {
        return $product->variants()->with(['variant_values' => function ($query) {
            $query->with('variant_attribute');
        }])->get();
    });


    Route::get('statistics/users', [StatisticsController::class, 'userStatistics'])->name('statistics.users');
    Route::get('statistics/vendors', [StatisticsController::class, 'vendorStatistics'])->name('statistics.vendors');
    Route::get('statistics/products', [StatisticsController::class, 'productStatistics'])->name('statistics.products');
    Route::get('statistics/orders', [StatisticsController::class, 'orderStatistics'])->name('statistics.orders');

    Route::get('order/{order}/print', [OrderController::class, 'printOrder'])->name('order.print');



    Route::prefix('price-exchange')->name('price-exchange.')->group(function () {
        Route::get('/', [PriceExchangeController::class, 'index'])->name('index'); // List all
        Route::get('/create', [PriceExchangeController::class, 'create'])->name('create'); // Show form
        Route::post('/', [PriceExchangeController::class, 'store'])->name('store'); // Store new
    });

    Route::prefix('platform-markup')->name('platform-markup.')->group(function () {
        Route::get('/', [PlatformMarkupController::class, 'index'])->name('index');
        Route::get('/create', [PlatformMarkupController::class, 'create'])->name('create');
        Route::post('/', [PlatformMarkupController::class, 'store'])->name('store');
    });

    Route::get('vendor-payouts', [\Modules\Admin\Http\Controllers\VendorPayoutController::class, 'index'])->name('vendor-payouts.index');
    Route::get('vendor-payouts/settings', [\Modules\Admin\Http\Controllers\VendorPayoutController::class, 'settings'])->name('vendor-payouts.settings');
    Route::post('vendor-payouts/settings', [\Modules\Admin\Http\Controllers\VendorPayoutController::class, 'saveSettings'])->name('vendor-payouts.settings.save');
    Route::get('vendor-payouts/{vendor}', [\Modules\Admin\Http\Controllers\VendorPayoutController::class, 'show'])->name('vendor-payouts.show');
    Route::post('vendor-payouts/{vendor}/payout', [\Modules\Admin\Http\Controllers\VendorPayoutController::class, 'payout'])->name('vendor-payouts.payout');
    Route::post('vendor-payouts/{vendor}/adjust', [\Modules\Admin\Http\Controllers\VendorPayoutController::class, 'adjust'])->name('vendor-payouts.adjust');
});

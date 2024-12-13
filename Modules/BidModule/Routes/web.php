<?php

use Illuminate\Support\Facades\Route;
use Modules\BidModule\Http\Controllers\Web\Admin\CustomRequestController;
use Modules\BidModule\Http\Controllers\Web\Provider\PostController;
use Modules\BidModule\Http\Controllers\Web\Admin\PostController as AdminPostController;
use Modules\BidModule\Http\Controllers\Web\Provider\CustomRequestController as ProviderCustomRequestController;

Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Web\Provider', 'middleware' => ['provider']], function () {

    Route::group(['prefix' => 'booking', 'as' => 'booking.'], function () {
        Route::group(['prefix' => 'post', 'as' => 'post.'], function () {
            Route::any('/', [PostController::class, 'index'])->name('list');
            Route::any('export', [PostController::class, 'export'])->name('export');
            Route::any('details/{id}', [PostController::class, 'details'])->name('details');
            Route::any('update-status/{id}', [PostController::class, 'updateStatus'])->name('update_status');
            Route::post('multi-ignore', [PostController::class, 'multiIgnore'])->name('multi-ignore');
            Route::any('withdraw/{id}', [PostController::class, 'withdraw'])->name('withdraw');

            Route::any('check-all', [PostController::class, 'check_all'])->name('check_all');
        });

        Route::group(['prefix' =>'custom-request', 'as' => 'custom-request.'],function () {
            Route::get('create',[ProviderCustomRequestController::class,'create'])->name('create.custom-request');
            Route::get('/',[ProviderCustomRequestController::class,'index'])->name('list');
            Route::put('reject/{id}',[ProviderCustomRequestController::class,'rejectCustomRequest'])->name('reject');

            Route::get('details/{id}',[ProviderCustomRequestController::class,'show'])->name('details');
            Route::get('get-service/{id}',[ProviderCustomRequestController::class,'getServiceBySubCategory'])->name('get-service.custom-request');
            Route::get('get-variation/{id}',[ProviderCustomRequestController::class,'getVariationByService'])->name('get-varation.custom-request');
            Route::any('add-cart/{custom_request_id}',[ProviderCustomRequestController::class,'store'])->name('store');
            // get js
            Route::get('/get-services/{machineId}', [ProviderCustomRequestController::class,'getServices'])->withoutMiddleware('csrf');
            Route::get('/get-variations/{serviceId}', [ProviderCustomRequestController::class,'getVariations'])->withoutMiddleware('csrf');
        });
    });
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin']], function () {

    Route::group(['prefix' => 'booking', 'as' => 'booking.'], function () {
        Route::group(['prefix' => 'post', 'as' => 'post.'], function () {
            Route::any('/', [AdminPostController::class, 'index'])->name('list');
            Route::any('export', [AdminPostController::class, 'export'])->name('export');
            Route::any('details/{id}', [AdminPostController::class, 'details'])->name('details');
            Route::post('delete/{id}', [AdminPostController::class, 'delete'])->name('delete');
            Route::post('multi-remove', [AdminPostController::class, 'multiDelete'])->name('multi-remove');
        });
        Route::group(['prefix' =>'custom-request', 'as' => 'custom-request.'],function () {
            Route::get('create',[CustomRequestController::class,'create'])->name('admin.create.custom-request');
            Route::get('/',[CustomRequestController::class,'index'])->name('list');
            Route::put('reject/{id}',[CustomRequestController::class,'rejectCustomRequest'])->name('reject');
            Route::get('details/{id}',[CustomRequestController::class,'show'])->name('details');
            Route::get('get-service/{id}',[CustomRequestController::class,'getServiceBySubCategory'])->name('admin.get-service.custom-request');
            Route::get('get-variation/{id}',[CustomRequestController::class,'getVariationByService'])->name('admin.get-varation.custom-request');
            Route::any('add-cart/{custom_request_id}',[CustomRequestController::class,'store'])->withoutMiddleware('admin')->name('store');
            // get js
            Route::get('/get-services/{machineId}', [CustomRequestController::class,'getServices'])->withoutMiddleware('csrf');
            Route::get('/get-variations/{serviceId}', [CustomRequestController::class,'getVariations'])->withoutMiddleware('csrf');
        });
    });
});

// Route::post('add-cart/{id}',[CustomRequestController::class,'store'])->name('admin.store.custom-request');

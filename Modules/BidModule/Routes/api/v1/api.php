<?php

use Illuminate\Support\Facades\Route;
use Modules\BidModule\Http\Controllers\APi\V1\Customer\CustomRequestController;
use Modules\BidModule\Http\Controllers\APi\V1\Provider\CustomRequestController as ProviderAPI; 
use Modules\BidModule\Http\Controllers\APi\V1\Customer\PostBidController;
use Modules\BidModule\Http\Controllers\APi\V1\Customer\PostController;
use Modules\BidModule\Http\Controllers\APi\V1\Provider\PostBidController as ProviderPostBidController;
use Modules\BidModule\Http\Controllers\APi\V1\Provider\PostController as ProviderPostController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'customer', 'namespace' => 'Api\V1\Customer', 'middleware' => ['auth:api', 'ensureBiddingIsActive']], function () {
    Route::group(['prefix' => 'post'], function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/details/{id}', [PostController::class, 'show']);
        Route::post('/', [PostController::class, 'store']);

        Route::put('update-info', [PostController::class, 'updateInfo']);

        Route::group(['prefix' => 'bid'], function () {
            Route::get('/', [PostBidController::class, 'index']);
            Route::get('details', [PostBidController::class, 'show']);
            Route::put('update-status', [PostBidController::class, 'update']);
        });
    });

    //Custom Request
    Route::prefix('custom-request')->group(function () {
        Route::any('/',[CustomRequestController::class,'index']);
        Route::post('/store', [CustomRequestController::class, 'store'])->middleware('hitLimiter');
        Route::get('/list',[CustomRequestController::class,'list']);
        Route::get('/show/{id}',[CustomRequestController::class,'show']);
        Route::delete('delete/{id}', [CustomRequestController::class,'destroy']);
    });
});

Route::group(['prefix' => 'provider', 'namespace' => 'Api\V1\Provider', 'middleware' => ['auth:api', 'ensureBiddingIsActive']], function () {
    Route::group(['prefix' => 'post'], function () {
        Route::get('/', [ProviderPostController::class, 'index']);
        Route::get('details/{id}', [ProviderPostController::class, 'show']);
        Route::post('/', [ProviderPostController::class, 'decline']);
        Route::prefix('custom-request')->group(function () {
            Route::get('/',[ProviderPostController::class,'list']);            
            Route::get('/show/{id}',[ProviderPostController::class,'show']);            
            Route::get('/variations/{serviceId}',[ProviderPostController::class,'getVariations']);            
            Route::post('add-cart/{custom_request_id}',[ProviderPostController::class,'store']);
                        
        });
        Route::group(['prefix' => 'bid'], function () {
            Route::get('/', [ProviderPostBidController::class, 'index']);
            Route::post('/', [ProviderPostBidController::class, 'store']);
            Route::post('/withdraw', [ProviderPostBidController::class, 'withdraw']);
        });

        Route::group(['prefix' => 'custom-request','as' => 'custom-request.'],function(){
            Route::get('/',[ProviderAPI::class,'index'])->name('list');
            Route::get('details/{id}',[ProviderAPI::class,'show'])->name('details');
            Route::any('add-cart/{custom_request_id}',[ProviderAPI::class,'store'])->middleware('hitLimiter');
            Route::any('reject/{custom_request_id}',[ProviderAPI::class,'update'])->middleware('hitLimiter');
        });
    });
});

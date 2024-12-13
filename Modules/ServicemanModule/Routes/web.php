<?php

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

use Illuminate\Support\Facades\Route;
use Modules\ServicemanModule\Http\Controllers\Web\Admin\ServicemanController as AdminServicemanController;
use Modules\ServicemanModule\Http\Controllers\Web\Provider\ServicemanController;

Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Web\Provider', 'middleware' => ['provider']], function () {

    Route::group(['prefix' => 'serviceman', 'as' => 'serviceman.', 'middleware'=>'provider-admin'], function () {
        Route::any('/list', 'ServicemanController@index')->name('list');
        Route::get('create', 'ServicemanController@create')->name('create');
        Route::post('store', 'ServicemanController@store')->name('store');
        Route::get('show/{id}', [ServicemanController::class, 'show'])->name('show');
        Route::get('edit/{id}', 'ServicemanController@edit')->name('edit');
        Route::put('update/{id}', 'ServicemanController@update')->name('update');
        Route::any('status-update/{id}', 'ServicemanController@statusUpdate')->name('status-update');
        Route::delete('delete/{id}', 'ServicemanController@destroy')->name('delete');
        Route::any('download', 'ServicemanController@download')->name('download');
    });
});


Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin']], function () {

    Route::group(['prefix' => 'serviceman', 'as' => 'serviceman.', 'middleware'=>'admin'], function () {
        Route::any('/list', 'ServicemanController@index')->name('list');
        Route::get('create', 'ServicemanController@create')->name('create');
        Route::post('store', 'ServicemanController@store')->name('store');
        Route::get('show/{id}', [AdminServicemanController::class, 'show'])->name('show');
        Route::get('edit/{id}', 'ServicemanController@edit')->name('edit');
        Route::put('update/{id}', 'ServicemanController@update')->name('update');
        Route::any('status-update/{id}', 'ServicemanController@statusUpdate')->name('status-update');
        Route::delete('delete/{id}', 'ServicemanController@destroy')->name('delete');
        Route::any('download', 'ServicemanController@download')->name('download');
    });
});


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

Route::prefix('bidmodule/controller/api/v1/provider')->group(function() {
    Route::get('/', 'BidModule/Controller/APi/V1/ProviderController@index');
});

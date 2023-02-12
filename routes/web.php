<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/liqpay/result/{order}', [\App\Http\Controllers\OrderController::class, 'liqPayResult'])->name('orders.liqpay.result');

Route::prefix('admin')->group(function () {
    Auth::routes();
    Route::middleware('auth')->group(function () {
        Route::view('/home', 'home');
        Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index']);
        Route::get('/prices', [\App\Http\Controllers\Admin\PricesController::class, 'index'])->name('prices.index');
        Route::get('/prices/download/{filename}', [\App\Http\Controllers\Admin\PricesController::class, 'download'])->name('prices.download');
        Route::post('/prices/upload', [\App\Http\Controllers\Admin\PricesController::class, 'upload'])->name('prices.upload');
    });
});

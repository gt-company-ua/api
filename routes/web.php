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
Route::get('/liqpay/result/{order}/assist', [\App\Http\Controllers\OrderController::class, 'liqPayResultAssist'])->name('orders.liqpay.result.assist');

Route::prefix('admin')->group(function () {
    Auth::routes(['register' => false]);
    Route::middleware('auth')->group(function () {
        Route::view('/home', 'home');
        Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index']);
        Route::get('/osago', [\App\Http\Controllers\Admin\OsagoController::class, 'index']);
        Route::put('/osago/k1', [\App\Http\Controllers\Admin\OsagoController::class, 'updateK1'])->name('osago.k1');
        Route::put('/osago/k2', [\App\Http\Controllers\Admin\OsagoController::class, 'updateK2'])->name('osago.k2');
        Route::put('/osago/tariffs', [\App\Http\Controllers\Admin\OsagoController::class, 'updateTariffs'])->name('osago.tariffs');
        Route::get('/prices', [\App\Http\Controllers\Admin\PricesController::class, 'index'])->name('prices.index');
        Route::get('/prices/download/{filename}', [\App\Http\Controllers\Admin\PricesController::class, 'download'])->name('prices.download');
        Route::post('/prices/upload', [\App\Http\Controllers\Admin\PricesController::class, 'upload'])->name('prices.upload');

        Route::get('/assist-me', [\App\Http\Controllers\Admin\AssistMeController::class, 'index'])->name('assist.index');
        Route::put('/assist-me/tariffs', [\App\Http\Controllers\Admin\AssistMeController::class, 'update'])->name('assist.tariffs');
    });
});

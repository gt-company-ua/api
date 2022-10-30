<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::prefix('handbooks')->group(function () {
    Route::get('/cars', [\App\Http\Controllers\handbooks\CarController::class, 'marks']);
    Route::get('/cars/find', [\App\Http\Controllers\handbooks\CarController::class, 'findVehicle']);
    Route::get('/cars/{car_mark_id}/models', [\App\Http\Controllers\handbooks\CarController::class, 'models']);

    Route::get('/transport', [\App\Http\Controllers\handbooks\TransportController::class, 'categories']);
    Route::get('/transport/{transport_category_id}/powers', [\App\Http\Controllers\handbooks\TransportController::class, 'powers']);

    Route::get('/vzrRanges', [\App\Http\Controllers\handbooks\VzrRangeController::class, 'ranges']);
    Route::get('/vzrRanges/{vzr_range_id}/days', [\App\Http\Controllers\handbooks\VzrRangeController::class, 'days']);

    Route::get('/cities', [\App\Http\Controllers\handbooks\CityController::class, 'searchMtsbu']);
});

Route::prefix('kasko')->group(function () {
    Route::post('/', [\App\Http\Controllers\KaskoController::class, 'store']);
    Route::post('/calculate', [\App\Http\Controllers\KaskoController::class, 'calculate']);
});

Route::prefix('osago')->group(function () {
    Route::post('/', [\App\Http\Controllers\OsagoController::class, 'store']);
    Route::post('/calculate', [\App\Http\Controllers\OsagoController::class, 'calculate']);
    Route::get('/tariffs', [\App\Http\Controllers\OsagoController::class, 'tariffs']);
});

Route::prefix('greencard')->group(function () {
    Route::post('/', [\App\Http\Controllers\GreenCardController::class, 'store']);
    Route::post('/calculate', [\App\Http\Controllers\GreenCardController::class, 'calculate']);
});

Route::prefix('orders')->group(function () {
    Route::post('/liqypay/status', [\App\Http\Controllers\OrderController::class, 'liqPayStatus'])->name('orders.liqpay.status');
    Route::post('/liqypay/result', [\App\Http\Controllers\OrderController::class, 'liqPayResult'])->name('orders.liqpay.result');
});
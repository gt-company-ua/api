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
    Route::get('/cars', [\App\Http\Controllers\Handbooks\CarController::class, 'marks']);
    Route::get('/cars/find', [\App\Http\Controllers\Handbooks\CarController::class, 'findVehicle']);
    Route::get('/cars/{car_mark_id}/models', [\App\Http\Controllers\Handbooks\CarController::class, 'models']);

    Route::get('/transport', [\App\Http\Controllers\Handbooks\TransportController::class, 'categories']);
    Route::get('/transport/{transport_category_id}/powers', [\App\Http\Controllers\Handbooks\TransportController::class, 'powers']);

    Route::get('/vzrRanges', [\App\Http\Controllers\Handbooks\VzrRangeController::class, 'ranges']);
    Route::get('/vzrRanges/{vzr_range_id}/days', [\App\Http\Controllers\Handbooks\VzrRangeController::class, 'days']);

    Route::get('/cities', [\App\Http\Controllers\Handbooks\CityController::class, 'searchMtsbu']);
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

Route::prefix('vzr')->group(function () {
    Route::post('/', [\App\Http\Controllers\VzrController::class, 'store']);
    Route::post('/calculate', [\App\Http\Controllers\VzrController::class, 'calculate']);
});

Route::prefix('orders')->group(function () {
    Route::get('/{uuid}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    Route::post('/liqpay/status', [\App\Http\Controllers\OrderController::class, 'liqPayStatus'])->name('orders.liqpay.status');
    Route::post('/liqpay/result', [\App\Http\Controllers\OrderController::class, 'liqPayResult'])->name('orders.liqpay.result');
});

Route::prefix('data')->group(function () {
    Route::get('/inn', [\App\Http\Controllers\DataController::class, 'innInfo'])->name('data.inn');
});
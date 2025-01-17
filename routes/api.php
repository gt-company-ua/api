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
    Route::get('/cars/findIngo', [\App\Http\Controllers\Handbooks\CarController::class, 'findVehicleIngo']);
    Route::get('/cars/findBot', [\App\Http\Controllers\Handbooks\CarController::class, 'findVehicleOpendatabot']);
    Route::get('/cars/{car_mark_id}/models', [\App\Http\Controllers\Handbooks\CarController::class, 'models']);

    Route::get('/transport', [\App\Http\Controllers\Handbooks\TransportController::class, 'categories']);
    Route::get('/transport/{transport_category_id}/powers', [\App\Http\Controllers\Handbooks\TransportController::class, 'powers']);

    Route::get('/vzrRanges', [\App\Http\Controllers\Handbooks\VzrRangeController::class, 'ranges']);
    Route::get('/vzrRanges/{vzr_range_id}/days', [\App\Http\Controllers\Handbooks\VzrRangeController::class, 'days']);

    Route::get('/cities', [\App\Http\Controllers\Handbooks\CityController::class, 'searchMtsbu']);
    Route::get('/cities/local', [\App\Http\Controllers\Handbooks\CityController::class, 'searchLocal']);
    Route::get('/cities/osago', [\App\Http\Controllers\Handbooks\CityController::class, 'searchOsago']);
});

Route::prefix('kasko')->group(function () {
    Route::post('/', [\App\Http\Controllers\KaskoController::class, 'store']);
    Route::post('/calculate', [\App\Http\Controllers\KaskoController::class, 'calculate']);
});

Route::prefix('osago')->group(function () {
    Route::post('/', [\App\Http\Controllers\OsagoController::class, 'store']);
    Route::post('/calculate', [\App\Http\Controllers\OsagoController::class, 'calculate']);
    Route::get('/tariffs', [\App\Http\Controllers\OsagoController::class, 'tariffs']);

    Route::prefix('salamandra')->group(function () {
        Route::post('/', [\App\Http\Controllers\Osago\SalamandraController::class, 'store']);
        Route::post('/calculate', [\App\Http\Controllers\Osago\SalamandraController::class, 'calculate']);
        Route::post('/tariffs', [\App\Http\Controllers\Osago\SalamandraController::class, 'tariffs']);
    });

    Route::prefix('ingo')->group(function () {
        Route::post('/', [\App\Http\Controllers\Osago\IngoController::class, 'store']);
        Route::post('/draft', [\App\Http\Controllers\Osago\IngoController::class, 'draft']);
        Route::post('/calculate', [\App\Http\Controllers\Osago\IngoController::class, 'calculate']);
        Route::get('/discounts', [\App\Http\Controllers\Osago\IngoController::class, 'discountDocuments']);
    });
});

Route::prefix('greencard')->group(function () {
    Route::post('/', [\App\Http\Controllers\GreenCardController::class, 'store']);
    Route::post('/draft', [\App\Http\Controllers\GreenCardController::class, 'draft']);
    Route::post('/calculate', [\App\Http\Controllers\GreenCardController::class, 'calculate']);
});

Route::prefix('vzr')->group(function () {
    Route::post('/', [\App\Http\Controllers\VzrController::class, 'store']);
    Route::post('/calculate', [\App\Http\Controllers\VzrController::class, 'calculate']);
});

Route::prefix('orders')->group(function () {
    Route::get('/promocode', [\App\Http\Controllers\OrderController::class, 'promocode'])->name('orders.promocode');
    Route::post('/liqpay/status', [\App\Http\Controllers\OrderController::class, 'liqPayStatus'])->name('orders.liqpay.status');
    Route::post('/liqpay/status/{order}', [\App\Http\Controllers\OrderController::class, 'liqPayStatusUuid'])->name('orders.liqpay.status.uuid');
    Route::post('/liqpay/status/assist', [\App\Http\Controllers\OrderController::class, 'liqPayStatusAssist'])->name('orders.liqpay.status.assist');
    Route::get('/{uuid}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    Route::post('/{uuid}/sms/send', [\App\Http\Controllers\OrderController::class, 'sendSms'])->name('orders.sendSms');
    Route::post('/{uuid}/sms/confirm', [\App\Http\Controllers\OrderController::class, 'confirmSms'])->name('orders.confirmSms');
    Route::get('/{uuid}/{file_name}', [\App\Http\Controllers\OrderController::class, 'getFile'])->name('orders.files');
});

Route::prefix('data')->group(function () {
    Route::get('/inn', [\App\Http\Controllers\DataController::class, 'innInfo'])->name('data.inn');
    Route::get('/pdf/{order_id}', [\App\Http\Controllers\DataController::class, 'test'])->name('data.pdf');
    Route::post('/search-by-phone', [\App\Http\Controllers\DataController::class, 'searchUserByPhone']);
    Route::post('/search-by-hash', [\App\Http\Controllers\DataController::class, 'searchUserByHash']);
    Route::post('/send-sms', [\App\Http\Controllers\DataController::class, 'sendUserSms']);
});

Route::prefix('vignettes')->group(function () {
    Route::get('/products', [\App\Http\Controllers\VignetteController::class, 'products']);
    Route::post('/', [\App\Http\Controllers\VignetteController::class, 'save']);
    Route::post('/checkVehicles', [\App\Http\Controllers\VignetteController::class, 'checkVehicles']);
});

Route::prefix('v2')->group(function () {
    Route::prefix('vzr')->group(function () {
        Route::post('/', [\App\Http\Controllers\v2\VzrController::class, 'store']);
        Route::post('/draft', [\App\Http\Controllers\v2\VzrController::class, 'draft']);
        Route::post('/calculate', [\App\Http\Controllers\v2\VzrController::class, 'calculate']);
        Route::post('/calculate-with-cashback', [\App\Http\Controllers\v2\VzrController::class, 'calculateWithCashback']);
        Route::get('/territories', [\App\Http\Controllers\v2\VzrController::class, 'territories']);
        Route::get('/territories/{lang}', [\App\Http\Controllers\v2\VzrController::class, 'territories']);
        Route::get('/documents', [\App\Http\Controllers\v2\VzrController::class, 'documents']);
        Route::get('/goals', [\App\Http\Controllers\v2\VzrController::class, 'goals']);
        Route::get('/goals/{lang}', [\App\Http\Controllers\v2\VzrController::class, 'goals']);
    });
    Route::prefix('greencard')->group(function () {
        Route::post('/', [\App\Http\Controllers\v2\GreenCardController::class, 'store']);
        Route::post('/draft', [\App\Http\Controllers\v2\GreenCardController::class, 'draft']);
        Route::post('/calculate', [\App\Http\Controllers\v2\GreenCardController::class, 'calculate']);
    });
});

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
    Route::get('/cars/{car_mark_id}/models', [\App\Http\Controllers\handbooks\CarController::class, 'models']);

    Route::get('/transport', [\App\Http\Controllers\handbooks\TransportController::class, 'categories']);
    Route::get('/transport/{transport_category_id}/powers', [\App\Http\Controllers\handbooks\TransportController::class, 'powers']);
});

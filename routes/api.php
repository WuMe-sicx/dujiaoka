<?php

use App\Http\Controllers\Api\GoodsApiController;
use App\Http\Controllers\Api\OrderApiController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API routes
Route::prefix('v1')->group(function () {
    // 商品相关
    Route::get('/categories', [GoodsApiController::class, 'groups']);
    Route::get('/goods', [GoodsApiController::class, 'index']);
    Route::get('/goods/{id}', [GoodsApiController::class, 'show']);

    // 订单相关
    Route::post('/order/query-by-sn', [OrderApiController::class, 'queryBySn']);
    Route::post('/order/query-by-email', [OrderApiController::class, 'queryByEmail']);
    Route::get('/order/{orderSN}/status', [OrderApiController::class, 'checkStatus']);
});

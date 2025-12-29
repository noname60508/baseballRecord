<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('hello', function () {
    return response('B00 system API', 200);
});

Route::prefix('B10')->namespace('B10')->group(function () {
    Route::prefix('B11_gamesController')->group(function () {
        // 修改密碼
        Route::post('changePassword', 'B11_gamesController@changePassword');
    });
    Route::apiResource('B11_gamesController', 'B11_gamesController');
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('hello', function () {
    return response('B00 system API', 200);
});

Route::prefix('B10')->namespace('B10')->group(function () {
    // Route::prefix('B11_gamesController')->group(function () {
    //     // 修改密碼
    //     Route::post('changePassword', 'B11_gamesController@changePassword');
    // });
    Route::apiResource('B11_gamesController', 'B11_gamesController');
});

Route::prefix('B20')->namespace('B20')->group(function () {
    // 打擊結果
    Route::apiResource('B21_battingStatistics', 'B21_battingStatistics');

    // 逐打席結果
    Route::prefix('B21_battingResult')->group(function () {
        // 修改逐打席結果
        Route::put('update', 'B21_battingResult@update');
        // 刪除逐打席結果
        Route::delete('destroy', 'B21_battingResult@destroy');
    });
    Route::apiResource('B21_battingResult', 'B21_battingResult')->only(['store']);
});

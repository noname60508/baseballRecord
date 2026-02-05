<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('hello', function () {
    return response('B00 system API', 200);
});

Route::prefix('B10')->namespace('B10')->group(function () {
    Route::apiResource('B11_gamesController', 'B11_gamesController');
});

Route::prefix('B20')->namespace('B20')->group(function () {
    // 打擊結果統計
    Route::prefix('B21_battingStatistics')->group(function () {
        // 打擊數據統計
        Route::get('dataStatistics', 'B21_battingStatistics@dataStatistics');
    });
    // 打擊結果
    Route::apiResource('B21_battingStatistics', 'B21_battingStatistics');

    // 逐打席結果
    Route::prefix('B21_battingResult')->group(function () {
        // 新增/修改逐打席結果
        Route::POST('updateOrCreate', 'B21_battingResult@updateOrCreate');
        // 刪除逐打席結果
        Route::delete('destroy', 'B21_battingResult@destroy');
    });
});

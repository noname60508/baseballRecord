<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('hello', function () {
    return response('A00 system API', 200);
});

// 隊伍
Route::apiResource('Z00_teamsController', 'Z00_teamsController');
// 賽季
Route::apiResource('Z00_seasonsController', 'Z00_seasonsController');
// 場地
Route::apiResource('Z00_fieldsController', 'Z00_fieldsController');

// 結果記錄選項
Route::prefix('Z00_resultOptions')->group(function () {
    // 對決結果
    Route::get('Z00_matchupResultList', 'Z00_resultOptions@Z00_matchupResultList');
    // 擊球落點與守備位置
    Route::get('Z00_positionAndLocation/{Z00_matchupResultList_id}', 'Z00_resultOptions@Z00_positionAndLocation');
    // 擊球型態
    Route::get('Z00_ballInPlayType/{Z00_matchupResultList_id}', 'Z00_resultOptions@Z00_ballInPlayType');
});

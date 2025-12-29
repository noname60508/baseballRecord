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

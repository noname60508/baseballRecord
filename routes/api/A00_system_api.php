<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('hello', function () {
    return response('A00 system API', 200);
});

Route::prefix('A10')->namespace('A10')->group(function () {
    Route::prefix('A11_authController')->group(function () {
        // 新增使用者
        Route::get('register', 'A11_authController@register');
    });
    Route::apiResource('A11_authController', 'A11_authController');
});

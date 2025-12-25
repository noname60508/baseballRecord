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
        Route::post('register', 'A11_authController@register');
        // 登出
        Route::get('logout', 'A11_authController@logout');
        // 更新使用者頭像
        Route::post('iconUpdate', 'A11_authController@iconUpdate');
        // 修改密碼
        Route::post('changePassword', 'A11_authController@changePassword');
    });
    Route::apiResource('A11_authController', 'A11_authController');
});

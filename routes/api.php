<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('hello', function () {
    return response('API is working', 200);
});
Route::get('login', 'App\Http\Controllers\A00\A10\A11_authController@login')->withoutMiddleware('auth:sanctum');
// 帳號註冊
Route::post('register', 'App\Http\Controllers\A00\A10\A11_authController@register')->withoutMiddleware('auth:sanctum');
// 忘記密碼
Route::post('forgotPassword', 'App\Http\Controllers\A00\A10\A11_authController@forgotPassword')->withoutMiddleware('auth:sanctum');
// 忘記密碼重設密碼
Route::post('resetForgotPassword', 'App\Http\Controllers\A00\A10\A11_authController@resetForgotPassword')->withoutMiddleware('auth:sanctum');

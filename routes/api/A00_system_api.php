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
    // Route::get('/auth', [App\Http\Controllers\A00\A10\A11_authController::class, 'index']);
    // Route::post('/auth', [App\Http\Controllers\A00\A10\A11_authController::class, 'store']);
    Route::apiResource('A11_authController', 'A11_authController');
    // Add other routes as needed
});

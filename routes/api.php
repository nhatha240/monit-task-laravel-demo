<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return response()->json(['message' => 'Welcome to the API']);
});

Route::Post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::Post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});


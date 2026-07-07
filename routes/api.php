<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TasksControllers;
Route::get('/', function () {
    return response()->json(['message' => 'Welcome to the API']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/tasks', [TasksControllers::class, 'index']);
    Route::get('/tasks/{task}', [TasksControllers::class, 'show']);
    Route::post('/tasks', [TasksControllers::class, 'store']);
    Route::put('/tasks/{task}', [TasksControllers::class, 'update']);
    Route::delete('/tasks/{task}', [TasksControllers::class, 'destroy']);
});


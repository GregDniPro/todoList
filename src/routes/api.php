<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\v1\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:api')->group(function () {
    Route::post('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

Route::prefix('v1')->middleware('auth:api')->group(function () {
    Route::apiResource('tasks', TaskController::class);

    Route::prefix('tasks')->group(function () {
        Route::patch('/mark-done/{task}', [TaskController::class, 'done']);
    });
});

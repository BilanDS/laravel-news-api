<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Dashboard\NewsController as DashboardNewsController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PublicNewsController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/news', [PublicNewsController::class, 'index']);
Route::get('/news/{news}', [PublicNewsController::class, 'show']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profiles/me', [ProfileController::class, 'show']);
    Route::put('/profiles/me', [ProfileController::class, 'update']);

    Route::apiResource('dashboard/news', DashboardNewsController::class);
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PublicNewsController;
use App\Http\Controllers\Api\UserNewsController;
use App\Http\Controllers\Api\ProfileController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/news', [PublicNewsController::class, 'index']);
Route::get('/news/{news}', [PublicNewsController::class, 'show']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profiles/me', [ProfileController::class, 'show']);
    Route::put('/profiles/me', [ProfileController::class, 'update']);

    Route::apiResource('users/me/news', UserNewsController::class)->parameters([

        'news' => 'my_news'
    ]);
});
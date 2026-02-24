<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PublicNewsController;
use App\Http\Controllers\Api\UserNewsController;
use App\Http\Controllers\Api\ProfileController;

// Авторизація
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Публічні новини
Route::get('/news', [PublicNewsController::class, 'index']);
Route::get('/news/{news}', [PublicNewsController::class, 'show']);

Route::middleware(['auth:sanctum'])->group(function () {

    // Вихід з акаунту
    Route::post('/logout', [AuthController::class, 'logout']);

    // Перегляд та оновлення профілю
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // CRUD для власних новин
    Route::apiResource('my-news', UserNewsController::class)->parameters([
        'my-news' => 'my_news'
    ]);
});

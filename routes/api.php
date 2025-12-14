<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PlatformController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // User Profile
    Route::get('/user', [UserController::class, 'show'])->name('user.show');
    Route::put('/user', [UserController::class, 'update'])->name('user.update');

    // Platforms
    Route::get('/platforms', [PlatformController::class, 'index'])->name('platforms.index');
    Route::post('/platforms/{platform}/toggle', [PlatformController::class, 'toggle'])->name('platforms.toggle');

    // Posts
    Route::apiResource('posts', PostController::class);

    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/overview', [AnalyticsController::class, 'overview'])->name('overview');
        Route::get('/platforms', [AnalyticsController::class, 'platforms'])->name('platforms');
        Route::get('/timeline', [AnalyticsController::class, 'timeline'])->name('timeline');
    });
});

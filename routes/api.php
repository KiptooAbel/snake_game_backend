<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameScoreController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working',
        'timestamp' => now()->toISOString(),
    ]);
});

// Authentication routes (no middleware)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('profile', [AuthController::class, 'profile'])->middleware('auth:api');
});

// Protected routes (require JWT authentication)
Route::middleware('auth:api')->group(function () {
    // User routes
    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'profile']);
        Route::put('/', [UserController::class, 'updateProfile']);
        Route::get('stats', [UserController::class, 'stats']);
        Route::post('avatar', [UserController::class, 'uploadAvatar']);
    });
    
    // Score routes
    Route::prefix('scores')->group(function () {
        Route::get('/', [GameScoreController::class, 'index']);
        Route::post('/', [GameScoreController::class, 'store']);
        Route::get('best', [GameScoreController::class, 'best']);
        Route::get('{gameScore}', [GameScoreController::class, 'show']);
        Route::delete('{gameScore}', [GameScoreController::class, 'destroy']);
    });
});

// Public leaderboard routes (with rate limiting)
Route::prefix('leaderboard')->middleware('throttle:60,1')->group(function () {
    Route::get('global', [LeaderboardController::class, 'global']);
    Route::get('daily', [LeaderboardController::class, 'daily']);
    Route::get('weekly', [LeaderboardController::class, 'weekly']);
    Route::get('monthly', [LeaderboardController::class, 'monthly']);
    Route::get('difficulty/{difficulty}', [LeaderboardController::class, 'byDifficulty']);
});

// High scores (public with rate limiting)
Route::get('high-scores', [GameScoreController::class, 'highScores'])->middleware('throttle:60,1');

# .env configuration additions

# Add these to your .env file:

# Database 

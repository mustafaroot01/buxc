<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScannerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', \App\Http\Middleware\ForceJsonResponse::class])->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user()->load('roles');
        });

        Route::post('/logout', [AuthController::class, 'logout']);

        // Teacher API Group
        Route::prefix('teacher')->group(function () {
            // New Aggregate Init Path
            Route::get('/init', [\App\Http\Controllers\Api\Teacher\InitialDataController::class, '__invoke']);

            // Dashboard
            Route::get('/dashboard', [\App\Http\Controllers\Api\Teacher\DashboardController::class, 'index']);

            // Lectures Management
            Route::get('/lectures', [\App\Http\Controllers\Api\Teacher\LectureController::class, 'index']);
            Route::post('/lectures', [\App\Http\Controllers\Api\Teacher\LectureController::class, 'store']);
            Route::get('/lectures/{lecture}', [\App\Http\Controllers\Api\Teacher\LectureController::class, 'show']);
            Route::patch('/lectures/{lecture}', [\App\Http\Controllers\Api\Teacher\LectureController::class, 'update']);
            Route::delete('/lectures/{lecture}', [\App\Http\Controllers\Api\Teacher\LectureController::class, 'destroy']);
            Route::post('/lectures/{lecture}/toggle-attendance', [\App\Http\Controllers\Api\Teacher\LectureController::class, 'toggleAttendance']);

            // Warnings
            Route::get('/warnings', [\App\Http\Controllers\Api\Teacher\WarningController::class, 'index']);

            // Profile
            Route::get('/profile', [\App\Http\Controllers\Api\Teacher\ProfileController::class, 'show']);
            Route::post('/profile', [\App\Http\Controllers\Api\Teacher\ProfileController::class, 'update']);
        });

        // Rate limiting: 60 requests per minute configuration applied to Scanner
        Route::middleware(['throttle:60,1'])->group(function () {
            Route::post('/scan', [ScannerController::class, 'scan'])->name('api.scan');
        });
    });
});

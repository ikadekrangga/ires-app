<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InternalApiController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Internal API Routes
|--------------------------------------------------------------------------
| Semua endpoint di sini:
| - prefix: /api/internal/*
| - middleware: internal (API key validation)
*/

Route::prefix('internal')
    ->middleware(['internal']) // hanya middleware ini
    ->group(function () {

        // =========================
        // HEALTH CHECK
        // =========================
        Route::get('/health', [InternalApiController::class, 'health']);

        // =========================
        // JOB MANAGEMENT
        // =========================
        Route::get('/jobs/pending', [InternalApiController::class, 'getPendingJob']);
        Route::post('/jobs/{id}/success', [InternalApiController::class, 'markSuccess']);
        Route::post('/jobs/{id}/failed', [InternalApiController::class, 'markFailed']);
        Route::post('/jobs/recover-stuck', [InternalApiController::class, 'recoverStuck']);
        Route::get('/jobs/stats', [InternalApiController::class, 'jobStats']);

        // =========================
        // ACCOUNT
        // =========================
        Route::get('/accounts/{id}', [InternalApiController::class, 'getAccount']);

        // =========================
        // INSIGHTS
        // =========================
        Route::post('/insights', [InternalApiController::class, 'storeInsight']);

        Route::post('/accounts/{id}/refresh-token', [InternalApiController::class, 'refreshToken']);
        Route::post('/accounts/{id}/status', [InternalApiController::class, 'updateAccountStatus']);
    });

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTE (FRONTEND USAGE)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/jobs', [JobController::class, 'listJobs']);
    Route::post('/jobs', [JobController::class, 'createJob']);
    Route::get('/jobs/{id}', [JobController::class, 'show']);
    Route::get('/jobs/stats', [JobController::class, 'stats']);

    Route::get('/dashboard', [InsightController::class, 'dashboard']);
    Route::post('/insights/manual', [InsightController::class, 'storeManual']);

    Route::get('/accounts', [AccountController::class, 'index']);
    Route::post('/accounts', [AccountController::class, 'store']);
    Route::put('/accounts/{id}', [AccountController::class, 'update']);
    Route::delete('/accounts/{id}', [AccountController::class, 'destroy']);
});
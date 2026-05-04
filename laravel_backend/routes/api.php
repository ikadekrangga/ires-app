<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InternalApiController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\InsightController;
use PHPUnit\Util\PHP\Job;

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
    });

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTE (FRONTEND USAGE)
|--------------------------------------------------------------------------
*/

Route::get('/jobs', [JobController::class, 'listJobs']);
Route::post('/jobs', [JobController::class, 'createJob']);
Route::get('/jobs/{id}', [JobController::class, 'show']);
Route::get('/jobs/stats', [JobController::class, 'stats']);

Route::get('/dashboard', [InsightController::class, 'dashboard']);
Route::post('/insights/manual', [InsightController::class, 'storeManual']);

Route::get('/accounts', [AccountController::class, 'index']);
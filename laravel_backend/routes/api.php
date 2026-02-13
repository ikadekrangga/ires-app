<?php

use App\Http\Controllers\Api\InternalApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/account/update', [InternalApiController::class, 'updateAccount']);
Route::post('/insights/store', [InternalApiController::class, 'storeInsights']);

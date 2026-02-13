<?php

use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get("/accounts", [AccountController::class, 'index']);

Route::get("/sync-ig", [AccountController::class, 'syncData']);
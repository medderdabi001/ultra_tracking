<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminAccountController;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
*/

// Public Admin Auth Route
Route::post('/admin/login', [AdminAuthController::class, 'login']);

// Protected Admin Routes (Sanctum)
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::post('/accounts', [AdminAccountController::class, 'store']);
});
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminAccountController;
use App\Http\Controllers\Api\Client\AuthController;
use App\Http\Controllers\Api\Client\LiveTrackingController;
use App\Http\Controllers\Api\Client\CommandController;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
*/

// Public Admin Auth Route
Route::post('/admin/login', [AdminAuthController::class, 'login']);

// Public Client Auth
Route::post('/client/login', [AuthController::class, 'login']);

// Protected Admin Routes (Sanctum)
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

    Route::post('/accounts', [AdminAccountController::class, 'store']);

    // Accounts
    Route::post('/accounts', [AdminAccountController::class, 'store']);

    // Devices
    Route::post('/devices', [AdminDeviceController::class, 'store']);

    // Cars & Device Assignment
    Route::post('/cars', [AdminCarController::class, 'store']);
    Route::post('/cars/{id}/reassign-device', [AdminCarController::class, 'reassignDevice']);

    Route::get('/profile', [AuthController::class, 'profile']);

    // Live Telemetry
    Route::get('/vehicles/live', [LiveTrackingController::class, 'getLiveVehicles']);

    // Commands
    Route::post('/engine-command', [CommandController::class, 'sendEngineCommand']);
});
<?php

use App\Http\Controllers\Api\Admin\AdminAccountController;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminCarController;
use App\Http\Controllers\Api\Admin\AdminDeviceController;
use App\Http\Controllers\Api\Client\AuthController;
use App\Http\Controllers\Api\Client\CommandController;
use App\Http\Controllers\Api\Client\LiveTrackingController;
use Illuminate\Support\Facades\Route;

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
Route::middleware(['auth:sanctum', 'ensure.admin'])
    ->prefix('admin')
    ->group(function () {
        Route::post('/accounts', [AdminAccountController::class, 'store']);
        Route::post('/devices', [AdminDeviceController::class, 'store']);
        Route::post('/cars', [AdminCarController::class, 'store']);
        Route::post('/cars/{id}/reassign-device', [AdminCarController::class, 'reassignDevice']);

        Route::get('/profile', [AuthController::class, 'profile']);
    });

// Client routes are authenticated by Sanctum and authorize access in their controllers.
Route::middleware('auth:sanctum')
    ->prefix('client')
    ->group(function () {
        Route::get('/vehicles/live', [LiveTrackingController::class, 'getLiveVehicles']);
        Route::post('/engine-command', [CommandController::class, 'sendEngineCommand']);
    });

<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
        Route::get('/me', [\App\Http\Controllers\Api\V1\AuthController::class, 'me']);
        Route::put('/password/force-update', [\App\Http\Controllers\Api\V1\AuthController::class, 'forceUpdatePassword']);
        
        Route::put('/profile/update', [\App\Http\Controllers\Api\V1\AuthController::class, 'updateProfile']);
        Route::post('/profile/avatar', [\App\Http\Controllers\Api\V1\AuthController::class, 'uploadAvatar']);

        // Data Master
        Route::get('/areas', [\App\Http\Controllers\Api\V1\AreaController::class, 'index']);
        Route::get('/customers', [\App\Http\Controllers\Api\V1\CustomerController::class, 'index']);
        Route::get('/customers/{customer}', [\App\Http\Controllers\Api\V1\CustomerController::class, 'show']);

        // Transaksi (Catat Meter & Tagihan)
        Route::get('/meter-periods/{meterPeriod}/readings', [\App\Http\Controllers\Api\V1\MeterReadingController::class, 'index']);
        Route::post('/meter-periods/{meterPeriod}/readings/{meterReading}', [\App\Http\Controllers\Api\V1\MeterReadingController::class, 'update']);
        
        Route::get('/customers/{customer}/bills', [\App\Http\Controllers\Api\V1\BillingController::class, 'customerBills']);
        Route::post('/bills/{bill}/pay', [\App\Http\Controllers\Api\V1\BillingController::class, 'pay']);
    });
});

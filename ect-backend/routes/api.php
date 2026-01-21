<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\PasswordOtpController;

Route::prefix('v1')->group(function () {

    // Public
    Route::post('/auth/register', RegisterController::class);
    Route::post('/auth/login', LoginController::class);

    // Password reset OTP
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/auth/password/otp', [PasswordOtpController::class, 'sendOtp']);
        Route::post('/auth/password/verify', [PasswordOtpController::class, 'verifyOtp']);
        Route::post('/auth/password/reset', [PasswordOtpController::class, 'resetPassword']);
    });

    // Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [LoginController::class, 'me']);
        Route::post('/auth/logout', [LogoutController::class, 'logout']);
        Route::post('/auth/logout-all', [LogoutController::class, 'logoutAll']);
    });
});

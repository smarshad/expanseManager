<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| Guest (Authentication) Routes
|--------------------------------------------------------------------------
|
| These routes handle all guest-accessible authentication pages:
| login, registration, OTP verification, and password reset.
| Grouped under 'auth/' prefix and 'guest' middleware.
|
*/

Route::prefix('auth')->middleware('guest')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Login Routes
    |--------------------------------------------------------------------------
    */
    Route::get('login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('auth.login');

    Route::get('verify/login/otp', [LoginController::class, 'showOtpForm'])->name('login.otp.show');
    Route::post('verify/login/otp', [LoginController::class, 'verifyOtp'])->name('login.otp.verify');

    /*
    |--------------------------------------------------------------------------
    | Registration Routes
    |--------------------------------------------------------------------------
    */
    Route::get('register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('register', [RegisterController::class, 'register'])->name('register.store');

    Route::get('verify/register/otp', [RegisterController::class, 'showOtpForm'])->name('register.otp.show');
    Route::post('verify/register/otp', [RegisterController::class, 'verifyOtpAndCreateUser'])->name('register.otp.verify');

    /*
    |--------------------------------------------------------------------------
    | Password Reset (Forgot / Reset)
    |--------------------------------------------------------------------------
    |
    | Use two dedicated controllers for better separation of concerns:
    | - ForgotPasswordController → sends reset link email
    | - ResetPasswordController  → handles form & reset logic
    |
    */

    // Request reset link
    Route::get('password/forgot', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('password/forgot', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:6,1') // 6 requests per minute per IP/email
        ->name('password.forgot.password');

    // Reset password (via token)
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'update'])->name('password.reset');
});

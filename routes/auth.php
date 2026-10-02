<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])
                ->middleware('throttle:6,1');

    // Email Verification Routes (Link-based)
    Route::get('verify-email-sent', [RegisteredUserController::class, 'showEmailSent'])
                ->name('verify.email.sent');
    
    Route::get('verify-email/{token}', [RegisteredUserController::class, 'verifyEmail'])
                ->name('verify.email');
    
    Route::post('resend-verification', [RegisteredUserController::class, 'resendVerification'])
                ->name('resend.verification')
                ->middleware('throttle:3,1');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
                ->middleware('throttle:10,1');

    Route::get('forgot-password', function () {
        return Inertia::render('Auth/ForgotPassword', [
            'title' => 'Forgot your password?',
            'subtitle' => 'Enter your email address and we will send you a link to reset it.',
        ]);
    })->name('password.request');
    
    Route::post('forgot-password', [PasswordResetController::class, 'sendOtp'])
                ->name('password.email')
                ->middleware('throttle:5,1');
    
    Route::get('password-link-sent', [PasswordResetController::class, 'showLinkSent'])
                ->name('password.link.sent');
    
    Route::get('reset-password/{token}', [PasswordResetController::class, 'verifyToken'])
                ->name('password.verify.token');
    
    Route::get('reset-password', [PasswordResetController::class, 'showResetForm'])
                ->name('password.reset');
    
    Route::post('reset-password', [PasswordResetController::class, 'reset'])
                ->name('password.update')
                ->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});

<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;

// Rutas de invitado (no logueado)
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->name('register.store');

    Route::get('registro/pendiente', [RegisteredUserController::class, 'pending'])
        ->name('register.pending');

    Route::post('registro/verificar/reenviar', [RegisteredUserController::class, 'resendVerification'])
        ->middleware('throttle:6,1')
        ->name('registration.verification.resend');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [FortifyAuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'email'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'update'])
        ->name('password.update');
});

// La firma es la única autorización necesaria: el usuario puede abrir el enlace
// desde otra sesión o sin estar autenticado.
Route::get('registro/verificar/{user}', [RegisteredUserController::class, 'verifyRegistration'])
    ->middleware('signed')
    ->whereNumber('user')
    ->name('registration.verify-email');

// Rutas de usuario autenticado
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

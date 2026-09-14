<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

// Frontend
require __DIR__.'/frontend/home.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/personas.php';

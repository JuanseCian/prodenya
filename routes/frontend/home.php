<?php

use App\Http\Controllers\frontend\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])
    ->middleware('auth')
    ->name('home');

Route::get('/mi-panel', [HomeController::class, 'panel'])
    ->middleware('auth')
    ->name('personal-panel');

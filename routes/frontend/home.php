<?php

use App\Http\Controllers\frontend\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])
    ->middleware('auth')
    ->name('home');

Route::get('/mi-panel', [HomeController::class, 'panel'])
    ->middleware('auth')
    ->name('personal-panel');

Route::get('/buscar-personas', [HomeController::class, 'buscarPersonas'])
    ->middleware(['auth', 'verified', 'rol:admin,operador,consulta,oficios,coordinador,profesional,administrativo'])
    ->name('home.buscar-personas');

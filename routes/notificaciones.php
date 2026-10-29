<?php

use App\Http\Controllers\NotificacionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'rol:admin,operador,consulta,oficios,coordinador,profesional,administrativo'])
    ->prefix('notificaciones')
    ->name('notificaciones.')
    ->controller(NotificacionController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::patch('/{notification}/leer', 'marcarLeida')->name('leer');
        Route::patch('/leer-todas', 'marcarTodasLeidas')->name('leer-todas');
    });

<?php

use App\Http\Controllers\AgendaEventoController;
use App\Http\Controllers\TareaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('rol:admin,operador,oficios,coordinador,profesional,administrativo')
        ->controller(TareaController::class)
        ->prefix('tareas')
        ->name('tareas.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::patch('/{tarea}', 'update')->whereNumber('tarea')->name('update');
        });

    Route::middleware('rol:admin,coordinador,profesional,administrativo')
        ->controller(AgendaEventoController::class)
        ->prefix('agenda')
        ->name('agenda.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{agendaEvento}', 'update')->whereNumber('agendaEvento')->name('update');
            Route::delete('/{agendaEvento}', 'destroy')->whereNumber('agendaEvento')->name('destroy');
        });
});

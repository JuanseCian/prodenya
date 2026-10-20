<?php

use App\Http\Controllers\ComunicacionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'rol:admin,operador,consulta,oficios,coordinador,profesional,administrativo'])
    ->prefix('comunicacion')
    ->name('comunicacion.')
    ->controller(ComunicacionController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/no-leidos', 'noLeidos')->name('no-leidos');
        Route::get('/{conversacion}', 'show')->whereNumber('conversacion')->name('show');
        Route::post('/conversaciones', 'crearConversacion')->name('conversaciones.store');
        Route::post('/{conversacion}/mensajes', 'enviarMensaje')->whereNumber('conversacion')->name('mensajes.store');
        Route::patch('/{conversacion}/leida', 'marcarLeida')->whereNumber('conversacion')->name('leida');
        Route::get('/recursos/buscar', 'buscarRecursos')->name('recursos.buscar');
        Route::get('/recursos/{tipo}/{id}', 'abrirRecurso')->whereNumber('id')->name('recursos.abrir');
    });

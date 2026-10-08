<?php

use App\Http\Controllers\LegajoController;
use App\Http\Controllers\PersonaController;
use App\Models\Barrio;
use App\Models\Localidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('personas/nueva', [PersonaController::class, 'create'])->name('personas.create');
    Route::post('personas', [PersonaController::class, 'store'])->name('personas.store');

    Route::get('legajos/create', [LegajoController::class, 'create'])->name('legajos.create');
    Route::post('legajos', [LegajoController::class, 'store'])->name('legajos.store');
    Route::get('/personas', [PersonaController::class, 'index'])->name('personas.index');
    Route::get('/personas/{persona}', [PersonaController::class, 'show'])->name('personas.show')->whereNumber('persona');
    Route::get('/personas/{persona}/edit', [PersonaController::class, 'edit'])->name('personas.edit')->whereNumber('persona');
    Route::put('/personas/{persona}', [PersonaController::class, 'update'])->name('personas.update')->whereNumber('persona');
    Route::delete('/personas/{persona}', [PersonaController::class, 'destroy'])->name('personas.destroy')->whereNumber('persona');
    Route::get('/legajos', [LegajoController::class, 'index'])->name('legajos.index');
    Route::get('/legajos/{legajo}', [LegajoController::class, 'show'])->name('legajos.show')->whereNumber('legajo');
    Route::get('/legajos/{legajo}/edit', [LegajoController::class, 'edit'])->name('legajos.edit')->whereNumber('legajo');
    Route::put('/legajos/{legajo}', [LegajoController::class, 'update'])->name('legajos.update')->whereNumber('legajo');
    Route::patch('/legajos/{legajo}/cerrar', [LegajoController::class, 'cerrar'])->name('legajos.cerrar')->whereNumber('legajo');
    Route::patch('/legajos/{legajo}/reabrir', [LegajoController::class, 'reabrir'])->name('legajos.reabrir')->whereNumber('legajo');
    // Endpoints livianos para los selects en cascada del formulario de persona
    Route::get('api/localidades', function (Request $request) {
        return Localidad::where('provincia_id', $request->query('provincia_id'))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    })->name('api.localidades');

    Route::get('api/barrios', function (Request $request) {
        return Barrio::where('localidad_id', $request->query('localidad_id'))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    })->name('api.barrios');
});

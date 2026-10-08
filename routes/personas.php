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

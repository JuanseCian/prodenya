<?php

use App\Http\Controllers\LegajoController;
use App\Models\Barrio;
use App\Models\Localidad;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Legajos es la única experiencia visible. Estas rutas mantienen enlaces antiguos funcionando.
    Route::middleware('rol:admin,operador')->group(function () {
        Route::get('personas/nueva', fn () => redirect()->route('legajos.create'))->name('personas.create');
        Route::get('legajos/create', [LegajoController::class, 'create'])->name('legajos.create');
        Route::post('legajos', [LegajoController::class, 'store'])->name('legajos.store');
        Route::get('/legajos/{legajo}/edit', [LegajoController::class, 'edit'])->name('legajos.edit')->whereNumber('legajo');
        Route::put('/legajos/{legajo}', [LegajoController::class, 'update'])->name('legajos.update')->whereNumber('legajo');
        Route::patch('/legajos/{legajo}/cerrar', [LegajoController::class, 'cerrar'])->name('legajos.cerrar')->whereNumber('legajo');
        Route::patch('/legajos/{legajo}/reabrir', [LegajoController::class, 'reabrir'])->name('legajos.reabrir')->whereNumber('legajo');
    });

    // ── Lectura: admin, operador y consulta ─────────────────────────────
    Route::middleware('rol:admin,operador,consulta')->group(function () {
        Route::get('/personas', fn () => redirect()->route('legajos.index'))->name('personas.index');
        Route::get('/personas/{persona}', fn (Persona $persona) => redirect()->route('legajos.show', $persona->id))
            ->name('personas.show')->whereNumber('persona');
        Route::get('/legajos', [LegajoController::class, 'index'])->name('legajos.index');
        Route::get('/legajos/{legajo}', [LegajoController::class, 'show'])->name('legajos.show')->whereNumber('legajo');

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
});

<?php

use App\Http\Controllers\DerivacionCasoController;
use App\Http\Controllers\GrupoFamiliarController;
use App\Http\Controllers\IntervencionController;
use App\Http\Controllers\LegajoController;
use App\Models\Barrio;
use App\Models\Localidad;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Legajos es la única experiencia visible. Estas rutas mantienen enlaces antiguos funcionando.
    Route::middleware('rol:admin,operador,coordinador,administrativo')->group(function () {
        Route::get('personas/nueva', fn () => redirect()->route('legajos.create'))->name('personas.create');
        Route::get('legajos/create', [LegajoController::class, 'create'])->name('legajos.create');
        Route::post('legajos', [LegajoController::class, 'store'])->name('legajos.store');
        Route::get('/legajos/{legajo}/edit', [LegajoController::class, 'edit'])->name('legajos.edit')->whereNumber('legajo');
        Route::put('/legajos/{legajo}', [LegajoController::class, 'update'])->name('legajos.update')->whereNumber('legajo');
        Route::patch('/legajos/{legajo}/cerrar', [LegajoController::class, 'cerrar'])->name('legajos.cerrar')->whereNumber('legajo');
        Route::patch('/legajos/{legajo}/reabrir', [LegajoController::class, 'reabrir'])->name('legajos.reabrir')->whereNumber('legajo');
    });

    Route::middleware('rol:admin,operador,coordinador,profesional')->group(function () {
        Route::get('/legajos/{legajo}/intervenciones/create', [IntervencionController::class, 'create'])->name('legajos.intervenciones.create')->whereNumber('legajo');
        Route::post('/legajos/{legajo}/intervenciones', [IntervencionController::class, 'store'])->name('legajos.intervenciones.store')->whereNumber('legajo');
        Route::get('/legajos/{legajo}/intervenciones/{intervencion}/edit', [IntervencionController::class, 'edit'])->name('legajos.intervenciones.edit')->whereNumber(['legajo', 'intervencion']);
        Route::put('/legajos/{legajo}/intervenciones/{intervencion}', [IntervencionController::class, 'update'])->name('legajos.intervenciones.update')->whereNumber(['legajo', 'intervencion']);
    });

    Route::middleware('rol:admin,coordinador,profesional,administrativo')->group(function () {
        Route::post('/legajos/{legajo}/vinculos', [GrupoFamiliarController::class, 'store'])
            ->name('legajos.vinculos.store')->whereNumber('legajo');
        Route::delete('/legajos/{legajo}/vinculos/{vinculo}', [GrupoFamiliarController::class, 'destroy'])
            ->name('legajos.vinculos.destroy')->whereNumber(['legajo', 'vinculo']);
    });

    Route::middleware('rol:admin,operador,oficios,coordinador,profesional,administrativo')->group(function () {
        Route::post('/legajos/{legajo}/derivaciones', [DerivacionCasoController::class, 'store'])
            ->name('legajos.derivaciones.store')->whereNumber('legajo');
        Route::patch('/derivaciones/{derivacion}/aceptar', [DerivacionCasoController::class, 'aceptar'])
            ->name('derivaciones.aceptar')->whereNumber('derivacion');
        Route::patch('/derivaciones/{derivacion}/rechazar', [DerivacionCasoController::class, 'rechazar'])
            ->name('derivaciones.rechazar')->whereNumber('derivacion');
        Route::patch('/derivaciones/{derivacion}/cancelar', [DerivacionCasoController::class, 'cancelar'])
            ->name('derivaciones.cancelar')->whereNumber('derivacion');
    });

    // ── Lectura: admin, operador y consulta ─────────────────────────────
    Route::middleware('rol:admin,operador,consulta,oficios,coordinador,profesional,administrativo')->group(function () {
        Route::get('/personas', fn () => redirect()->route('legajos.index'))->name('personas.index');
        Route::get('/personas/{persona}', fn (Persona $persona) => redirect()->route('legajos.show', $persona->id))
            ->name('personas.show')->whereNumber('persona');
        Route::get('/legajos', [LegajoController::class, 'index'])->name('legajos.index');
        Route::get('/mayores-18', [LegajoController::class, 'mayores18'])->name('mayores18.index');
        Route::get('/legajos/{legajo}/estadisticas', [LegajoController::class, 'estadisticas'])
            ->name('legajos.estadisticas')->whereNumber('legajo');
        Route::get('/legajos/{legajo}', [LegajoController::class, 'show'])->name('legajos.show')->whereNumber('legajo');
        Route::get('/intervenciones/documentos/{documento}/descargar', [IntervencionController::class, 'descargarAdjunto'])
            ->name('intervenciones.documentos.descargar')->whereNumber('documento');
        Route::get('/legajos/{legajo}/intervenciones/{intervencion}/firma/{firma}/descargar', [IntervencionController::class, 'descargarFirma'])
            ->name('legajos.intervenciones.firma')->whereNumber(['legajo', 'intervencion']);

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

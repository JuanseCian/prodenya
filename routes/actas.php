<?php

use App\Http\Controllers\ActaController;
use App\Models\Acta;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'rol:'.implode(',', Acta::ROLES)])->group(function () {
    $tipos = array_keys(Acta::TIPOS);

    Route::get('actas', [ActaController::class, 'index'])->name('actas.index');
    Route::get('actas/sin-vincular', [ActaController::class, 'pendientes'])->name('actas.pendientes');
    Route::get('actas/personas', [ActaController::class, 'buscarPersonas'])->name('actas.personas');
    Route::get('actas/nueva/{tipo}', [ActaController::class, 'create'])->name('actas.create')->whereIn('tipo', $tipos);
    Route::post('actas/nueva/{tipo}', [ActaController::class, 'store'])->name('actas.store')->whereIn('tipo', $tipos);
    Route::post('actas/nueva/{tipo}/borrador', [ActaController::class, 'borrador'])->name('actas.borrador')->whereIn('tipo', $tipos);
    Route::get('actas/{acta}/descargar', [ActaController::class, 'descargar'])->name('actas.descargar')->whereNumber('acta');
    Route::patch('actas/{acta}/vincular', [ActaController::class, 'vincular'])->name('actas.vincular')->whereNumber('acta');
});

<?php

use App\Http\Controllers\AdminCatalogController;
use App\Http\Controllers\AdminHomeController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AvisoController;
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\ManualController;
use App\Http\Controllers\PuntajeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('rol:admin,operador,consulta,oficios,coordinador,profesional,administrativo')
        ->get('/manual/usuario/pdf', [ManualController::class, 'download'])
        ->name('manual.download');

    Route::controller(EstadisticasController::class)
        ->prefix('estadisticas')
        ->name('estadisticas')
        ->group(function () {
            Route::middleware('rol:admin,operador,consulta,oficios,coordinador,profesional,administrativo')->group(function () {
                Route::get('/', '__invoke')->name('');
                Route::get('/legajos', 'legajos')->name('.legajos');
                Route::get('/problematicas', 'problematicas')->name('.problematicas');
                Route::get('/intervenciones', 'intervenciones')->name('.intervenciones');
                Route::get('/exportar', 'exportar')->name('.exportar');
            });
            Route::middleware('rol:admin,operador,oficios,coordinador,profesional,administrativo')
                ->get('/tareas', 'tareas')->name('.tareas');
            Route::middleware('rol:admin,coordinador,profesional,administrativo')
                ->get('/agenda', 'agenda')->name('.agenda');
        });

    Route::middleware('rol:admin,coordinador')
        ->get('/estadisticas/ranking', [PuntajeController::class, 'index'])
        ->name('estadisticas.ranking');
});

Route::middleware(['auth', 'verified'])
    ->prefix('backend')
    ->name('admin.')
    ->group(function () {
        Route::middleware('rol:admin')->group(function () {
            Route::get('home', AdminHomeController::class)->name('home');
            Route::get('auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
            Route::put('usuarios/{user}/secciones-legajo', [AdminUserController::class, 'updateLegajoSecciones'])->whereNumber('user')->name('usuarios.secciones-legajo');
        });

        Route::middleware('rol:admin,coordinador')->group(function () {
            Route::get('usuarios', [AdminUserController::class, 'index'])->name('usuarios.index');
            Route::post('usuarios/{user}/aprobar', [AdminUserController::class, 'approve'])->whereNumber('user')->name('usuarios.aprobar');
            Route::post('usuarios/{user}/rechazar', [AdminUserController::class, 'reject'])->whereNumber('user')->name('usuarios.rechazar');
            Route::post('usuarios/{user}/reabrir', [AdminUserController::class, 'reopen'])->whereNumber('user')->name('usuarios.reabrir');
            Route::patch('usuarios/{user}/rol', [AdminUserController::class, 'updateRole'])->whereNumber('user')->name('usuarios.rol');
            Route::post('usuarios/{user}/suspender', [AdminUserController::class, 'suspend'])->whereNumber('user')->name('usuarios.suspender');
            Route::post('usuarios/{user}/reactivar', [AdminUserController::class, 'reactivate'])->whereNumber('user')->name('usuarios.reactivar');
            Route::post('usuarios/{user}/archivar', [AdminUserController::class, 'archive'])->whereNumber('user')->name('usuarios.archivar');
            Route::post('usuarios/{user}/restaurar', [AdminUserController::class, 'restore'])->whereNumber('user')->name('usuarios.restaurar');

            Route::controller(AvisoController::class)
                ->prefix('avisos')
                ->name('avisos.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('crear', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('{aviso}/editar', 'edit')->whereNumber('aviso')->name('edit');
                    Route::put('{aviso}', 'update')->whereNumber('aviso')->name('update');
                    Route::post('{aviso}/desactivar', 'desactivar')->whereNumber('aviso')->name('desactivar');
                    Route::post('{aviso}/reactivar', 'reactivar')->whereNumber('aviso')->name('reactivar');
                });
        });

    });

Route::middleware(['auth', 'verified', 'rol:admin'])
    ->prefix('backend/catalogos')
    ->name('admin.catalogos.')
    ->controller(AdminCatalogController::class)
    ->group(function () {
        Route::get('{catalog}', 'index')->name('index');
        Route::get('{catalog}/crear', 'create')->name('create');
        Route::post('{catalog}', 'store')->name('store');
        Route::get('{catalog}/{item}/editar', 'edit')->whereNumber('item')->name('edit');
        Route::put('{catalog}/{item}', 'update')->whereNumber('item')->name('update');
        Route::delete('{catalog}/{item}', 'destroy')->whereNumber('item')->name('destroy');
    });

// Frontend
require __DIR__.'/frontend/home.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/personas.php';
require __DIR__.'/mailbox.php';
require __DIR__.'/comunicacion.php';
require __DIR__.'/notificaciones.php';
require __DIR__.'/workflow.php';
require __DIR__.'/actas.php';
require __DIR__.'/legal.php';

if (app()->isLocal()) {
    Route::get('/_errores/{codigo}', fn (int $codigo) => abort($codigo))
        ->whereIn('codigo', ['403', '404', '419', '500', '503'])
        ->name('errores.preview');
}

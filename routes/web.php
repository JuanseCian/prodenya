<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminHomeController;
use App\Http\Controllers\AdminCatalogController;
use App\Http\Controllers\EstadisticasController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::controller(EstadisticasController::class)
        ->prefix('estadisticas')
        ->name('estadisticas')
        ->group(function () {
            Route::get('/', '__invoke')->name('');
            Route::get('/legajos', 'legajos')->name('.legajos');
            Route::get('/problematicas', 'problematicas')->name('.problematicas');
            Route::get('/intervenciones', 'intervenciones')->name('.intervenciones');
        });
});

Route::middleware(['auth', 'verified', 'rol:admin'])
    ->prefix('backend')
    ->name('admin.')
    ->group(function () {
        Route::get('home', AdminHomeController::class)->name('home');
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

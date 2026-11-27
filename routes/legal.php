<?php

use App\Http\Controllers\LegalController;
use Illuminate\Support\Facades\Route;

Route::get('terminos-y-condiciones', [LegalController::class, 'terminos'])
    ->name('legal.terminos');
Route::get('contacto', [LegalController::class, 'contacto'])
    ->name('legal.contacto');
Route::get('privacidad', [LegalController::class, 'privacidad'])
    ->name('legal.privacidad');
Route::get('accesibilidad', [LegalController::class, 'accesibilidad'])
    ->name('legal.accesibilidad');
Route::get('ubicacion', [LegalController::class, 'ubicacion'])
    ->name('legal.ubicacion');

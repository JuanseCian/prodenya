<?php

use App\Http\Controllers\LegalController;
use Illuminate\Support\Facades\Route;

Route::get('terminos-y-condiciones', [LegalController::class, 'terminos'])
    ->name('legal.terminos');

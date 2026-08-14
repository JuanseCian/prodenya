<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLegajoRequest;
use App\Models\Legajo;
use App\Models\Persona;
use Inertia\Inertia;
use Inertia\Response;

class LegajoController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('legajos/create', [
            'personas' => Persona::where('activo', true)
                ->orderBy('apellido')
                ->get(['id', 'nombre', 'apellido', 'dni']),
        ]);
    }

    public function store(StoreLegajoRequest $request)
    {
        $legajo = Legajo::create([
            ...$request->validated(),
            'estado' => 'Abierto',
            'creado_por' => $request->user()->id,
        ]);

        return redirect()->route('dashboard')
            ->with('status', "Legajo \"{$legajo->numero_legajo}\" creado correctamente.");
    }
}

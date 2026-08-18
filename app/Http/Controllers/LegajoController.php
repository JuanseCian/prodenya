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
        $datos = $request->validated();
        $persona = Persona::findOrFail($datos['persona_id']);

        $legajo = Legajo::create([
            'persona_id' => $persona->id,
            'numero_legajo' => Legajo::generarNumero($persona),
            'motivo_ingreso' => $datos['motivo_ingreso'],
            'observaciones' => $datos['observaciones'] ?? null,
            'estado' => 'Abierto',
            'creado_por' => $request->user()->id,
        ]);

        return redirect()->route('dashboard')
            ->with('status', "Legajo {$legajo->numero_legajo} creado correctamente.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Legajo;
use App\Models\Persona;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('dashboard', [
            'personasRecientes' => Persona::with('localidad')
                ->where('activo', true)
                ->latest('creado_en')
                ->take(5)
                ->get()
                ->map(fn (Persona $persona) => [
                    'id' => $persona->id,
                    'nombreCompleto' => "{$persona->nombre} {$persona->apellido}",
                    'dni' => $persona->dni,
                    'localidad' => $persona->localidad?->nombre,
                    'fechaAlta' => $persona->creado_en->format('d/m/Y'),
                ]),
            'legajosRecientes' => Legajo::with('persona')
                ->latest('creado_en')
                ->take(6)
                ->get()
                ->map(fn (Legajo $legajo) => [
                    'id' => $legajo->id,
                    'numero' => $legajo->numero_legajo,
                    'persona' => "{$legajo->persona->apellido}, {$legajo->persona->nombre}",
                    'dni' => $legajo->persona->dni,
                    'riesgo' => $legajo->nivel_riesgo,
                    'estado' => $legajo->estado,
                    'fechaApertura' => $legajo->fecha_apertura->format('d/m/Y'),
                    'motivo' => $legajo->motivo_ingreso,
                ]),
        ]);
    }
}

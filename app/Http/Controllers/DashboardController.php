<?php

namespace App\Http\Controllers;

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
        ]);
    }
}

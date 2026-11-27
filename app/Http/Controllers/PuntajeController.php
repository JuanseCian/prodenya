<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PuntajeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PuntajeController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->tieneRol('admin', 'coordinador'), 403);

        $ranking = User::query()
            ->with('rol:id,nombre')
            ->withSum('puntajeMovimientos', 'puntos')
            ->withCount('puntajeMovimientos')
            ->where('activo', true)
            ->where('estado_cuenta', User::ESTADO_APROBADO)
            ->whereHas('rol', fn ($query) => $query->whereIn('nombre', PuntajeService::ROLES_RANKING))
            ->orderByDesc('puntaje_movimientos_sum_puntos')
            ->orderBy('name')
            ->get()
            ->values()
            ->map(fn (User $usuario, int $indice): array => [
                'posicion' => $indice + 1,
                'nombre' => $usuario->name,
                'profesion' => $usuario->profesion,
                'rol' => $usuario->rol?->nombre,
                'puntos' => (int) ($usuario->puntaje_movimientos_sum_puntos ?? 0),
                'movimientos' => (int) $usuario->puntaje_movimientos_count,
            ])
            ->all();

        return Inertia::render('estadisticas/ranking', [
            'ranking' => $ranking,
            'participantes' => count($ranking),
            'modulosDisponibles' => [
                'resumen',
                'legajos',
                'problematicas',
                'intervenciones',
                'tareas',
                'agenda',
                'ranking',
            ],
        ]);
    }
}

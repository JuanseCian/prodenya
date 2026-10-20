<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaEvento;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditoriaController extends Controller
{
    public function index(Request $request): Response
    {
        $buscar = trim((string) $request->query('buscar', ''));

        $eventos = AuditoriaEvento::query()
            ->with('usuario')
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where('evento', 'like', "%{$buscar}%")
                    ->orWhere('auditable_type', 'like', "%{$buscar}%")
                    ->orWhereHas('usuario', fn ($usuario) => $usuario->where('name', 'like', "%{$buscar}%"));
            })
            ->orderByDesc('creado_en')
            ->paginate(40)
            ->withQueryString()
            ->through(fn (AuditoriaEvento $evento) => [
                'id' => $evento->id,
                'evento' => $evento->evento,
                'recurso' => $evento->auditable_type ? class_basename($evento->auditable_type) : 'Sistema',
                'recurso_id' => $evento->auditable_id,
                'usuario' => $evento->usuario?->name ?? 'Sistema',
                'creado_en' => $evento->creado_en?->toIso8601String(),
                'datos_antes' => $evento->datos_antes,
                'datos_despues' => $evento->datos_despues,
                'ip_origen' => $evento->ip_origen,
            ]);

        return Inertia::render('auditoria/index', [
            'eventos' => $eventos,
            'filtros' => ['buscar' => $buscar !== '' ? $buscar : null],
        ]);
    }
}

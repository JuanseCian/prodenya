<?php

namespace App\Http\Controllers;

use App\Models\AgendaEvento;
use App\Models\Legajo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AgendaEventoController extends Controller
{
    public function index(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $usuario = $request->user();
        $eventos = AgendaEvento::query()
            ->with(['legajos', 'creador'])
            ->where(function ($query) use ($usuario) {
                $query
                    ->where('visibilidad', 'general')
                    ->orWhere(function ($personal) use ($usuario) {
                        $personal
                            ->where('visibilidad', 'personal')
                            ->where('creado_por', $usuario->id);
                    });
            })
            ->when($request->filled('desde'), fn ($query) => $query->whereDate('fecha_inicio', '>=', $request->date('desde')))
            ->when($request->filled('hasta'), fn ($query) => $query->whereDate('fecha_inicio', '<=', $request->date('hasta')))
            ->orderBy('fecha_inicio')
            ->limit(500)
            ->get()
            ->map(fn (AgendaEvento $evento) => [
                'id' => $evento->id,
                'titulo' => $evento->titulo,
                'tipo' => $evento->tipo,
                'visibilidad' => $evento->visibilidad,
                'fecha_inicio' => $evento->fecha_inicio?->toIso8601String(),
                'fecha_fin' => $evento->fecha_fin?->toIso8601String(),
                'lugar' => $evento->lugar,
                'enlace' => $evento->enlace,
                'estado' => $evento->estado,
                'creador' => $evento->creador ? [
                    'id' => $evento->creador->id,
                    'nombre' => $evento->creador->name,
                ] : null,
                'puede_editar' => $usuario->esAdmin()
                    || $usuario->tieneRol('coordinador')
                    || $evento->creado_por === $usuario->id,
                'legajos' => $evento->legajos->map(fn (Legajo $legajo) => [
                    'id' => $legajo->id,
                    'numero_legajo' => $legajo->numero_legajo,
                ])->values(),
            ])
            ->values();

        if ($request->expectsJson()) {
            return response()->json($eventos);
        }

        return Inertia::render('agenda/index', ['eventos' => $eventos]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->rules());
        $evento = AgendaEvento::create([
            'titulo' => $datos['titulo'],
            'tipo' => $datos['tipo'],
            'visibilidad' => $datos['visibilidad'],
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin'] ?? null,
            'lugar' => $datos['lugar'] ?? null,
            'enlace' => $datos['enlace'] ?? null,
            'participantes_externos' => $datos['participantes_externos'] ?? null,
            'notas' => $datos['notas'] ?? null,
            'estado' => $datos['estado'] ?? 'programado',
            'creado_por' => $request->user()->id,
        ]);

        $evento->legajos()->sync($datos['legajo_ids'] ?? []);
        $evento->usuarios()->sync($datos['usuario_ids'] ?? []);

        return back()->with('status', 'Evento agendado correctamente.');
    }

    public function update(Request $request, AgendaEvento $agendaEvento): RedirectResponse
    {
        abort_unless(
            $request->user()->esAdmin()
                || $request->user()->tieneRol('coordinador')
                || $agendaEvento->creado_por === $request->user()->id,
            403,
        );

        $datos = $request->validate($this->rules());
        $agendaEvento->update([
            'titulo' => $datos['titulo'],
            'tipo' => $datos['tipo'],
            'visibilidad' => $datos['visibilidad'],
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin'] ?? null,
            'lugar' => $datos['lugar'] ?? null,
            'enlace' => $datos['enlace'] ?? null,
            'participantes_externos' => $datos['participantes_externos'] ?? null,
            'notas' => $datos['notas'] ?? null,
            'estado' => $datos['estado'] ?? 'programado',
        ]);
        $agendaEvento->legajos()->sync($datos['legajo_ids'] ?? []);
        $agendaEvento->usuarios()->sync($datos['usuario_ids'] ?? []);

        return back()->with('status', 'Evento actualizado correctamente.');
    }

    public function destroy(Request $request, AgendaEvento $agendaEvento): RedirectResponse
    {
        abort_unless(
            $request->user()->esAdmin()
                || $request->user()->tieneRol('coordinador')
                || $agendaEvento->creado_por === $request->user()->id,
            403,
        );

        $agendaEvento->delete();

        return back()->with('status', 'Evento eliminado.');
    }

    private function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:180'],
            'tipo' => ['required', 'string', 'max:40'],
            'visibilidad' => ['required', Rule::in(['personal', 'general'])],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'lugar' => ['nullable', 'string', 'max:180'],
            'enlace' => ['nullable', 'url', 'max:500'],
            'participantes_externos' => ['nullable', 'array'],
            'notas' => ['nullable', 'string', 'max:5000'],
            'estado' => ['nullable', Rule::in(['programado', 'realizado', 'cancelado'])],
            'legajo_ids' => ['nullable', 'array'],
            'legajo_ids.*' => ['integer', Rule::exists(Legajo::class, 'id')],
            'usuario_ids' => ['nullable', 'array'],
            'usuario_ids.*' => ['integer', Rule::exists(User::class, 'id')],
        ];
    }
}

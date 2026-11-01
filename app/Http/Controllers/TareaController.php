<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TareaController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        $usuario = $request->user();
        $puedeAsignarOtros = $usuario->esAdmin() || $usuario->tieneRol('coordinador');

        $tareas = Tarea::query()
            ->with(['asignado', 'legajo'])
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->string('estado')))
            ->when(
                $puedeAsignarOtros && $request->filled('asignado_a'),
                fn ($query) => $query->where('asignado_a', $request->integer('asignado_a')),
            )
            ->when(
                ! $puedeAsignarOtros,
                fn ($query) => $query->where('asignado_a', $usuario->id),
            )
            ->orderByRaw('fecha_limite IS NULL')
            ->orderBy('fecha_limite')
            ->limit(100)
            ->get()
            ->map(fn (Tarea $tarea) => [
                'id' => $tarea->id,
                'titulo' => $tarea->titulo,
                'descripcion' => $tarea->descripcion,
                'tipo' => $tarea->tipo,
                'prioridad' => $tarea->prioridad,
                'estado' => $tarea->estado,
                'fecha_limite' => $tarea->fecha_limite?->toDateString(),
                'asignado' => $tarea->asignado ? ['id' => $tarea->asignado->id, 'name' => $tarea->asignado->name] : null,
                'legajo' => $tarea->legajo ? ['id' => $tarea->legajo->id, 'numero_legajo' => $tarea->legajo->numero_legajo] : null,
            ])
            ->values();

        if ($request->expectsJson()) {
            return response()->json($tareas);
        }

        return Inertia::render('tareas/index', [
            'tareas' => $tareas,
            'responsables' => $puedeAsignarOtros
                ? User::query()
                    ->where('activo', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'profesion'])
                : [],
            'puedeAsignarOtros' => $puedeAsignarOtros,
            'usuarioActualId' => $usuario->id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $usuario = $request->user();
        $puedeAsignarOtros = $usuario->esAdmin() || $usuario->tieneRol('coordinador');
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'tipo' => ['nullable', 'string', 'max:40'],
            'prioridad' => ['required', Rule::in(['baja', 'normal', 'alta', 'urgente'])],
            'fecha_limite' => ['nullable', 'date'],
            'asignado_a' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'legajo_id' => ['nullable', 'integer', Rule::exists('legajos', 'id')],
            'intervencion_id' => ['nullable', 'integer', Rule::exists('intervenciones', 'id')],
            'mail_message_id' => ['nullable', 'integer', Rule::exists('mail_messages', 'id')],
            'recursos' => ['nullable', 'array'],
            'recursos.*.tipo_recurso' => ['required_with:recursos', Rule::in(['legajo', 'intervencion', 'documento', 'mail'])],
            'recursos.*.recurso_id' => ['required_with:recursos', 'integer'],
        ]);

        $asignadoA = $puedeAsignarOtros && ! empty($datos['asignado_a'])
            ? $datos['asignado_a']
            : $usuario->id;

        $tarea = Tarea::create([
            'titulo' => $datos['titulo'],
            'descripcion' => $datos['descripcion'] ?? null,
            'tipo' => $datos['tipo'] ?? 'derivacion',
            'prioridad' => $datos['prioridad'],
            'fecha_limite' => $datos['fecha_limite'] ?? null,
            'asignado_a' => $asignadoA,
            'legajo_id' => $datos['legajo_id'] ?? null,
            'intervencion_id' => $datos['intervencion_id'] ?? null,
            'mail_message_id' => $datos['mail_message_id'] ?? null,
            'creado_por' => $usuario->id,
            'estado' => 'pendiente',
        ]);

        foreach ($datos['recursos'] ?? [] as $recurso) {
            $tarea->recursos()->create($recurso);
        }

        return back()->with('status', 'Tarea creada correctamente.');
    }

    public function update(Request $request, Tarea $tarea): RedirectResponse
    {
        abort_unless(
            $request->user()->esAdmin()
                || $request->user()->tieneRol('coordinador')
                || $tarea->asignado_a === $request->user()->id,
            403,
        );

        $datos = $request->validate([
            'estado' => ['required', Rule::in(['pendiente', 'en_progreso', 'completada', 'cancelada'])],
        ]);

        $tarea->update([
            'estado' => $datos['estado'],
            'completada_en' => $datos['estado'] === 'completada' ? now() : null,
        ]);

        return back()->with('status', 'Estado de tarea actualizado.');
    }
}

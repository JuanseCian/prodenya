<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\AgendaEvento;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class HomeController extends Controller
{
    public function index()
    {
        return view('frontend.home.home');
    }

    public function panel(Request $request)
    {
        $usuario = $request->user();
        $hoy = today();
        $tareas = Tarea::query()
            ->with(['legajo.persona'])
            ->where('asignado_a', $usuario->id)
            ->whereNotIn('estado', ['completada', 'cancelada'])
            ->orderByRaw('fecha_limite IS NULL')
            ->orderBy('fecha_limite')
            ->orderByDesc('prioridad')
            ->limit(12)
            ->get();

        $eventos = AgendaEvento::query()
            ->with('legajos')
            ->whereDate('fecha_inicio', $hoy)
            ->where(function ($query) use ($usuario) {
                $query->where('visibilidad', 'general')
                    ->orWhere(fn ($personal) => $personal
                        ->where('visibilidad', 'personal')
                        ->where('creado_por', $usuario->id));
            })
            ->orderBy('fecha_inicio')
            ->limit(12)
            ->get();

        return response()->json([
            'fecha' => $hoy->translatedFormat('l d \d\e F'),
            'tareas_pendientes' => Tarea::where('asignado_a', $usuario->id)
                ->whereNotIn('estado', ['completada', 'cancelada'])
                ->count(),
            'tareas_vencidas' => Tarea::where('asignado_a', $usuario->id)
                ->whereNotIn('estado', ['completada', 'cancelada'])
                ->whereDate('fecha_limite', '<', $hoy)
                ->count(),
            'tareas' => $tareas->map(fn (Tarea $tarea) => [
                'id' => $tarea->id,
                'titulo' => $tarea->titulo,
                'prioridad' => $tarea->prioridad,
                'fecha_limite' => $tarea->fecha_limite?->format('d/m/Y'),
                'legajo' => $tarea->legajo?->numero_legajo,
                'legajo_url' => $tarea->legajo && Route::has('legajos.show')
                    ? route('legajos.show', $tarea->legajo)
                    : null,
            ])->values(),
            'eventos' => $eventos->map(fn (AgendaEvento $evento) => [
                'id' => $evento->id,
                'titulo' => $evento->titulo,
                'tipo' => $evento->tipo,
                'hora' => $evento->fecha_inicio?->format('H:i'),
                'lugar' => $evento->lugar,
                'visibilidad' => $evento->visibilidad,
                'agenda_url' => route('agenda.index'),
            ])->values(),
        ]);
    }
}

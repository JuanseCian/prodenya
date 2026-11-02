<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\AgendaEvento;
use App\Models\Tarea;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Route;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $notificaciones = $request->user()->notifications()
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (DatabaseNotification $notification): array => [
                'id' => $notification->id,
                'titulo' => $notification->data['titulo'] ?? 'Notificación',
                'mensaje' => $notification->data['mensaje'] ?? '',
                'url' => $notification->data['url'] ?? null,
                'leida' => $notification->read_at !== null,
                'creada_en' => $notification->created_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        return view('frontend.home.home', [
            'frontendNotificaciones' => $notificaciones,
            'frontendNotificacionesNoLeidas' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function panel(Request $request)
    {
        $usuario = $request->user();
        $hoy = today();
        $hasta = $hoy->copy()->addDays(3);
        $tareasBase = Tarea::query()
            ->where('asignado_a', $usuario->id)
            ->whereNotIn('estado', ['completada', 'cancelada']);
        $tareas = (clone $tareasBase)
            ->where(function ($query) use ($hasta) {
                $query->whereNull('fecha_limite')
                    ->orWhereDate('fecha_limite', '<=', $hasta);
            })
            ->with(['legajo.persona'])
            ->get()
            ->sortBy(function (Tarea $tarea) use ($hoy): array {
                $prioridad = [
                    'urgente' => 0,
                    'alta' => 1,
                    'normal' => 2,
                    'baja' => 3,
                ][$tarea->prioridad] ?? 4;

                if (! $tarea->fecha_limite) {
                    return [3, $prioridad, PHP_INT_MAX, $tarea->id];
                }

                $fecha = $tarea->fecha_limite;
                $estado = $fecha->lt($hoy) ? 0 : ($fecha->isSameDay($hoy) ? 1 : 2);

                return [$estado, $prioridad, $fecha->timestamp, $tarea->id];
            })
            ->take(12)
            ->values();

        $eventosBase = AgendaEvento::query()
            ->where('estado', '!=', 'cancelado')
            ->where(function ($query) use ($usuario) {
                $query->where('visibilidad', 'general')
                    ->orWhere(function ($personal) use ($usuario) {
                        $personal
                            ->where('visibilidad', 'personal')
                            ->where(function ($ownerOrParticipant) use ($usuario) {
                                $ownerOrParticipant->where('creado_por', $usuario->id)
                                    ->orWhereHas('usuarios', fn ($users) => $users->whereKey($usuario->id));
                            });
                    });
            });

        $eventos = (clone $eventosBase)
            ->with('legajos')
            ->whereBetween('fecha_inicio', [$hoy->copy()->startOfDay(), $hasta->copy()->endOfDay()])
            ->orderBy('fecha_inicio')
            ->limit(12)
            ->get();

        return response()->json([
            'fecha' => $hoy->translatedFormat('l d \d\e F'),
            'rango' => [
                'desde' => $hoy->toDateString(),
                'hasta' => $hasta->toDateString(),
                'etiqueta' => 'Hoy y próximos 3 días',
            ],
            'tareas_pendientes' => (clone $tareasBase)->count(),
            'tareas_vencidas' => (clone $tareasBase)
                ->whereNotNull('fecha_limite')
                ->whereDate('fecha_limite', '<', $hoy)
                ->count(),
            'tareas_proximas' => (clone $tareasBase)
                ->whereBetween('fecha_limite', [$hoy->toDateString(), $hasta->toDateString()])
                ->count(),
            'eventos_hoy' => (clone $eventosBase)
                ->whereDate('fecha_inicio', $hoy)
                ->count(),
            'eventos_proximos' => (clone $eventosBase)
                ->whereBetween('fecha_inicio', [$hoy->copy()->startOfDay(), $hasta->copy()->endOfDay()])
                ->count(),
            'tareas' => $tareas->map(fn (Tarea $tarea) => [
                'id' => $tarea->id,
                'titulo' => $tarea->titulo,
                'prioridad' => $tarea->prioridad,
                'fecha_limite' => $tarea->fecha_limite?->format('d/m/Y'),
                'alerta' => $tarea->fecha_limite?->lt($hoy)
                    ? 'Vencida'
                    : ($tarea->fecha_limite?->isSameDay($hoy) ? 'Vence hoy' : ($tarea->fecha_limite ? 'Próxima' : 'Sin fecha')),
                'urgencia' => $tarea->fecha_limite?->lt($hoy)
                    ? 'vencida'
                    : ($tarea->fecha_limite?->isSameDay($hoy) ? 'hoy' : ($tarea->fecha_limite ? 'proxima' : 'sin_fecha')),
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
                'fecha' => $evento->fecha_inicio?->format('d/m'),
                'es_hoy' => $evento->fecha_inicio?->isSameDay($hoy),
                'lugar' => $evento->lugar,
                'visibilidad' => $evento->visibilidad,
                'agenda_url' => route('agenda.index'),
            ])->values(),
        ]);
    }
}

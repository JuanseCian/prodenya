<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\AgendaEvento;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\Tarea;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();

        return view('frontend.home.home', [
            'homeSaludo' => $this->saludo(),
            'homeTareasPendientes' => $this->tareasAbiertas($usuario->id)->count(),
            'homeTareasVencidas' => $this->tareasAbiertas($usuario->id)
                ->whereNotNull('fecha_limite')
                ->whereDate('fecha_limite', '<', today())
                ->count(),
            'frontendNotificacionesNoLeidas' => $usuario->avisosSinLeer(),
            'showPasskeyNotice' => Features::canManagePasskeys()
                && ! $usuario->passkeys()->exists(),
            'passkeyPasswordConfirmed' => now()->timestamp - (int) $request->session()->get('auth.password_confirmed_at', 0)
                <= (int) config('auth.password_timeout'),
        ]);
    }

    /**
     * Busca personas por nombre, apellido o DNI para abrir su legajo o crear uno nuevo.
     */
    public function buscarPersonas(Request $request): JsonResponse
    {
        $termino = trim((string) $request->query('q'));

        if (mb_strlen($termino) < 2) {
            return response()->json(['personas' => []]);
        }

        $digitos = preg_replace('/\D/', '', $termino);
        $esDni = $digitos !== '' && $digitos === preg_replace('/[\s.]/', '', $termino);

        $personas = Persona::query()
            ->where('activo', true)
            ->when($esDni, fn ($query) => $query->where('dni', 'like', "{$digitos}%"))
            ->when(! $esDni, function ($query) use ($termino) {
                foreach (preg_split('/[\s,]+/', $termino, -1, PREG_SPLIT_NO_EMPTY) as $palabra) {
                    $query->where(fn ($porPalabra) => $porPalabra
                        ->where('apellido', 'like', "%{$palabra}%")
                        ->orWhere('nombre', 'like', "%{$palabra}%"));
                }
            })
            ->with(['legajos' => fn ($query) => $query->latest('fecha_apertura')->select(['id', 'persona_id', 'numero_legajo', 'estado', 'fecha_apertura'])])
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->limit(8)
            ->get(['id', 'nombre', 'apellido', 'dni', 'fecha_nacimiento']);

        return response()->json([
            'personas' => $personas->map(fn (Persona $persona): array => [
                'id' => $persona->id,
                'nombre' => trim(mb_convert_case("{$persona->apellido}, {$persona->nombre}", MB_CASE_TITLE), ', '),
                'dni' => (string) ($persona->dni ?? ''),
                'edad' => $persona->fecha_nacimiento?->age,
                'legajos' => $persona->legajos->map(fn (Legajo $legajo): array => [
                    'numero' => $legajo->numero_legajo,
                    'estado' => $legajo->estado,
                    'url' => route('legajos.show', $legajo),
                ])->values(),
            ])->values(),
        ]);
    }

    public function panel(Request $request)
    {
        $usuario = $request->user();
        $hoy = today();
        $hasta = $hoy->copy()->addDays(3);
        $tareasBase = $this->tareasAbiertas($usuario->id);
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

    /**
     * Tareas asignadas al usuario que todavía no se cerraron.
     */
    private function tareasAbiertas(int $usuarioId): Builder
    {
        return Tarea::query()
            ->where('asignado_a', $usuarioId)
            ->whereNotIn('estado', ['completada', 'cancelada']);
    }

    private function saludo(): string
    {
        $hora = now()->hour;

        return match (true) {
            $hora < 13 => 'Buen día',
            $hora < 20 => 'Buenas tardes',
            default => 'Buenas noches',
        };
    }
}

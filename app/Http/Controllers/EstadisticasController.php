<?php

namespace App\Http\Controllers;

use App\Models\AgendaEvento;
use App\Models\AuditoriaEvento;
use App\Models\Intervencion;
use App\Models\Persona;
use App\Models\Problematica;
use App\Models\Tarea;
use App\Models\TipoIntervencion;
use App\Models\User;
use App\Services\MapboxGeocodingService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EstadisticasController extends Controller
{
    public function __construct(private MapboxGeocodingService $mapboxGeocoding) {}

    public function __invoke(Request $request): Response
    {
        return Inertia::render('estadisticas/index', $this->datos($request));
    }

    public function legajos(Request $request): Response
    {
        return Inertia::render('estadisticas/legajos', $this->datos($request));
    }

    public function problematicas(Request $request): Response
    {
        return Inertia::render('estadisticas/problematicas', $this->datos($request));
    }

    public function intervenciones(Request $request): Response
    {
        return Inertia::render('estadisticas/intervenciones', $this->datos($request));
    }

    public function tareas(Request $request): Response
    {
        return Inertia::render('estadisticas/tareas', $this->datos($request));
    }

    public function agenda(Request $request): Response
    {
        return Inertia::render('estadisticas/agenda', $this->datos($request));
    }

    public function exportar(Request $request): StreamedResponse
    {
        $datos = $this->datos($request);
        $filas = [
            ['Indicador', 'Valor'],
            ['Personas dadas de alta', $datos['kpis']['personasAltas']],
            ['Legajos abiertos', $datos['kpis']['legajosAltas']],
            ['Legajos cerrados', $datos['kpis']['legajosBajas']],
            ['Intervenciones', $datos['kpis']['intervenciones']],
            ['Tareas', $datos['kpis']['tareas']],
            ['Tareas completadas', $datos['kpis']['tareasCompletadas']],
            ['Eventos de agenda', $datos['kpis']['agenda']],
            ['Modificaciones y auditoría', $datos['kpis']['modificaciones']],
            [],
            ['Problemática', 'Casos'],
        ];

        foreach ($datos['problematicas'] as $problematica) {
            $filas[] = [$problematica['nombre'], $problematica['total']];
        }

        foreach ($datos['barrios'] as $barrio) {
            $filas[] = ["Barrio: {$barrio['nombre']}", $barrio['total']];
        }

        return response()->streamDownload(function () use ($filas): void {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");

            foreach ($filas as $fila) {
                fputcsv($salida, $fila, ';');
            }

            fclose($salida);
        }, 'estadisticas-'.today()->toDateString().'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function datos(Request $request): array
    {
        $rango = $this->resolverRango($request);
        $filtros = $this->resolverFiltros($request);
        $intervalos = $this->intervalos($rango['desde'], $rango['hasta']);
        $principal = $this->resumen($rango['desde'], $rango['hasta'], $filtros, $request, $intervalos);
        $comparacion = null;

        if ($filtros['comparar'] !== 'ninguno') {
            $rangoComparacion = $this->rangoComparacion($rango['desde'], $rango['hasta'], $filtros['comparar']);
            $comparacion = [
                'rango' => [
                    'desde' => $rangoComparacion['desde']->toDateString(),
                    'hasta' => $rangoComparacion['hasta']->toDateString(),
                ],
                'kpis' => $this->resumen(
                    $rangoComparacion['desde'],
                    $rangoComparacion['hasta'],
                    $filtros,
                    $request,
                    $intervalos,
                )['kpis'],
            ];
        }

        return $principal + [
            'modulosDisponibles' => $this->modulosDisponibles($request),
            'filtros' => $filtros,
            'rango' => [
                'desde' => $rango['desde']->toDateString(),
                'hasta' => $rango['hasta']->toDateString(),
                'descripcion' => $this->descripcionRango($rango['desde'], $rango['hasta']),
            ],
            'intervalos' => $intervalos,
            'comparacion' => $comparacion,
            'catalogos' => [
                'profesionales' => User::query()->where('activo', true)->orderBy('name')->get(['id', 'name']),
                'responsables' => User::query()->where('activo', true)->orderBy('name')->get(['id', 'name']),
                'tiposIntervencion' => TipoIntervencion::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
                'generos' => [
                    ['value' => 'M', 'label' => 'Masculino'],
                    ['value' => 'F', 'label' => 'Femenino'],
                    ['value' => 'X', 'label' => 'X'],
                    ['value' => 'NO_ESPECIFICA', 'label' => 'No especifica'],
                ],
                'edades' => [
                    ['value' => '0-5', 'label' => '0 a 5 años'],
                    ['value' => '6-12', 'label' => '6 a 12 años'],
                    ['value' => '13-17', 'label' => '13 a 17 años'],
                    ['value' => '18-21', 'label' => '18 a 21 años'],
                    ['value' => '22+', 'label' => '22 años o más'],
                ],
            ],
        ];
    }

    private function modulosDisponibles(Request $request): array
    {
        $usuario = $request->user();
        $lectura = $usuario?->tieneRol(
            'admin',
            'operador',
            'consulta',
            'oficios',
            'coordinador',
            'profesional',
            'administrativo',
        ) ?? false;

        return array_values(array_filter([
            $lectura ? 'resumen' : null,
            $lectura ? 'legajos' : null,
            $lectura ? 'problematicas' : null,
            $lectura ? 'intervenciones' : null,
            $usuario?->tieneRol('admin', 'operador', 'oficios', 'coordinador', 'profesional', 'administrativo')
                ? 'tareas'
                : null,
            $usuario?->tieneRol('admin', 'coordinador', 'profesional', 'administrativo')
                ? 'agenda'
                : null,
        ]));
    }

    private function resumen(
        CarbonInterface $desde,
        CarbonInterface $hasta,
        array $filtros,
        Request $request,
        array $intervalos,
    ): array {
        $personas = $this->personasQuery($desde, $hasta, $filtros);
        $legajos = $this->legajosQuery($desde, $hasta, $filtros);
        $intervenciones = $this->intervencionesQuery($desde, $hasta, $filtros);
        $tareas = $this->tareasQuery($desde, $hasta, $filtros);
        $agenda = $this->agendaQuery($desde, $hasta, $filtros, $request);

        $problematicas = Problematica::query()
            ->whereNull('deleted_at')
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(function (Problematica $problematica) use ($legajos): array {
                return [
                    'id' => $problematica->id,
                    'nombre' => $problematica->nombre,
                    'total' => (clone $legajos)->whereHas('problematicas', fn ($query) => $query->whereKey($problematica->id))->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $personasAltas = (clone $personas)->count();
        $legajosAltas = (clone $legajos)->count();
        $legajosBajas = (clone $this->legajosBaseQuery($filtros))
            ->whereBetween('fecha_cierre', [$desde->toDateString(), $hasta->toDateString()])
            ->count();
        $modificacionDates = $this->modificacionDates($desde, $hasta, $filtros);

        return [
            'kpis' => [
                'personasAltas' => $personasAltas,
                'legajosAltas' => $legajosAltas,
                'legajosBajas' => $legajosBajas,
                'totalLegajos' => $legajosAltas,
                'legajosAbiertos' => (clone $legajos)->where('estado', 'Abierto')->count(),
                'legajosCerrados' => $legajosBajas,
                'problematicasConCasos' => $problematicas->where('total', '>', 0)->count(),
                'intervenciones' => (clone $intervenciones)->count(),
                'tareas' => (clone $tareas)->count(),
                'tareasCompletadas' => (clone $tareas)->where('estado', 'completada')->count(),
                'agenda' => (clone $agenda)->count(),
                'modificaciones' => $modificacionDates->count(),
            ],
            'legajosPorEstado' => [
                'abiertos' => (clone $legajos)->where('estado', 'Abierto')->count(),
                'cerrados' => (clone $legajos)->where('estado', 'Cerrado')->count(),
                'archivados' => (clone $legajos)->where('estado', 'Archivado')->count(),
            ],
            'personasPorPeriodo' => $this->agruparFechas((clone $personas)->pluck('creado_en'), $intervalos),
            'legajosPorPeriodo' => $this->agruparFechas((clone $legajos)->pluck('fecha_apertura'), $intervalos),
            'bajasPorPeriodo' => $this->agruparFechas(
                (clone $this->legajosBaseQuery($filtros))
                    ->whereBetween('fecha_cierre', [$desde->toDateString(), $hasta->toDateString()])
                    ->pluck('fecha_cierre'),
                $intervalos,
            ),
            'modificacionesPorPeriodo' => $this->agruparFechas($modificacionDates, $intervalos),
            'intervencionesPorPeriodo' => $this->agruparFechas((clone $intervenciones)->pluck('fecha_intervencion'), $intervalos),
            'tareasPorPeriodo' => $this->agruparFechas((clone $tareas)->pluck('created_at'), $intervalos),
            'agendaPorPeriodo' => $this->agruparFechas((clone $agenda)->pluck('fecha_inicio'), $intervalos),
            'problematicas' => $problematicas->all(),
            'problematicaSeleccionada' => $this->problematicaSeleccionada($filtros['problematica']),
            'intervencionesPorTipo' => $this->agrupacionIntervenciones(clone $intervenciones),
            'intervencionesPorProfesional' => $this->agrupacionIntervencionesPorProfesional(clone $intervenciones),
            'tareasPorEstado' => $this->agrupacion($tareas, 'estado'),
            'agendaPorTipo' => $this->agrupacion(clone $agenda, 'tipo'),
            'agendaPorEstado' => $this->agrupacion(clone $agenda, 'estado'),
            'barrios' => $this->barrios($legajos),
            'mapaBarrios' => $request->routeIs('estadisticas')
                ? $this->mapaBarrios($legajos)
                : [
                    'disponible' => false,
                    'mensaje' => 'El mapa está disponible en el resumen de estadísticas.',
                    'zonas' => [],
                ],
            'modificacionesTimeline' => $this->modificacionesTimeline($desde, $hasta),
            'actividadTimeline' => $this->actividadTimeline($desde, $hasta, $filtros),
            'agendaTimeline' => (clone $agenda)->with('creador')->latest('fecha_inicio')->limit(12)->get()->map(fn (AgendaEvento $evento) => [
                'id' => $evento->id,
                'titulo' => $evento->titulo,
                'tipo' => $evento->tipo,
                'estado' => $evento->estado,
                'fecha' => $evento->fecha_inicio?->toIso8601String(),
                'creador' => $evento->creador?->name,
            ])->values()->all(),
        ];
    }

    private function personasQuery(CarbonInterface $desde, CarbonInterface $hasta, array $filtros): Builder
    {
        return $this->personasBaseQuery($filtros)
            ->whereBetween('creado_en', [$desde->startOfDay(), $hasta->endOfDay()]);
    }

    private function legajosQuery(CarbonInterface $desde, CarbonInterface $hasta, array $filtros): Builder
    {
        return $this->legajosBaseQuery($filtros)
            ->whereBetween('fecha_apertura', [$desde->toDateString(), $hasta->toDateString()]);
    }

    private function personasBaseQuery(array $filtros): Builder
    {
        return Persona::query()
            ->when($filtros['estado'], fn (Builder $query, string $estado) => $query->where('estado', $estado))
            ->when($filtros['problematica'], fn (Builder $query, int $id) => $query->whereHas('problematicas', fn ($relation) => $relation->whereKey($id)))
            ->when($filtros['profesional'], fn (Builder $query, int $id) => $query->where('profesional_id', $id))
            ->when($filtros['genero'], fn (Builder $query, string $genero) => $query->where('genero', $genero))
            ->when($filtros['edad'], fn (Builder $query, string $edad) => $this->aplicarEdad($query, $edad));
    }

    private function legajosBaseQuery(array $filtros): Builder
    {
        return $this->personasBaseQuery($filtros)->whereNotNull('numero_legajo');
    }

    private function intervencionesQuery(CarbonInterface $desde, CarbonInterface $hasta, array $filtros): Builder
    {
        return Intervencion::query()
            ->whereBetween('fecha_intervencion', [$desde->toDateString(), $hasta->toDateString()])
            ->when($filtros['tipo_intervencion'], fn (Builder $query, int $id) => $query->where('tipo_intervencion_id', $id))
            ->when($filtros['profesional'], fn (Builder $query, int $id) => $query->where('profesional_id', $id))
            ->when($filtros['problematica'], fn (Builder $query, int $id) => $query->whereHas('legajo.persona.problematicas', fn ($relation) => $relation->whereKey($id)))
            ->when($filtros['estado'], fn (Builder $query, string $estado) => $query->whereHas('legajo', fn ($legajo) => $legajo->where('estado', $estado)))
            ->when($filtros['genero'] || $filtros['edad'], fn (Builder $query) => $query->whereHas('legajo.persona', function ($persona) use ($filtros) {
                $this->aplicarAtributosPersona($persona, $filtros);
            }));
    }

    private function tareasQuery(CarbonInterface $desde, CarbonInterface $hasta, array $filtros): Builder
    {
        return Tarea::query()
            ->whereBetween('created_at', [$desde->startOfDay(), $hasta->endOfDay()])
            ->when($filtros['tarea_estado'], fn (Builder $query, string $estado) => $query->where('estado', $estado))
            ->when($filtros['responsable'], fn (Builder $query, int $id) => $query->where('asignado_a', $id))
            ->when($filtros['problematica'], fn (Builder $query, int $id) => $query->whereHas('legajo.persona.problematicas', fn ($relation) => $relation->whereKey($id)))
            ->when($filtros['profesional'], fn (Builder $query, int $id) => $query->whereHas('legajo', fn ($legajo) => $legajo->where('profesional_id', $id)));
    }

    private function agendaQuery(CarbonInterface $desde, CarbonInterface $hasta, array $filtros, Request $request): Builder
    {
        return AgendaEvento::query()
            ->whereBetween('fecha_inicio', [$desde->startOfDay(), $hasta->endOfDay()])
            ->where(function (Builder $query) use ($request) {
                $query->where('visibilidad', 'general')
                    ->orWhere(fn (Builder $personal) => $personal->where('visibilidad', 'personal')->where('creado_por', $request->user()->id));
            })
            ->when($filtros['agenda_estado'], fn (Builder $query, string $estado) => $query->where('estado', $estado))
            ->when($filtros['responsable'], fn (Builder $query, int $id) => $query->where('creado_por', $id));
    }

    private function aplicarAtributosPersona($query, array $filtros): void
    {
        $query->when($filtros['genero'], fn ($relation, string $genero) => $relation->where('genero', $genero));
        $query->when($filtros['edad'], fn ($relation, string $edad) => $this->aplicarEdad($relation, $edad));
    }

    private function aplicarEdad($query, string $edad)
    {
        $rangos = [
            '0-5' => [0, 5],
            '6-12' => [6, 12],
            '13-17' => [13, 17],
            '18-21' => [18, 21],
            '22+' => [22, null],
        ];
        [$min, $max] = $rangos[$edad];
        $hoy = today();
        $query->whereDate('fecha_nacimiento', '<=', $hoy->copy()->subYears($min));

        if ($max !== null) {
            $query->whereDate('fecha_nacimiento', '>', $hoy->copy()->subYears($max + 1));
        }

        return $query;
    }

    private function agrupacion(Builder $query, string $campo): array
    {
        return $query->select($campo)->selectRaw('COUNT(*) as total')->groupBy($campo)->orderByDesc('total')->get()->map(fn ($fila) => [
            'nombre' => $fila->{$campo} ?: 'Sin especificar',
            'total' => (int) $fila->total,
        ])->values()->all();
    }

    private function agrupacionIntervenciones(Builder $query): array
    {
        return $query->with('tipo')->get()->groupBy('tipo_intervencion_id')->map(function (Collection $items) {
            return [
                'id' => $items->first()->tipo_intervencion_id,
                'nombre' => $items->first()->tipo?->nombre ?? 'Sin tipo',
                'total' => $items->count(),
            ];
        })->sortByDesc('total')->values()->all();
    }

    private function agrupacionIntervencionesPorProfesional(Builder $query): array
    {
        return $query->with('profesional')->get()
            ->groupBy('profesional_id')
            ->map(fn (Collection $items) => [
                'id' => $items->first()->profesional_id,
                'nombre' => $items->first()->profesional?->name ?? 'Sin profesional',
                'total' => $items->count(),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    private function barrios(Builder $legajos): array
    {
        return (clone $legajos)
            ->join('barrios', 'barrios.id', '=', 'personas.barrio_id')
            ->join('localidades', 'localidades.id', '=', 'personas.localidad_id')
            ->where('localidades.nombre', 'like', 'San Nicol%')
            ->select('barrios.id', 'barrios.nombre')
            ->selectRaw('COUNT(DISTINCT personas.id) as total')
            ->groupBy('barrios.id', 'barrios.nombre')
            ->orderByDesc('total')
            ->orderBy('barrios.nombre')
            ->get()
            ->map(fn ($barrio) => ['id' => $barrio->id, 'nombre' => $barrio->nombre, 'total' => (int) $barrio->total])
            ->values()
            ->all();
    }

    /**
     * @return array{disponible: bool, mensaje: string, zonas: array<int, array<string, mixed>>}
     */
    private function mapaBarrios(Builder $legajos): array
    {
        $sinDatos = [
            'disponible' => false,
            'mensaje' => 'El mapa necesita configurar MAPBOX_ACCESS_TOKEN para geocodificar las direcciones.',
            'zonas' => [],
        ];

        if (! $this->mapboxGeocoding->configured()) {
            return $sinDatos;
        }

        $personas = (clone $legajos)
            ->whereHas('localidad', fn (Builder $query) => $query->where('nombre', 'like', 'San Nicol%'))
            ->with(['localidad', 'barrio.localidad', 'problematicas:id,nombre'])
            ->get([
                'id',
                'direccion_calle',
                'direccion_numero',
                'localidad_id',
                'barrio_id',
                'latitud',
                'longitud',
                'geocodificacion_precision',
                'geocodificacion_consulta_hash',
            ]);

        if ($personas->isEmpty()) {
            return [
                'disponible' => true,
                'mensaje' => 'No hay casos geolocalizables en el período seleccionado.',
                'zonas' => [],
            ];
        }

        $personasCoordenadas = $this->mapboxGeocoding->geocodePeople($personas);
        $barrios = $personas->pluck('barrio')->filter()->unique('id')->values();
        $barriosCoordenadas = $this->mapboxGeocoding->geocodeBarrios($barrios);
        $zonas = [];

        foreach ($personas as $persona) {
            $coordenada = $personasCoordenadas[$persona->id]
                ?? ($persona->barrio ? ($barriosCoordenadas[$persona->barrio->id] ?? null) : null);

            if ($coordenada === null) {
                continue;
            }

            $latitud = round($coordenada['latitude'], 3);
            $longitud = round($coordenada['longitude'], 3);
            $clave = sprintf('%.3f:%.3f', $latitud, $longitud);

            if (! isset($zonas[$clave])) {
                $zonas[$clave] = [
                    'id' => $clave,
                    'latitud' => $latitud,
                    'longitud' => $longitud,
                    'total' => 0,
                    'problematicas' => [],
                ];
            }

            $zonas[$clave]['total']++;

            foreach ($persona->problematicas as $problematica) {
                $problematicaId = (string) $problematica->id;

                if (! isset($zonas[$clave]['problematicas'][$problematicaId])) {
                    $zonas[$clave]['problematicas'][$problematicaId] = [
                        'id' => $problematica->id,
                        'nombre' => $problematica->nombre,
                        'total' => 0,
                    ];
                }

                $zonas[$clave]['problematicas'][$problematicaId]['total']++;
            }
        }

        $zonas = collect($zonas)
            ->filter(fn (array $zona): bool => $zona['total'] >= 3)
            ->map(function (array $zona): array {
                $zona['problematicas'] = collect($zona['problematicas'])
                    ->sortByDesc('total')
                    ->values()
                    ->all();

                return $zona;
            })
            ->sortByDesc('total')
            ->values()
            ->all();

        return [
            'disponible' => true,
            'mensaje' => $zonas === []
                ? 'No hay zonas con al menos 3 casos para mostrar de forma agregada.'
                : 'Las zonas se agrupan y no muestran personas ni domicilios individuales.',
            'zonas' => $zonas,
        ];
    }

    private function modificacionDates(CarbonInterface $desde, CarbonInterface $hasta, array $filtros): Collection
    {
        $dates = collect()
            ->merge((clone $this->personasBaseQuery($filtros))->whereBetween('actualizado_en', [$desde->startOfDay(), $hasta->endOfDay()])->pluck('actualizado_en'))
            ->merge((clone $this->legajosBaseQuery($filtros))->whereBetween('actualizado_en', [$desde->startOfDay(), $hasta->endOfDay()])->pluck('actualizado_en'))
            ->merge(Intervencion::query()->whereBetween('actualizado_en', [$desde->startOfDay(), $hasta->endOfDay()])->pluck('actualizado_en'))
            ->merge(Tarea::query()->whereBetween('updated_at', [$desde->startOfDay(), $hasta->endOfDay()])->pluck('updated_at'))
            ->merge(AgendaEvento::query()->whereBetween('updated_at', [$desde->startOfDay(), $hasta->endOfDay()])->pluck('updated_at'))
            ->merge(AuditoriaEvento::query()->where('evento', 'editado')->whereBetween('creado_en', [$desde->startOfDay(), $hasta->endOfDay()])->pluck('creado_en'));

        return $dates->filter()->values();
    }

    private function modificacionesTimeline(CarbonInterface $desde, CarbonInterface $hasta): array
    {
        return AuditoriaEvento::query()
            ->with('usuario')
            ->where('evento', 'editado')
            ->whereBetween('creado_en', [$desde->startOfDay(), $hasta->endOfDay()])
            ->latest('creado_en')
            ->limit(12)
            ->get()
            ->map(fn (AuditoriaEvento $evento) => [
                'id' => $evento->id,
                'evento' => $evento->evento,
                'fecha' => $evento->creado_en?->toIso8601String(),
                'usuario' => $evento->usuario?->name,
                'tipo' => class_basename((string) $evento->auditable_type),
            ])
            ->values()
            ->all();
    }

    private function actividadTimeline(CarbonInterface $desde, CarbonInterface $hasta, array $filtros): array
    {
        $eventos = collect();

        $eventos = $eventos->merge(
            $this->personasBaseQuery($filtros)
                ->whereBetween('creado_en', [$desde->startOfDay(), $hasta->endOfDay()])
                ->get(['id', 'creado_en'])
                ->map(fn (Persona $persona) => [
                    'id' => "persona-alta-{$persona->id}",
                    'fecha' => $persona->creado_en?->toIso8601String(),
                    'evento' => 'Alta de persona',
                    'tipo' => 'Persona',
                    'usuario' => 'Sistema',
                ]),
        );

        $eventos = $eventos->merge(
            $this->legajosBaseQuery($filtros)
                ->whereBetween('fecha_apertura', [$desde->toDateString(), $hasta->toDateString()])
                ->get(['id', 'fecha_apertura'])
                ->map(fn (Persona $persona) => [
                    'id' => "legajo-alta-{$persona->id}",
                    'fecha' => $persona->fecha_apertura?->toIso8601String(),
                    'evento' => 'Apertura de legajo',
                    'tipo' => 'Legajo',
                    'usuario' => 'Sistema',
                ]),
        );

        $eventos = $eventos->merge(
            $this->legajosBaseQuery($filtros)
                ->whereBetween('fecha_cierre', [$desde->toDateString(), $hasta->toDateString()])
                ->get(['id', 'fecha_cierre'])
                ->map(fn (Persona $persona) => [
                    'id' => "legajo-baja-{$persona->id}",
                    'fecha' => $persona->fecha_cierre?->toIso8601String(),
                    'evento' => 'Cierre de legajo',
                    'tipo' => 'Legajo',
                    'usuario' => 'Sistema',
                ]),
        );

        return $eventos
            ->merge($this->modificacionesTimeline($desde, $hasta))
            ->sortByDesc('fecha')
            ->take(30)
            ->values()
            ->all();
    }

    private function resolverFiltros(Request $request): array
    {
        $entero = static fn (string $key): ?int => $request->integer($key) > 0 ? $request->integer($key) : null;
        $edad = (string) $request->query('edad', '');

        return [
            'preset' => in_array($request->query('preset'), ['hoy', 'mes_actual', 'anio_actual', 'personalizado'], true) ? $request->query('preset') : 'mes_actual',
            'comparar' => in_array($request->query('comparar'), ['anterior', 'anio_anterior'], true) ? $request->query('comparar') : 'ninguno',
            'desde' => $request->query('desde'),
            'hasta' => $request->query('hasta'),
            'periodo' => in_array($request->query('periodo'), ['dia', 'mes', 'anio'], true) ? $request->query('periodo') : 'mes',
            'anio' => (int) $request->query('anio', today()->year),
            'mes' => (int) $request->query('mes', today()->month),
            'estado' => in_array($request->query('estado'), ['Abierto', 'Cerrado', 'Archivado'], true) ? $request->query('estado') : null,
            'problematica' => $entero('problematica'),
            'profesional' => $entero('profesional'),
            'responsable' => $entero('responsable'),
            'edad' => array_key_exists($edad, ['0-5' => true, '6-12' => true, '13-17' => true, '18-21' => true, '22+' => true]) ? $edad : null,
            'genero' => in_array($request->query('genero'), ['M', 'F', 'X', 'NO_ESPECIFICA'], true) ? $request->query('genero') : null,
            'tipo_intervencion' => $entero('tipo_intervencion'),
            'tarea_estado' => in_array($request->query('tarea_estado'), ['pendiente', 'en_progreso', 'completada', 'cancelada'], true) ? $request->query('tarea_estado') : null,
            'agenda_estado' => in_array($request->query('agenda_estado'), ['programado', 'realizado', 'cancelado'], true) ? $request->query('agenda_estado') : null,
        ];
    }

    private function resolverRango(Request $request): array
    {
        $hoy = today();
        $preset = $request->query('preset');

        if ($preset === 'hoy') {
            return ['desde' => $hoy->copy()->startOfDay(), 'hasta' => $hoy->copy()->endOfDay()];
        }
        if ($preset === 'anio_actual') {
            return ['desde' => $hoy->copy()->startOfYear(), 'hasta' => $hoy->copy()->endOfDay()];
        }
        if ($preset === 'personalizado' && $request->filled('desde') && $request->filled('hasta')) {
            $desde = Carbon::parse($request->query('desde'))->startOfDay();
            $hasta = Carbon::parse($request->query('hasta'))->endOfDay();

            if ($desde->lessThanOrEqualTo($hasta)) {
                return ['desde' => $desde, 'hasta' => $hasta];
            }
        }

        // Keep the previous periodo/anio/mes links valid for bookmarked views.
        if (! $preset && in_array($request->query('periodo'), ['dia', 'mes', 'anio'], true)) {
            $periodo = $request->query('periodo');
            $anio = max(2020, min((int) $request->query('anio', $hoy->year), $hoy->year));
            $mes = max(1, min((int) $request->query('mes', $hoy->month), 12));

            if ($periodo === 'dia') {
                $desde = Carbon::create($anio, $mes, 1)->startOfDay();
                $hasta = $desde->copy()->endOfMonth()->min($hoy->copy()->endOfDay());

                return ['desde' => $desde, 'hasta' => $hasta];
            }

            if ($periodo === 'anio') {
                return ['desde' => $hoy->copy()->subYears(4)->startOfYear(), 'hasta' => $hoy->copy()->endOfDay()];
            }

            $desde = Carbon::create($anio, 1, 1)->startOfYear();
            $hasta = $desde->copy()->endOfYear()->min($hoy->copy()->endOfDay());

            return ['desde' => $desde, 'hasta' => $hasta];
        }

        return ['desde' => $hoy->copy()->startOfMonth(), 'hasta' => $hoy->copy()->endOfDay()];
    }

    private function rangoComparacion(CarbonInterface $desde, CarbonInterface $hasta, string $tipo): array
    {
        if ($tipo === 'anio_anterior') {
            return ['desde' => $desde->copy()->subYear(), 'hasta' => $hasta->copy()->subYear()];
        }

        $dias = $desde->diffInDays($hasta) + 1;
        $fin = $desde->copy()->subDay()->endOfDay();

        return ['desde' => $fin->copy()->subDays($dias - 1)->startOfDay(), 'hasta' => $fin];
    }

    private function intervalos(CarbonInterface $desde, CarbonInterface $hasta): array
    {
        $dias = $desde->diffInDays($hasta) + 1;
        $intervalos = [];

        if ($dias <= 62) {
            for ($fecha = $desde->copy()->startOfDay(); $fecha->lessThanOrEqualTo($hasta); $fecha = $fecha->addDay()) {
                $intervalos[] = ['key' => $fecha->toDateString(), 'label' => $fecha->format('d/m')];
            }

            return $intervalos;
        }

        if ($dias <= 800) {
            for ($fecha = $desde->copy()->startOfMonth(); $fecha->lessThanOrEqualTo($hasta); $fecha = $fecha->addMonth()) {
                $intervalos[] = ['key' => $fecha->format('Y-m'), 'label' => $fecha->format('M Y')];
            }

            return $intervalos;
        }

        for ($fecha = $desde->copy()->startOfYear(); $fecha->lessThanOrEqualTo($hasta); $fecha = $fecha->addYear()) {
            $intervalos[] = ['key' => $fecha->format('Y'), 'label' => $fecha->format('Y')];
        }

        return $intervalos;
    }

    private function agruparFechas(iterable $fechas, array $intervalos): array
    {
        $totales = collect($intervalos)->mapWithKeys(fn (array $intervalo) => [$intervalo['key'] => 0]);
        $longitud = strlen((string) ($intervalos[0]['key'] ?? ''));

        foreach ($fechas as $fecha) {
            if (! $fecha) {
                continue;
            }

            $fecha = Carbon::parse($fecha);
            $clave = $longitud === 4 ? $fecha->format('Y') : ($longitud === 7 ? $fecha->format('Y-m') : $fecha->toDateString());

            if ($totales->has($clave)) {
                $totales->put($clave, $totales->get($clave) + 1);
            }
        }

        return $totales->values()->all();
    }

    private function problematicaSeleccionada(?int $id): ?array
    {
        if (! $id) {
            return null;
        }

        $problematica = Problematica::query()->find($id);

        return $problematica ? ['id' => $problematica->id, 'nombre' => $problematica->nombre] : null;
    }

    private function descripcionRango(CarbonInterface $desde, CarbonInterface $hasta): string
    {
        if ($desde->isSameDay($hasta)) {
            return $desde->format('d/m/Y');
        }

        return $desde->format('d/m/Y').' al '.$hasta->format('d/m/Y');
    }
}

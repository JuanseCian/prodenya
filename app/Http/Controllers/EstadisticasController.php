<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Problematica;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EstadisticasController extends Controller
{
    private const PERIODOS = ['dia', 'mes', 'anio'];

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

    public function intervenciones(): Response
    {
        return Inertia::render('estadisticas/intervenciones', [
            'intervencionesDisponible' => false,
        ]);
    }

    private function datos(Request $request): array
    {
        $hoy = today();
        $periodo = in_array($request->query('periodo'), self::PERIODOS, true)
            ? $request->query('periodo')
            : 'mes';
        $anio = max(2020, min((int) $request->query('anio', $hoy->year), $hoy->year));
        $mes = max(1, min((int) $request->query('mes', $hoy->month), 12));
        if ($anio === $hoy->year) {
            $mes = min($mes, $hoy->month);
        }
        $problematicaId = $request->integer('problematica');
        $problematicaSeleccionada = $problematicaId > 0
            ? Problematica::query()->find($problematicaId)
            : null;
        $rango = $this->rango($periodo, $anio, $mes, $hoy);

        $legajosQuery = Persona::query()
            ->whereNotNull('numero_legajo')
            ->whereNotNull('fecha_apertura')
            ->whereBetween('fecha_apertura', [$rango['desde'], $rango['hasta']])
            ->when(
                $problematicaSeleccionada,
                fn ($query) => $query->whereHas(
                    'problematicas',
                    fn ($problematica) => $problematica->whereKey($problematicaSeleccionada->id),
                ),
            );

        $problematicas = DB::table('problematicas as pr')
            ->leftJoin('persona_problematica as pp', 'pp.problematica_id', '=', 'pr.id')
            ->leftJoin('personas as pe', function ($join) use ($rango) {
                $join->on('pe.id', '=', 'pp.persona_id')
                    ->whereNotNull('pe.numero_legajo')
                    ->whereNotNull('pe.fecha_apertura')
                    ->whereBetween('pe.fecha_apertura', [$rango['desde'], $rango['hasta']]);
            })
            ->whereNull('pr.deleted_at')
            ->select('pr.id', 'pr.nombre')
            ->selectRaw('COUNT(DISTINCT pe.id) as total')
            ->groupBy('pr.id', 'pr.nombre')
            ->orderByDesc('total')
            ->orderBy('pr.nombre')
            ->get()
            ->map(fn ($problematica) => [
                'id' => (int) $problematica->id,
                'nombre' => $problematica->nombre,
                'total' => (int) $problematica->total,
            ])
            ->values();

        $legajosPorPeriodo = $this->agruparFechas(
            (clone $legajosQuery)->pluck('fecha_apertura'),
            $rango['intervalos'],
        );
        $totalLegajos = (clone $legajosQuery)->count();

        return [
            'filtros' => [
                'periodo' => $periodo,
                'anio' => $anio,
                'mes' => $mes,
                'problematica' => $problematicaSeleccionada?->id,
            ],
            'rango' => [
                'desde' => $rango['desde'],
                'hasta' => $rango['hasta'],
                'descripcion' => $rango['descripcion'],
            ],
            'intervalos' => $rango['intervalos'],
            'kpis' => [
                'totalLegajos' => $totalLegajos,
                'legajosAbiertos' => (clone $legajosQuery)->where('estado', 'Abierto')->count(),
                'legajosCerrados' => (clone $legajosQuery)->where('estado', 'Cerrado')->count(),
                'problematicasConCasos' => $problematicas->where('total', '>', 0)->count(),
            ],
            'legajosPorEstado' => [
                'abiertos' => (clone $legajosQuery)->where('estado', 'Abierto')->count(),
                'cerrados' => (clone $legajosQuery)->where('estado', 'Cerrado')->count(),
                'archivados' => (clone $legajosQuery)->where('estado', 'Archivado')->count(),
            ],
            'legajosPorPeriodo' => $legajosPorPeriodo,
            'problematicas' => $problematicas,
            'problematicaSeleccionada' => $problematicaSeleccionada
                ? ['id' => $problematicaSeleccionada->id, 'nombre' => $problematicaSeleccionada->nombre]
                : null,
            'intervencionesDisponible' => false,
        ];
    }

    /**
     * Define the visible period and labels used by every aggregate.
     * Keeping this in PHP avoids database-specific date grouping functions.
     */
    private function rango(string $periodo, int $anio, int $mes, CarbonInterface $hoy): array
    {
        if ($periodo === 'dia') {
            $inicio = Carbon::create($anio, $mes, 1)->startOfDay();
            $finDelMes = $inicio->copy()->endOfMonth();
            $fin = $finDelMes->greaterThan($hoy) ? $hoy->copy()->endOfDay() : $finDelMes;
            $intervalos = [];

            for ($fecha = $inicio->copy(); $fecha->lessThanOrEqualTo($fin); $fecha->addDay()) {
                $intervalos[] = [
                    'key' => $fecha->toDateString(),
                    'label' => $fecha->format('d/m'),
                ];
            }

            return [
                'desde' => $inicio->toDateString(),
                'hasta' => $fin->toDateString(),
                'descripcion' => "Días de {$this->mesNombre($mes)} de {$anio}",
                'intervalos' => $intervalos,
            ];
        }

        if ($periodo === 'anio') {
            $inicio = $hoy->copy()->subYears(4)->startOfYear();
            $intervalos = [];

            for ($fecha = $inicio->copy(); $fecha->year <= $hoy->year; $fecha->addYear()) {
                $intervalos[] = [
                    'key' => $fecha->format('Y'),
                    'label' => $fecha->format('Y'),
                ];
            }

            return [
                'desde' => $inicio->toDateString(),
                'hasta' => $hoy->toDateString(),
                'descripcion' => 'Últimos cinco años',
                'intervalos' => $intervalos,
            ];
        }

        $inicio = Carbon::create($anio, 1, 1)->startOfDay();
        $finDelAnio = $inicio->copy()->endOfYear();
        $fin = $finDelAnio->greaterThan($hoy) ? $hoy->copy()->endOfDay() : $finDelAnio;
        $ultimoMes = (int) $fin->format('n');
        $intervalos = [];

        for ($mesActual = 1; $mesActual <= $ultimoMes; $mesActual++) {
            $intervalos[] = [
                'key' => sprintf('%04d-%02d', $anio, $mesActual),
                'label' => $this->mesNombre($mesActual, true),
            ];
        }

        return [
            'desde' => $inicio->toDateString(),
            'hasta' => $fin->toDateString(),
            'descripcion' => "Meses de {$anio}",
            'intervalos' => $intervalos,
        ];
    }

    private function agruparFechas($fechas, array $intervalos): array
    {
        if ($intervalos === []) {
            return [];
        }

        $totales = collect($intervalos)->mapWithKeys(fn (array $intervalo) => [$intervalo['key'] => 0]);

        foreach ($fechas as $fecha) {
            $fecha = Carbon::parse($fecha);
            $clave = match (strlen((string) $intervalos[0]['key'])) {
                4 => $fecha->format('Y'),
                7 => $fecha->format('Y-m'),
                default => $fecha->toDateString(),
            };

            if ($totales->has($clave)) {
                $totales->put($clave, $totales->get($clave) + 1);
            }
        }

        return $totales->values()->all();
    }

    private function mesNombre(int $mes, bool $corto = false): string
    {
        $nombres = [
            1 => ['enero', 'Ene'],
            2 => ['febrero', 'Feb'],
            3 => ['marzo', 'Mar'],
            4 => ['abril', 'Abr'],
            5 => ['mayo', 'May'],
            6 => ['junio', 'Jun'],
            7 => ['julio', 'Jul'],
            8 => ['agosto', 'Ago'],
            9 => ['septiembre', 'Sep'],
            10 => ['octubre', 'Oct'],
            11 => ['noviembre', 'Nov'],
            12 => ['diciembre', 'Dic'],
        ];

        return $nombres[$mes][$corto ? 1 : 0];
    }
}

<?php

namespace App\Services;

use App\Models\AuditoriaAccesoLegajo;
use App\Models\Auh;
use App\Models\CasoResponsable;
use App\Models\DerivacionCaso;
use App\Models\Intervencion;
use App\Models\Legajo;
use App\Models\MailMessage;
use App\Models\Tarea;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Estadísticas individuales de un legajo: actividad, circuito de derivaciones,
 * documentación y auditoría, más un resumen de puntos clave en lenguaje simple.
 */
class LegajoEstadisticasService
{
    /** @var array<int, string> */
    private const MESES = [1 => 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    /** @var array<string, string> */
    private const ESTADOS_DERIVACION = [
        'pendiente' => 'Pendientes',
        'aceptada' => 'Aceptadas',
        'rechazada' => 'Rechazadas',
        'cancelada' => 'Canceladas',
    ];

    /**
     * @return array<string, mixed>
     */
    public function calcular(Legajo $legajo, User $usuario): array
    {
        $hoy = CarbonImmutable::today();
        $intervenciones = $legajo->intervenciones()->with(['tipo', 'profesional'])->get();
        $derivaciones = $legajo->derivaciones()->with('destinatario')->latest()->get();
        $accesos = $legajo->accesos()->with('usuario')->get();

        $actividad = $this->actividad($legajo, $intervenciones, $hoy);
        $circuito = $this->circuito($legajo, $derivaciones, $hoy);
        $documentacion = $this->documentacion($legajo, $intervenciones, $usuario);
        $puedeVerDetalleAccesos = $usuario->tieneRol('admin', 'coordinador');
        $auditoria = $this->auditoria($accesos, $puedeVerDetalleAccesos);

        $ultima = $actividad['ultima'] ? CarbonImmutable::parse($actividad['ultima']) : null;
        $firmadas = $documentacion['firmas']['responsable'];

        $resumen = [
            'fecha_apertura' => $legajo->fecha_apertura?->toDateString(),
            'dias_abierto' => $legajo->fecha_apertura ? $this->dias($legajo->fecha_apertura, $hoy) : null,
            'intervenciones' => $intervenciones->count(),
            'intervenciones_ultimos_30_dias' => $intervenciones
                ->filter(fn (Intervencion $intervencion) => $intervencion->fecha_intervencion?->greaterThanOrEqualTo($hoy->subDays(30)))
                ->count(),
            'ultima_intervencion' => $ultima?->toDateString(),
            'dias_desde_ultima' => $ultima ? $this->dias($ultima, $hoy) : null,
            'derivaciones' => $derivaciones->count(),
            'derivaciones_pendientes' => $derivaciones->where('estado', 'pendiente')->count(),
            'firmas' => [
                'firmadas' => $firmadas,
                'total' => $intervenciones->count(),
                'porcentaje' => $intervenciones->isEmpty() ? null : (int) round($firmadas * 100 / $intervenciones->count()),
            ],
        ];

        return [
            'resumen' => $resumen,
            'puntos_clave' => $this->puntosClave($resumen, $actividad, $circuito, $documentacion, $auditoria),
            'actividad' => $actividad,
            'circuito' => $circuito,
            'documentacion' => $documentacion,
            'auditoria' => $auditoria,
        ];
    }

    /**
     * @param  Collection<int, Intervencion>  $intervenciones
     * @return array<string, mixed>
     */
    private function actividad(Legajo $legajo, Collection $intervenciones, CarbonImmutable $hoy): array
    {
        $fechas = $intervenciones
            ->map(fn (Intervencion $intervencion) => $intervencion->fecha_intervencion)
            ->filter()
            ->map(fn (CarbonInterface $fecha) => CarbonImmutable::parse($fecha)->startOfDay())
            ->sortBy(fn (CarbonImmutable $fecha) => $fecha->getTimestamp())
            ->values();

        $desde = collect([$legajo->fecha_apertura, $fechas->first()])
            ->filter()
            ->map(fn (CarbonInterface $fecha) => CarbonImmutable::parse($fecha)->startOfMonth())
            ->sortBy(fn (CarbonImmutable $fecha) => $fecha->getTimestamp())
            ->first() ?? $hoy->startOfMonth();

        $porMes = $fechas->countBy(fn (CarbonImmutable $fecha) => $fecha->format('Y-m'));
        $intervalos = [];
        $valores = [];

        for ($mes = $desde; $mes->lessThanOrEqualTo($hoy->startOfMonth()); $mes = $mes->addMonth()) {
            $intervalos[] = ['key' => $mes->format('Y-m'), 'label' => self::MESES[$mes->month].' '.$mes->format('y')];
            $valores[] = $porMes->get($mes->format('Y-m'), 0);
        }

        $separaciones = $fechas->sliding(2)
            ->map(fn (Collection $par) => $this->dias($par->first(), $par->last()));
        $tareas = Tarea::query()->where('legajo_id', $legajo->id)->get(['estado']);

        return [
            'por_mes' => ['intervalos' => $intervalos, 'valores' => $valores],
            'por_tipo' => $this->agrupar($intervenciones, fn (Intervencion $intervencion) => $intervencion->tipo?->nombre ?: 'Sin tipo'),
            'por_profesional' => $this->agrupar($intervenciones, fn (Intervencion $intervencion) => $intervencion->profesional?->name ?: 'Sin profesional'),
            'promedio_mensual' => count($valores) > 0 ? round($intervenciones->count() / count($valores), 1) : null,
            'dias_promedio_entre' => $separaciones->isEmpty() ? null : (int) round($separaciones->avg()),
            'primera' => $fechas->first()?->toDateString(),
            'ultima' => $fechas->last()?->toDateString(),
            'tareas' => [
                'total' => $tareas->count(),
                'pendientes' => $tareas->whereIn('estado', ['pendiente', 'en_progreso'])->count(),
                'completadas' => $tareas->where('estado', 'completada')->count(),
                'canceladas' => $tareas->where('estado', 'cancelada')->count(),
            ],
        ];
    }

    /**
     * @param  Collection<int, DerivacionCaso>  $derivaciones
     * @return array<string, mixed>
     */
    private function circuito(Legajo $legajo, Collection $derivaciones, CarbonImmutable $hoy): array
    {
        $respondidas = $derivaciones
            ->map(fn (DerivacionCaso $derivacion) => [$derivacion, $derivacion->aceptada_en ?? $derivacion->rechazada_en])
            ->filter(fn (array $par) => $par[1] !== null && $par[0]->created_at !== null);
        $pendientes = $derivaciones->where('estado', 'pendiente')->filter(fn (DerivacionCaso $derivacion) => $derivacion->created_at !== null);

        return [
            'por_estado' => collect(self::ESTADOS_DERIVACION)
                ->map(fn (string $label, string $estado) => ['nombre' => $label, 'total' => $derivaciones->where('estado', $estado)->count()])
                ->filter(fn (array $item) => $item['total'] > 0)
                ->values()
                ->all(),
            'dias_promedio_respuesta' => $respondidas->isEmpty()
                ? null
                : round($respondidas->avg(fn (array $par) => $par[0]->created_at->diffInHours($par[1]) / 24), 1),
            'pendiente_mas_antigua_dias' => $pendientes->isEmpty()
                ? null
                : $pendientes->map(fn (DerivacionCaso $derivacion) => $this->dias($derivacion->created_at, $hoy))->max(),
            'responsables' => $legajo->responsablesActivos()->with('usuario')->get()
                ->sortByDesc('es_principal')
                ->map(fn (CasoResponsable $responsable) => [
                    'nombre' => $responsable->usuario?->name ?? 'Usuario eliminado',
                    'principal' => (bool) $responsable->es_principal,
                    'desde' => $responsable->asignado_en ? CarbonImmutable::parse($responsable->asignado_en)->toDateString() : null,
                ])
                ->values()
                ->all(),
            'ultimas' => $derivaciones->take(5)->map(function (DerivacionCaso $derivacion) {
                $respuesta = $derivacion->aceptada_en ?? $derivacion->rechazada_en;

                return [
                    'id' => $derivacion->id,
                    'fecha' => $derivacion->created_at?->toDateString(),
                    'destinatario' => $derivacion->destinatario?->name ?? 'Sin destinatario',
                    'estado' => $derivacion->estado,
                    'dias_respuesta' => $respuesta && $derivacion->created_at
                        ? round($derivacion->created_at->diffInHours($respuesta) / 24, 1)
                        : null,
                ];
            })->values()->all(),
        ];
    }

    /**
     * @param  Collection<int, Intervencion>  $intervenciones
     * @return array<string, mixed>
     */
    private function documentacion(Legajo $legajo, Collection $intervenciones, User $usuario): array
    {
        $conFirmaResponsable = $intervenciones
            ->filter(fn (Intervencion $intervencion) => filled($intervencion->firma_archivo_path) || filled($intervencion->firma_nombre))
            ->count();
        $conFirmaEntrevistado = $intervenciones
            ->filter(fn (Intervencion $intervencion) => filled($intervencion->firma_entrevistado_archivo_path) || filled($intervencion->firma_entrevistado_nombre))
            ->count();

        $auh = null;

        if ($usuario->puedeVerSeccionLegajo('auh') && ($registro = $legajo->auhActual()->with('archivosActivos')->first())) {
            $tiposCargados = $registro->archivosActivos->pluck('tipo')->unique();
            $planillas = $registro->archivosActivos->whereIn('tipo', array_keys(Auh::PLANILLAS));

            $auh = [
                'cargados' => $tiposCargados->intersect(Auh::tiposObligatorios())->count(),
                'obligatorios' => count(Auh::tiposObligatorios()),
                'planillas_firmadas' => $planillas->where('firmado', true)->pluck('tipo')->unique()->count(),
                'planillas_total' => count(Auh::PLANILLAS),
            ];
        }

        return [
            'firmas' => [
                'responsable' => $conFirmaResponsable,
                'entrevistado' => $conFirmaEntrevistado,
                'sin_firma' => $intervenciones->count() - $conFirmaResponsable,
            ],
            'documentos' => $legajo->documentos()->count(),
            'mensajes' => MailMessage::query()->whereHas('legajos', fn ($query) => $query->whereKey($legajo->id))->count(),
            'auh' => $auh,
        ];
    }

    /**
     * @param  Collection<int, AuditoriaAccesoLegajo>  $accesos
     * @return array<string, mixed>
     */
    private function auditoria(Collection $accesos, bool $puedeVerDetalle): array
    {
        $ultimo = $accesos->pluck('fecha_acceso')->filter()
            ->sortByDesc(fn (CarbonInterface $fecha) => $fecha->getTimestamp())
            ->first();

        return [
            'total' => $accesos->count(),
            'visualizaciones' => $accesos->where('tipo_accion', 'Visualizacion')->count(),
            'descargas' => $accesos->where('tipo_accion', '!=', 'Visualizacion')->count(),
            'usuarios_distintos' => $accesos->pluck('usuario_id')->filter()->unique()->count(),
            'ultimo_acceso' => $ultimo ? CarbonImmutable::parse($ultimo)->toIso8601String() : null,
            'puede_ver_detalle' => $puedeVerDetalle,
            'por_usuario' => $puedeVerDetalle
                ? $this->agrupar($accesos, fn (AuditoriaAccesoLegajo $acceso) => $acceso->usuario?->name ?? 'Usuario eliminado')
                : [],
        ];
    }

    /**
     * Frases cortas y concretas con lo más importante del legajo.
     *
     * @param  array<string, mixed>  $resumen
     * @param  array<string, mixed>  $actividad
     * @param  array<string, mixed>  $circuito
     * @param  array<string, mixed>  $documentacion
     * @param  array<string, mixed>  $auditoria
     * @return array<int, array{tema: string, texto: string}>
     */
    private function puntosClave(array $resumen, array $actividad, array $circuito, array $documentacion, array $auditoria): array
    {
        $puntos = [];
        $total = $resumen['intervenciones'];

        if ($total === 0) {
            $puntos[] = ['tema' => 'actividad', 'texto' => 'Todavía no hay intervenciones registradas en este legajo.'];
        } else {
            $puntos[] = ['tema' => 'actividad', 'texto' => sprintf(
                'Se %s %s desde la apertura (%s por mes en promedio).',
                $total === 1 ? 'registró' : 'registraron',
                $this->plural($total, 'intervención', 'intervenciones'),
                $this->numero($actividad['promedio_mensual']),
            )];
            $puntos[] = ['tema' => 'actividad', 'texto' => sprintf(
                'La última intervención fue el %s (%s).',
                CarbonImmutable::parse($resumen['ultima_intervencion'])->format('d/m/Y'),
                $this->hace($resumen['dias_desde_ultima']),
            )];

            $tipo = $actividad['por_tipo'][0];
            $puntos[] = ['tema' => 'actividad', 'texto' => sprintf('El tipo más frecuente es «%s» (%d de %d).', $tipo['nombre'], $tipo['total'], $total)];

            $profesionales = count($actividad['por_profesional']);
            $principal = $actividad['por_profesional'][0];
            $puntos[] = ['tema' => 'actividad', 'texto' => $profesionales === 1
                ? sprintf('Todas las intervenciones las registró %s.', $principal['nombre'])
                : sprintf('Intervinieron %d profesionales; quien más intervino es %s (%d).', $profesionales, $principal['nombre'], $principal['total'])];
        }

        if ($resumen['derivaciones_pendientes'] > 0) {
            $pendientes = $resumen['derivaciones_pendientes'];
            $puntos[] = ['tema' => 'circuito', 'texto' => sprintf(
                '%s %s respuesta; la más antigua lleva %s.',
                $this->plural($pendientes, 'derivación', 'derivaciones'),
                $pendientes === 1 ? 'espera' : 'esperan',
                $this->plural($circuito['pendiente_mas_antigua_dias'], 'día', 'días'),
            )];
        } elseif ($resumen['derivaciones'] > 0 && $circuito['dias_promedio_respuesta'] !== null) {
            $puntos[] = ['tema' => 'circuito', 'texto' => sprintf(
                'Las derivaciones se respondieron en %s días en promedio.',
                $this->numero($circuito['dias_promedio_respuesta']),
            )];
        }

        if ($total > 0) {
            $sinFirma = $documentacion['firmas']['sin_firma'];
            $puntos[] = ['tema' => 'documentacion', 'texto' => $sinFirma === 0
                ? 'Todas las intervenciones tienen la firma del responsable.'
                : sprintf('%s sin la firma del responsable.', $this->plural($sinFirma, 'intervención está', 'intervenciones están'))];
        }

        if ($documentacion['auh'] !== null && $documentacion['auh']['cargados'] < $documentacion['auh']['obligatorios']) {
            $auh = $documentacion['auh'];
            $puntos[] = ['tema' => 'documentacion', 'texto' => sprintf(
                'AUH: faltan %d de %d documentos obligatorios.',
                $auh['obligatorios'] - $auh['cargados'],
                $auh['obligatorios'],
            )];
        }

        if ($auditoria['total'] > 0) {
            $puntos[] = ['tema' => 'auditoria', 'texto' => sprintf(
                'El legajo fue consultado %s por %s; último acceso el %s.',
                $this->plural($auditoria['total'], 'vez', 'veces'),
                $this->plural($auditoria['usuarios_distintos'], 'usuario', 'usuarios'),
                CarbonImmutable::parse($auditoria['ultimo_acceso'])->format('d/m/Y'),
            )];
        }

        return $puntos;
    }

    /**
     * @template T
     *
     * @param  Collection<int, T>  $items
     * @param  callable(T): string  $clave
     * @return array<int, array{nombre: string, total: int}>
     */
    private function agrupar(Collection $items, callable $clave): array
    {
        return $items->groupBy($clave)
            ->map(fn (Collection $grupo, string $nombre) => ['nombre' => $nombre, 'total' => $grupo->count()])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    private function dias(CarbonInterface $desde, CarbonInterface $hasta): int
    {
        return (int) abs(CarbonImmutable::parse($desde)->startOfDay()->diffInDays(CarbonImmutable::parse($hasta)->startOfDay()));
    }

    private function hace(int $dias): string
    {
        return match ($dias) {
            0 => 'hoy',
            1 => 'ayer',
            default => "hace {$dias} días",
        };
    }

    private function plural(int $cantidad, string $singular, string $plural): string
    {
        return $cantidad.' '.($cantidad === 1 ? $singular : $plural);
    }

    private function numero(float|int|null $valor): string
    {
        return number_format((float) $valor, (float) $valor === floor((float) $valor) ? 0 : 1, ',', '.');
    }
}

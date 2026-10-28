<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdateLegajoRequest;
use App\Models\Intervencion;
use App\Models\Legajo;
use App\Models\MailMessage;
use App\Models\MotivoIngreso;
use App\Models\Persona;
use App\Models\Problematica;
use App\Models\Provincia;
use App\Models\Tarea;
use App\Models\User;
use App\Models\VinculoFamiliar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class LegajoController extends Controller
{
    private const NIVELES_RIESGO = ['Bajo', 'Medio', 'Alto', 'Critico'];

    private const RANGOS_EDAD = [
        '0-5' => ['label' => '0 a 5 años', 'min' => 0, 'max' => 5],
        '6-12' => ['label' => '6 a 12 años', 'min' => 6, 'max' => 12],
        '13-17' => ['label' => '13 a 17 años', 'min' => 13, 'max' => 17],
        '18-21' => ['label' => '18 a 21 años', 'min' => 18, 'max' => 21],
        '22+' => ['label' => '22 años o más', 'min' => 22, 'max' => null],
    ];

    public function index(Request $request): Response
    {
        return $this->listadoPorEdad($request, false);
    }

    public function mayores18(Request $request): Response
    {
        return $this->listadoPorEdad($request, true);
    }

    private function listadoPorEdad(Request $request, bool $mayores18): Response
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $digitos = preg_replace('/\D/', '', $buscar);
        $nivel = in_array($request->query('nivel_riesgo'), self::NIVELES_RIESGO, true)
            ? $request->query('nivel_riesgo')
            : null;
        $estado = $request->query('estado') ?: null;
        $numeroLegajo = trim((string) $request->query('numero_legajo', ''));
        $problematicaId = $request->integer('problematica');
        $problematicaId = $problematicaId > 0 ? $problematicaId : null;
        $edad = (string) $request->query('edad', '');
        $edad = array_key_exists($edad, self::RANGOS_EDAD) ? $edad : null;
        $fechaLimite = now()->subYears(18)->toDateString();
        $operadorEdad = $mayores18 ? '<=' : '>';
        $baseQuery = Legajo::query()
            ->whereHas('persona', fn ($persona) => $persona->where('activo', true)
                ->whereNotNull('fecha_nacimiento')
                ->whereDate('fecha_nacimiento', $operadorEdad, $fechaLimite));

        $legajos = (clone $baseQuery)
            ->with(['persona', 'problematicas', 'profesional'])
            ->when($numeroLegajo !== '', fn ($query) => $query->where('numero_legajo', 'like', "%{$numeroLegajo}%"))
            ->when($problematicaId, fn ($query) => $query->whereHas('problematicas', fn ($problematica) => $problematica->whereKey($problematicaId)))
            ->when($edad, fn ($query) => $query->whereHas('persona', fn ($persona) => $this->aplicarRangoEdad($persona, $edad)))
            ->when($buscar !== '', function ($query) use ($buscar, $digitos) {
                $query->where(function ($query) use ($buscar, $digitos) {
                    $query->where('numero_legajo', 'like', "%{$buscar}%")
                        ->orWhereHas('persona', fn ($persona) => $persona->where('nombre', 'like', "%{$buscar}%")
                            ->orWhere('apellido', 'like', "%{$buscar}%"))
                        ->orWhereHas('problematicas', fn ($problematica) => $problematica->where('nombre', 'like', "%{$buscar}%"));

                    if ($digitos !== '') {
                        $query->orWhereHas('persona', fn ($persona) => $persona->where('dni', 'like', "%{$digitos}%"));
                    }
                });
            })
            ->when($nivel, fn ($query, $value) => $query->where('nivel_riesgo', $value))
            ->when($estado, fn ($query, $value) => $query->where('estado', $value))
            ->latest('fecha_apertura')
            ->paginate(15)
            ->withQueryString();

        $legajos->through(fn (Legajo $legajo) => $this->filaLegajo($legajo));

        return Inertia::render('legajos/index', [
            'legajos' => $legajos,
            'seccion' => $mayores18 ? 'mayores18' : 'legajos',
            'filtros' => [
                'buscar' => $buscar !== '' ? $buscar : null,
                'numero_legajo' => $numeroLegajo !== '' ? $numeroLegajo : null,
                'problematica' => $problematicaId,
                'edad' => $edad,
                'nivel_riesgo' => $nivel,
                'estado' => $estado,
            ],
            'estados' => (clone $baseQuery)->distinct()->orderBy('estado')->pluck('estado'),
            'problematicas' => Problematica::orderBy('nombre')->get(['id', 'nombre']),
            'edades' => collect(self::RANGOS_EDAD)
                ->map(fn (array $rango, string $valor) => [
                    'value' => $valor,
                    'label' => $rango['label'],
                ])
                ->values()
                ->all(),
            'status' => session('status'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('personas/create', [
            'modoLegajo' => true,
            'provincias' => Provincia::orderBy('nombre')->get(['id', 'nombre']),
            'problematicas' => Problematica::orderBy('nombre')->get(['id', 'nombre']),
            'motivosIngreso' => MotivoIngreso::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'profesionales' => $this->profesionalesActivos(),
        ]);
    }

    public function store(StorePersonaRequest $request)
    {
        $datos = $request->validated();

        try {
            $legajo = DB::transaction(function () use ($datos) {
                $persona = Persona::create($this->datosPersonales($datos));
                $legajo = Legajo::crearConNumeroUnico($persona, $this->datosLegajo($datos));
                $legajo->problematicas()->sync($datos['legajo_problematicas']);

                return $legajo->fresh();
            });
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'general' => config('app.debug')
                    ? 'No se pudo guardar: '.$e->getMessage()
                    : 'No se pudo crear el legajo. Intentá nuevamente; si el problema continúa, avisá al administrador.',
            ]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'title' => 'Legajo creado',
            'message' => "El legajo {$legajo->numero_legajo} quedó abierto y listo para trabajar.",
        ]);

        return redirect()->route('legajos.index');
    }

    public function estadisticas(Legajo $legajo): Response
    {
        $legajo->load(['persona', 'intervenciones.tipo', 'derivaciones']);

        return Inertia::render('legajos/estadisticas', [
            'legajo' => [
                'id' => $legajo->id,
                'numero_legajo' => $legajo->numero_legajo,
                'nombre' => $legajo->persona ? "{$legajo->persona->apellido}, {$legajo->persona->nombre}" : null,
                'estado' => $legajo->estado,
            ],
            'estadisticas' => $this->estadisticasDelLegajo($legajo),
        ]);
    }

    public function show(Request $request, Legajo $legajo): Response
    {
        $legajo->load([
            'persona',
            'problematicas',
            'profesional',
            'motivoIngreso',
            'intervenciones.tipo',
            'intervenciones.profesional',
            'intervenciones.creadoPor',
            'derivaciones.destinatario',
            'derivaciones.solicitante',
            'responsablesActivos.usuario',
        ]);
        $puedeVerVinculos = $request->user()->tieneRol('admin', 'coordinador', 'profesional', 'administrativo');
        $puedeGestionarDerivaciones = $request->user()->tieneRol(
            'admin',
            'operador',
            'oficios',
            'coordinador',
            'profesional',
            'administrativo',
        );
        $vinculos = [];
        $personasDisponibles = [];

        if ($puedeVerVinculos) {
            $vinculos = VinculoFamiliar::query()
                ->with(['grupo', 'persona.legajos', 'personaRelacionada.legajos'])
                ->whereNull('desvinculado_en')
                ->where(function ($query) use ($legajo) {
                    $query->where('persona_id', $legajo->persona_id)
                        ->orWhere('persona_relacionada_id', $legajo->persona_id);
                })
                ->latest()
                ->get()
                ->map(function (VinculoFamiliar $vinculo) use ($legajo) {
                    $otraPersona = $vinculo->persona_id === $legajo->persona_id
                        ? $vinculo->personaRelacionada
                        : $vinculo->persona;
                    $otroLegajo = $otraPersona?->legajos->first();

                    return [
                        'id' => $vinculo->id,
                        'tipo' => $vinculo->tipo_vinculo,
                        'grupo' => $vinculo->grupo?->nombre,
                        'persona' => $otroLegajo ? [
                            'id' => $otroLegajo->id,
                            'nombre' => "{$otraPersona->apellido}, {$otraPersona->nombre}",
                            'numero_legajo' => $otroLegajo->numero_legajo,
                        ] : null,
                    ];
                })
                ->filter(fn (array $vinculo) => $vinculo['persona'] !== null)
                ->values()
                ->all();

            $personasDisponibles = Persona::query()
                ->where('activo', true)
                ->whereHas('legajos')
                ->where('id', '!=', $legajo->persona_id)
                ->with('legajos')
                ->orderBy('apellido')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'apellido'])
                ->map(fn (Persona $persona) => [
                    'id' => $persona->id,
                    'nombre' => "{$persona->apellido}, {$persona->nombre}",
                    'numero_legajo' => $persona->legajos->first()?->numero_legajo,
                ])
                ->values()
                ->all();
        }

        return Inertia::render('legajos/show', [
            'legajo' => [
                'id' => $legajo->id,
                'numero_legajo' => $legajo->numero_legajo,
                'motivo_ingreso' => $legajo->motivo_ingreso,
                'motivo_ingreso_id' => $legajo->motivo_ingreso_id,
                'motivo_ingreso_nombre' => $legajo->motivoIngreso?->nombre,
                'nivel_riesgo' => $legajo->nivel_riesgo,
                'estado' => $legajo->estado,
                'origen_derivacion' => $legajo->origen_derivacion,
                'observaciones' => $legajo->observaciones,
                'fecha_apertura' => $legajo->fecha_apertura?->format('d/m/Y'),
                'profesional' => $legajo->profesional?->name,
                'problematicas' => $legajo->problematicas->pluck('nombre')->values(),
                'persona' => [
                    'id' => $legajo->persona->id,
                    'nombre_completo' => "{$legajo->persona->apellido}, {$legajo->persona->nombre}",
                    'dni' => $legajo->persona->dni,
                    'edad' => $legajo->persona->fecha_nacimiento?->age,
                ],
            ],
            'status' => session('status'),
            'usuarioActualId' => $request->user()->id,
            'puedeGestionarIntervenciones' => $request->user()->tieneRol('admin', 'operador'),
            'puedeGestionarDerivaciones' => $puedeGestionarDerivaciones,
            'responsablesDisponibles' => $puedeGestionarDerivaciones ? $this->responsablesActivos() : [],
            'puedeVerVinculos' => $puedeVerVinculos,
            'vinculos' => $vinculos,
            'personasDisponibles' => $personasDisponibles,
            'intervenciones' => $legajo->intervenciones->map(fn (Intervencion $intervencion) => [
                'id' => $intervencion->id,
                'fecha' => $intervencion->fecha_intervencion?->format('d/m/Y'),
                'hora' => $intervencion->hora_intervencion,
                'tipo' => $intervencion->tipo?->nombre,
                'detalle' => $intervencion->detalle,
                'profesional' => $intervencion->profesional?->name,
                'creado_por' => $intervencion->creadoPor?->name,
                'firma_nombre' => $intervencion->firma_nombre,
                'firma_aclaracion' => $intervencion->firma_aclaracion,
                'tiene_firma_responsable' => filled($intervencion->firma_archivo_path),
                'firma_entrevistado_nombre' => $intervencion->firma_entrevistado_nombre,
                'firma_entrevistado_aclaracion' => $intervencion->firma_entrevistado_aclaracion,
                'tiene_firma_entrevistado' => filled($intervencion->firma_entrevistado_archivo_path),
            ])->values(),
            'responsables' => $legajo->responsablesActivos->map(fn ($responsable) => [
                'id' => $responsable->id,
                'usuario_id' => $responsable->usuario_id,
                'nombre' => $responsable->usuario?->name,
                'es_principal' => $responsable->es_principal,
            ])->values(),
            'derivaciones' => $legajo->derivaciones->map(fn ($derivacion) => [
                'id' => $derivacion->id,
                'estado' => $derivacion->estado,
                'modo' => $derivacion->modo,
                'motivo' => $derivacion->motivo,
                'detalle' => $derivacion->detalle,
                'fecha_limite' => $derivacion->fecha_limite?->toDateString(),
                'destinatario_id' => $derivacion->destinatario_id,
                'destinatario' => $derivacion->destinatario?->name,
                'solicitante' => $derivacion->solicitante?->name,
                'motivo_rechazo' => $derivacion->motivo_rechazo,
            ])->values(),
            'estadisticas' => $this->estadisticasDelLegajo($legajo),
        ]);
    }

    public function edit(Legajo $legajo): Response
    {
        $legajo->load(['persona', 'problematicas']);

        return Inertia::render('legajos/edit', [
            'legajo' => [
                'id' => $legajo->id,
                'numero_legajo' => $legajo->numero_legajo,
                'persona' => $legajo->persona ? "{$legajo->persona->apellido}, {$legajo->persona->nombre}" : null,
                'motivo_ingreso' => $legajo->motivo_ingreso,
                'motivo_ingreso_id' => $legajo->motivo_ingreso_id,
                'nivel_riesgo' => $legajo->nivel_riesgo,
                'origen_derivacion' => $legajo->origen_derivacion,
                'profesional_id' => $legajo->profesional_id,
                'observaciones' => $legajo->observaciones,
                'problematicas' => $legajo->problematicas->pluck('id')->values(),
            ],
            'problematicas' => Problematica::orderBy('nombre')->get(['id', 'nombre']),
            'motivosIngreso' => MotivoIngreso::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'profesionales' => $this->profesionalesActivos(),
        ]);
    }

    public function update(UpdateLegajoRequest $request, Legajo $legajo)
    {
        $datos = $request->validated();

        try {
            DB::transaction(function () use ($legajo, $datos) {
                $legajo->update([
                    'motivo_ingreso' => $datos['motivo_ingreso'],
                    'motivo_ingreso_id' => $datos['motivo_ingreso_id'] ?? null,
                    'nivel_riesgo' => $datos['nivel_riesgo'],
                    'origen_derivacion' => $datos['origen_derivacion'],
                    'profesional_id' => $datos['profesional_id'] ?? null,
                    'observaciones' => $datos['observaciones'] ?? null,
                ]);

                $legajo->problematicas()->sync($datos['problematicas']);
            });
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'general' => config('app.debug')
                    ? 'No se pudo guardar: '.$e->getMessage()
                    : 'No se pudieron guardar los cambios. Intentá nuevamente; si el problema continúa, avisá al administrador.',
            ]);
        }

        return redirect()->route('legajos.show', $legajo)
            ->with('status', 'El legajo se actualizó correctamente.');
    }

    public function cerrar(Legajo $legajo)
    {
        return $this->cambiarEstado($legajo, 'Cerrado', "Legajo {$legajo->numero_legajo} cerrado.");
    }

    public function reabrir(Legajo $legajo)
    {
        return $this->cambiarEstado($legajo, 'Abierto', "Legajo {$legajo->numero_legajo} reabierto.");
    }

    private function cambiarEstado(Legajo $legajo, string $estado, string $mensaje)
    {
        try {
            $legajo->update([
                'estado' => $estado,
                'fecha_cierre' => $estado === 'Cerrado' ? today() : null,
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['general' => 'No se pudo cambiar el estado del legajo.']);
        }

        return back()->with('status', $mensaje);
    }

    private function filaLegajo(Legajo $legajo): array
    {
        return [
            'id' => $legajo->id,
            'numero_legajo' => $legajo->numero_legajo,
            'edad' => $legajo->persona?->fecha_nacimiento?->age,
            'persona' => [
                'id' => $legajo->persona?->id,
                'nombre_completo' => $legajo->persona ? "{$legajo->persona->apellido}, {$legajo->persona->nombre}" : null,
                'dni' => $legajo->persona?->dni,
            ],
            'problematicas' => $legajo->problematicas->pluck('nombre')->values(),
            'nivel_riesgo' => $legajo->nivel_riesgo,
            'estado' => $legajo->estado,
            'origen_derivacion' => $legajo->origen_derivacion,
            'profesional' => $legajo->profesional?->name,
            'fecha_apertura' => $legajo->fecha_apertura?->format('d/m/Y'),
        ];
    }

    private function aplicarRangoEdad($query, string $edad)
    {
        $rango = self::RANGOS_EDAD[$edad];
        $hoy = now();

        $query->whereDate(
            'fecha_nacimiento',
            '<=',
            $hoy->copy()->subYears($rango['min'])->toDateString(),
        );

        if ($rango['max'] !== null) {
            $query->whereDate(
                'fecha_nacimiento',
                '>',
                $hoy->copy()->subYears($rango['max'] + 1)->toDateString(),
            );
        }

        return $query;
    }

    private function datosPersonales(array $datos): array
    {
        return [
            'nombre' => $datos['nombre'],
            'apellido' => $datos['apellido'],
            'dni' => $datos['dni'],
            'fecha_nacimiento' => $datos['fecha_nacimiento'],
            'genero' => $datos['genero'],
            'direccion_calle' => $datos['direccion_calle'] ?? null,
            'direccion_numero' => $datos['direccion_numero'] ?? null,
            'direccion_piso_depto' => $datos['direccion_piso_depto'] ?? null,
            'localidad_id' => $datos['localidad_id'],
            'barrio_id' => $datos['barrio_id'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'email' => $datos['email'] ?? null,
            'observaciones' => $datos['observaciones'] ?? null,
        ];
    }

    private function datosLegajo(array $datos): array
    {
        return [
            'motivo_ingreso' => $datos['legajo_motivo_ingreso'],
            'motivo_ingreso_id' => $datos['legajo_motivo_ingreso_id'] ?? null,
            'nivel_riesgo' => $datos['legajo_nivel_riesgo'],
            'origen_derivacion' => $datos['legajo_origen_derivacion'],
            'profesional_id' => $datos['legajo_profesional_id'] ?? null,
            'estado' => 'Abierto',
            'observaciones' => $datos['legajo_observaciones'] ?? null,
        ];
    }

    private function profesionalesActivos()
    {
        return User::where('activo', true)
            ->whereHas('rol', fn ($query) => $query->where('nombre', 'profesional'))
            ->orderBy('name')
            ->get(['id', 'name as nombre_usuario', 'email', 'profesion']);
    }

    private function responsablesActivos()
    {
        return User::query()
            ->with('rol')
            ->where('activo', true)
            ->whereHas('rol', fn ($query) => $query->whereIn('nombre', [
                'admin',
                'oficios',
                'coordinador',
                'profesional',
                'administrativo',
                'operador',
            ]))
            ->orderBy('name')
            ->get(['id', 'name', 'profesion', 'rol_id'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'profesion' => $user->profesion,
                'rol' => $user->rol?->nombre,
            ])
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    private function estadisticasDelLegajo(Legajo $legajo): array
    {
        $intervenciones = $legajo->intervenciones;
        $tareas = Tarea::query()->where('legajo_id', $legajo->id)->get(['estado']);
        $derivaciones = $legajo->derivaciones;

        return [
            'intervenciones' => [
                'total' => $intervenciones->count(),
                'firmadas' => $intervenciones->filter(fn (Intervencion $intervencion) => filled($intervencion->firma_archivo_path) || filled($intervencion->firma_nombre))->count(),
                'por_tipo' => $intervenciones->groupBy(fn (Intervencion $intervencion) => $intervencion->tipo?->nombre ?: 'Sin tipo')
                    ->map(fn ($items, string $nombre) => ['nombre' => $nombre, 'total' => $items->count()])
                    ->sortByDesc('total')
                    ->values(),
            ],
            'tareas' => [
                'total' => $tareas->count(),
                'pendientes' => $tareas->whereIn('estado', ['pendiente', 'en_progreso'])->count(),
                'completadas' => $tareas->where('estado', 'completada')->count(),
                'canceladas' => $tareas->where('estado', 'cancelada')->count(),
            ],
            'derivaciones' => [
                'total' => $derivaciones->count(),
                'pendientes' => $derivaciones->where('estado', 'pendiente')->count(),
                'aceptadas' => $derivaciones->where('estado', 'aceptada')->count(),
                'rechazadas' => $derivaciones->where('estado', 'rechazada')->count(),
                'canceladas' => $derivaciones->where('estado', 'cancelada')->count(),
            ],
            'documentos' => $legajo->documentos()->count(),
            'mensajes' => MailMessage::query()->whereHas('legajos', fn ($query) => $query->whereKey($legajo->id))->count(),
            'accesos' => $legajo->accesos()->count(),
        ];
    }
}

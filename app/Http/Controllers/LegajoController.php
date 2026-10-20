<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdateLegajoRequest;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\MotivoIngreso;
use App\Models\Problematica;
use App\Models\Provincia;
use App\Models\User;
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
        $baseQuery = Persona::query()
            ->where('activo', true)
            ->whereNotNull('numero_legajo')
            ->whereNotNull('fecha_nacimiento')
            ->whereDate('fecha_nacimiento', $operadorEdad, $fechaLimite);

        $legajos = (clone $baseQuery)
            ->with(['problematicas', 'profesional'])
            ->when($numeroLegajo !== '', fn ($query) => $query->where('numero_legajo', 'like', "%{$numeroLegajo}%"))
            ->when($problematicaId, fn ($query) => $query->whereHas('problematicas', fn ($problematica) => $problematica->whereKey($problematicaId)))
            ->when($edad, fn ($query) => $this->aplicarRangoEdad($query, $edad))
            ->when($buscar !== '', function ($query) use ($buscar, $digitos) {
                $query->where(function ($query) use ($buscar, $digitos) {
                    $query->where('numero_legajo', 'like', "%{$buscar}%")
                        ->orWhere('nombre', 'like', "%{$buscar}%")
                        ->orWhere('apellido', 'like', "%{$buscar}%")
                        ->orWhereHas('problematicas', fn ($problematica) => $problematica->where('nombre', 'like', "%{$buscar}%"));

                    if ($digitos !== '') {
                        $query->orWhere('dni', 'like', "%{$digitos}%");
                    }
                });
            })
            ->when($nivel, fn ($query, $value) => $query->where('nivel_riesgo', $value))
            ->when($estado, fn ($query, $value) => $query->where('estado', $value))
            ->latest('fecha_apertura')
            ->paginate(15)
            ->withQueryString();

        $legajos->through(fn (Persona $persona) => $this->filaLegajo($persona));

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
            $persona = DB::transaction(function () use ($datos) {
                $persona = Persona::create($this->datosPersonales($datos));
                $persona->update($this->datosLegajo($datos, $persona));
                $persona->problematicas()->sync($datos['legajo_problematicas']);
                $this->sincronizarLegajoReal($persona->fresh());

                return $persona->fresh('problematicas');
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
            'message' => "El legajo {$persona->numero_legajo} quedó abierto y listo para trabajar.",
        ]);

        return redirect()->route('legajos.index');
    }

    public function show(Request $request, Persona $legajo): Response
    {
        $legajo->load(['problematicas', 'profesional', 'motivoIngreso']);
        $legajoReal = $this->sincronizarLegajoReal($legajo);
        $legajoReal->load(['intervenciones.tipo', 'intervenciones.profesional', 'intervenciones.creadoPor']);

        return Inertia::render('legajos/show', [
            'legajo' => [
                'id' => $legajo->id,
                'intervenciones_legajo_id' => $legajoReal->id,
                'numero_legajo' => $legajo->numero_legajo,
                'motivo_ingreso' => $legajo->motivo_ingreso,
                'motivo_ingreso_id' => $legajo->motivo_ingreso_id,
                'motivo_ingreso_nombre' => $legajo->motivoIngreso?->nombre,
                'nivel_riesgo' => $legajo->nivel_riesgo,
                'estado' => $legajo->estado,
                'origen_derivacion' => $legajo->origen_derivacion,
                'observaciones' => $legajo->observaciones_legajo,
                'fecha_apertura' => $legajo->fecha_apertura?->format('d/m/Y'),
                'profesional' => $legajo->profesional?->name,
                'problematicas' => $legajo->problematicas->pluck('nombre')->values(),
                'persona' => [
                    'id' => $legajo->id,
                    'nombre_completo' => "{$legajo->apellido}, {$legajo->nombre}",
                    'dni' => $legajo->dni,
                    'edad' => $legajo->fecha_nacimiento?->age,
                ],
            ],
            'status' => session('status'),
            'puedeGestionarIntervenciones' => $request->user()->tieneRol('admin', 'operador'),
            'intervenciones' => $legajoReal->intervenciones->map(fn (\App\Models\Intervencion $intervencion) => [
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
        ]);
    }

    public function edit(Persona $legajo): Response
    {
        $legajo->load('problematicas');

        return Inertia::render('legajos/edit', [
            'legajo' => [
                'id' => $legajo->id,
                'numero_legajo' => $legajo->numero_legajo,
                'persona' => "{$legajo->apellido}, {$legajo->nombre}",
                'motivo_ingreso' => $legajo->motivo_ingreso,
                'motivo_ingreso_id' => $legajo->motivo_ingreso_id,
                'nivel_riesgo' => $legajo->nivel_riesgo,
                'origen_derivacion' => $legajo->origen_derivacion,
                'profesional_id' => $legajo->profesional_id,
                'observaciones' => $legajo->observaciones_legajo,
                'problematicas' => $legajo->problematicas->pluck('id')->values(),
            ],
            'problematicas' => Problematica::orderBy('nombre')->get(['id', 'nombre']),
            'motivosIngreso' => MotivoIngreso::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'profesionales' => $this->profesionalesActivos(),
        ]);
    }

    public function update(UpdateLegajoRequest $request, Persona $legajo)
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
                    'observaciones_legajo' => $datos['observaciones'] ?? null,
                ]);

                $legajo->problematicas()->sync($datos['problematicas']);
                $this->sincronizarLegajoReal($legajo->fresh());
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

    public function cerrar(Persona $legajo)
    {
        return $this->cambiarEstado($legajo, 'Cerrado', "Legajo {$legajo->numero_legajo} cerrado.");
    }

    public function reabrir(Persona $legajo)
    {
        return $this->cambiarEstado($legajo, 'Abierto', "Legajo {$legajo->numero_legajo} reabierto.");
    }

    private function cambiarEstado(Persona $legajo, string $estado, string $mensaje)
    {
        try {
            $legajo->update([
                'estado' => $estado,
                'fecha_cierre' => $estado === 'Cerrado' ? today() : null,
            ]);
            $this->sincronizarLegajoReal($legajo->fresh());
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['general' => 'No se pudo cambiar el estado del legajo.']);
        }

        return back()->with('status', $mensaje);
    }

    private function filaLegajo(Persona $persona): array
    {
        return [
            'id' => $persona->id,
            'numero_legajo' => $persona->numero_legajo,
            'edad' => $persona->fecha_nacimiento?->age,
            'persona' => [
                'id' => $persona->id,
                'nombre_completo' => "{$persona->apellido}, {$persona->nombre}",
                'dni' => $persona->dni,
            ],
            'problematicas' => $persona->problematicas->pluck('nombre')->values(),
            'nivel_riesgo' => $persona->nivel_riesgo,
            'estado' => $persona->estado,
            'origen_derivacion' => $persona->origen_derivacion,
            'profesional' => $persona->profesional?->name,
            'fecha_apertura' => $persona->fecha_apertura?->format('d/m/Y'),
        ];
    }

    private function sincronizarLegajoReal(Persona $persona): Legajo
    {
        $atributos = [
            'numero_legajo' => $persona->numero_legajo,
            'motivo_ingreso' => $persona->motivo_ingreso,
            'nivel_riesgo' => $persona->nivel_riesgo,
            'origen_derivacion' => $persona->origen_derivacion,
            'profesional_id' => $persona->profesional_id,
            'fecha_apertura' => $persona->fecha_apertura ?? now()->toDateString(),
            'fecha_cierre' => $persona->fecha_cierre,
            'estado' => $persona->estado ?: 'Abierto',
            'observaciones' => $persona->observaciones_legajo,
        ];

        $legajoReal = Legajo::where('persona_id', $persona->id)->first();

        if (! $legajoReal) {
            return Legajo::create($atributos + ['persona_id' => $persona->id]);
        }

        $legajoReal->update($atributos);

        return $legajoReal->fresh();
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

    private function datosLegajo(array $datos, Persona $persona): array
    {
        return [
            'numero_legajo' => Persona::generarNumeroLegajo($persona),
            'motivo_ingreso' => $datos['legajo_motivo_ingreso'],
            'motivo_ingreso_id' => $datos['legajo_motivo_ingreso_id'] ?? null,
            'nivel_riesgo' => $datos['legajo_nivel_riesgo'],
            'origen_derivacion' => $datos['legajo_origen_derivacion'],
            'profesional_id' => $datos['legajo_profesional_id'] ?? null,
            'fecha_apertura' => now()->toDateString(),
            'estado' => 'Abierto',
            'observaciones_legajo' => $datos['legajo_observaciones'] ?? null,
        ];
    }

    private function profesionalesActivos()
    {
        return User::where('activo', true)
            ->orderBy('name')
            ->get(['id', 'name as nombre_usuario', 'email']);
    }
}

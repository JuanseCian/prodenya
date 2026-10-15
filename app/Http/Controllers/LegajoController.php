<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdateLegajoRequest;
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

    public function index(Request $request): Response
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $digitos = preg_replace('/\D/', '', $buscar);
        $nivel = in_array($request->query('nivel_riesgo'), self::NIVELES_RIESGO, true)
            ? $request->query('nivel_riesgo')
            : null;
        $estado = $request->query('estado') ?: null;

        $legajos = Persona::with(['problematicas', 'profesional'])
            ->where('activo', true)
            ->whereNotNull('numero_legajo')
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
            'filtros' => [
                'buscar' => $buscar !== '' ? $buscar : null,
                'nivel_riesgo' => $nivel,
                'estado' => $estado,
            ],
            'estados' => Persona::whereNotNull('numero_legajo')->distinct()->orderBy('estado')->pluck('estado'),
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

    public function show(Persona $legajo): Response
    {
        $legajo->load(['problematicas', 'profesional', 'motivoIngreso']);

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
            $legajo->update(['estado' => $estado]);
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

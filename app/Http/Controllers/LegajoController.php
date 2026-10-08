<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLegajoRequest;
use App\Http\Requests\UpdateLegajoRequest;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\Problematica;
use App\Models\Usuario;
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

        $legajos = Legajo::with(['persona', 'problematicas'])
            ->when($buscar !== '', function ($consulta) use ($buscar, $digitos) {
                $consulta->where(function ($q) use ($buscar, $digitos) {
                    $q->where('numero_legajo', 'like', "%{$buscar}%")
                        ->orWhereHas('persona', function ($persona) use ($buscar, $digitos) {
                            $persona->where('nombre', 'like', "%{$buscar}%")
                                ->orWhere('apellido', 'like', "%{$buscar}%")
                                ->orWhereRaw("CONCAT(nombre, ' ', apellido) like ?", ["%{$buscar}%"])
                                ->orWhereRaw("CONCAT(apellido, ' ', nombre) like ?", ["%{$buscar}%"]);

                            if ($digitos !== '') {
                                $persona->orWhere('dni', 'like', "%{$digitos}%");
                            }
                        });
                });
            })
            ->when($nivel, fn ($q, $valor) => $q->where('nivel_riesgo', $valor))
            ->when($estado, fn ($q, $valor) => $q->where('estado', $valor))
            ->latest('creado_en')
            ->paginate(15)
            ->withQueryString();

        // Nombres de los profesionales de esta página (una sola consulta).
        $profesionales = Usuario::whereIn('id', $legajos->pluck('profesional_id')->filter())
            ->pluck('nombre_usuario', 'id');

        $legajos->through(fn (Legajo $legajo) => [
            'id' => $legajo->id,
            'numero_legajo' => $legajo->numero_legajo,
            'persona' => $legajo->persona ? [
                'id' => $legajo->persona->id,
                'nombre_completo' => "{$legajo->persona->apellido}, {$legajo->persona->nombre}",
                'dni' => $legajo->persona->dni,
            ] : null,
            'problematicas' => $legajo->problematicas->pluck('nombre')->values(),
            'nivel_riesgo' => $legajo->nivel_riesgo,
            'estado' => $legajo->estado,
            'origen_derivacion' => $legajo->origen_derivacion,
            'profesional' => $profesionales->get($legajo->profesional_id),
            'fecha_apertura' => ($legajo->fecha_apertura ?? $legajo->creado_en)?->format('d/m/Y'),
        ]);

        return Inertia::render('legajos/index', [
            'legajos' => $legajos,
            'filtros' => [
                'buscar' => $buscar !== '' ? $buscar : null,
                'nivel_riesgo' => $nivel,
                'estado' => $estado,
            ],
            'estados' => Legajo::query()->distinct()->orderBy('estado')->pluck('estado'),
            'status' => session('status'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('legajos/create', [
            'personas' => Persona::with(['localidad', 'barrio'])
                ->where('activo', true)
                ->orderBy('apellido')
                ->get()
                ->map(fn (Persona $persona) => [
                    'id' => $persona->id,
                    'nombre' => $persona->nombre,
                    'apellido' => $persona->apellido,
                    'dni' => $persona->dni,
                    'fechaNacimiento' => $persona->fecha_nacimiento->format('d/m/Y'),
                    'edad' => $persona->fecha_nacimiento->age,
                    'genero' => $persona->genero,
                    'direccion' => trim("{$persona->direccion_calle} {$persona->direccion_numero}"),
                    'localidad' => $persona->localidad?->nombre,
                    'barrio' => $persona->barrio?->nombre,
                    'telefono' => $persona->telefono,
                    'email' => $persona->email,
                ]),
            'problematicas' => Problematica::orderBy('nombre')->get(['id', 'nombre']),
            'profesionales' => Usuario::where('activo', true)
                ->orderBy('nombre_usuario')
                ->get(['id', 'nombre_usuario', 'email']),
        ]);
    }

    public function store(StoreLegajoRequest $request)
    {
        $datos = $request->validated();
        $persona = Persona::findOrFail($datos['persona_id']);

        $legajo = Legajo::create([
            'persona_id' => $persona->id,
            'numero_legajo' => Legajo::generarNumero($persona),
            'motivo_ingreso' => $datos['motivo_ingreso'],
            'nivel_riesgo' => $datos['nivel_riesgo'],
            'origen_derivacion' => $datos['origen_derivacion'],
            'profesional_id' => $datos['profesional_id'] ?? null,
            'fecha_apertura' => now()->toDateString(),
            'observaciones' => $datos['observaciones'] ?? null,
            'estado' => 'Abierto',
            'creado_por' => $request->user()->id,
        ]);

        $legajo->problematicas()->sync($datos['problematicas']);

        return redirect()->route('home')
            ->with('status', "Legajo {$legajo->numero_legajo} creado correctamente.");
    }

    public function show(Request $request, Legajo $legajo): Response
    {
        $legajo->load(['persona', 'problematicas']);

        $this->registrarAcceso($request, $legajo, 'VISTA');

        return Inertia::render('legajos/show', [
            'legajo' => [
                'id' => $legajo->id,
                'numero_legajo' => $legajo->numero_legajo,
                'motivo_ingreso' => $legajo->motivo_ingreso,
                'nivel_riesgo' => $legajo->nivel_riesgo,
                'estado' => $legajo->estado,
                'origen_derivacion' => $legajo->origen_derivacion,
                'observaciones' => $legajo->observaciones,
                'fecha_apertura' => ($legajo->fecha_apertura ?? $legajo->creado_en)?->format('d/m/Y'),
                'profesional' => $legajo->profesional_id
                    ? Usuario::find($legajo->profesional_id)?->nombre_usuario
                    : null,
                'problematicas' => $legajo->problematicas->pluck('nombre')->values(),
                'persona' => $legajo->persona ? [
                    'id' => $legajo->persona->id,
                    'nombre_completo' => "{$legajo->persona->apellido}, {$legajo->persona->nombre}",
                    'dni' => $legajo->persona->dni,
                    'edad' => $legajo->persona->fecha_nacimiento?->age,
                ] : null,
            ],
            'status' => session('status'),
        ]);
    }

    public function edit(Legajo $legajo): Response
    {
        $legajo->load(['persona', 'problematicas']);

        return Inertia::render('legajos/edit', [
            'legajo' => [
                'id' => $legajo->id,
                'numero_legajo' => $legajo->numero_legajo,
                'persona' => $legajo->persona
                    ? "{$legajo->persona->apellido}, {$legajo->persona->nombre}"
                    : null,
                'motivo_ingreso' => $legajo->motivo_ingreso,
                'nivel_riesgo' => $legajo->nivel_riesgo,
                'origen_derivacion' => $legajo->origen_derivacion,
                'profesional_id' => $legajo->profesional_id,
                'observaciones' => $legajo->observaciones,
                'problematicas' => $legajo->problematicas->pluck('id')->values(),
            ],
            'problematicas' => Problematica::orderBy('nombre')->get(['id', 'nombre']),
            'profesionales' => Usuario::where('activo', true)
                ->orderBy('nombre_usuario')
                ->get(['id', 'nombre_usuario', 'email']),
        ]);
    }

    public function update(UpdateLegajoRequest $request, Legajo $legajo)
    {
        $datos = $request->validated();

        try {
            DB::transaction(function () use ($legajo, $datos) {
                $legajo->update([
                    'motivo_ingreso' => $datos['motivo_ingreso'],
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
            $legajo->estado = $estado;
            $legajo->save();
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'general' => config('app.debug')
                    ? 'No se pudo cambiar el estado: '.$e->getMessage()
                    : 'No se pudo cambiar el estado del legajo.',
            ]);
        }

        return back()->with('status', $mensaje);
    }

    /**
     * Auditoría de accesos (ver Legajo::registrarAcceso). Nunca debe romper la pantalla.
     */
    private function registrarAcceso(Request $request, Legajo $legajo, string $tipoAccion): void
    {
        $usuario = $request->user();

        if (! $usuario instanceof Usuario) {
            return;
        }

        try {
            $legajo->registrarAcceso($usuario, $tipoAccion);
        } catch (Throwable $e) {
            report($e);
        }
    }
}

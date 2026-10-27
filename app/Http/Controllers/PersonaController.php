<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdatePersonaRequest;
use App\Models\MotivoIngreso;
use App\Models\Persona;
use App\Models\Problematica;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PersonaController extends Controller
{
    public function index(Request $request): Response
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $digitos = preg_replace('/\D/', '', $buscar);

        $personas = Persona::with(['localidad', 'barrio', 'problematicas'])
            ->where('activo', true)
            ->when($buscar !== '', function ($consulta) use ($buscar, $digitos) {
                $consulta->where(function ($q) use ($buscar, $digitos) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('apellido', 'like', "%{$buscar}%")
                        ->orWhere('numero_legajo', 'like', "%{$buscar}%")
                        ->orWhereRaw("CONCAT(nombre, ' ', apellido) like ?", ["%{$buscar}%"])
                        ->orWhereRaw("CONCAT(apellido, ' ', nombre) like ?", ["%{$buscar}%"]);

                    if ($digitos !== '') {
                        $q->orWhere('dni', 'like', "%{$digitos}%");
                    }

                    $q->orWhereHas('problematicas', fn ($problematica) => $problematica->where('nombre', 'like', "%{$buscar}%"));
                });
            })
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $personas->through(fn (Persona $persona) => [
            'id' => $persona->id,
            'nombre' => $persona->nombre,
            'apellido' => $persona->apellido,
            'dni' => $persona->dni,
            'edad' => $persona->fecha_nacimiento?->age,
            'genero' => $persona->genero,
            'localidad' => $persona->localidad?->nombre,
            'barrio' => $persona->barrio?->nombre,
            'telefono' => $persona->telefono,
            'email' => $persona->email,
            'legajos' => $persona->numero_legajo ? [$persona->numero_legajo] : [],
        ]);

        return Inertia::render('personas/index', [
            'personas' => $personas,
            'filtros' => ['buscar' => $buscar !== '' ? $buscar : null],
            'status' => session('status'),
        ]);
    }

    public function create(): Response
    {
        $profesionales = User::query()
            ->when(Schema::hasColumn('users', 'activo'), fn ($query) => $query->where('activo', true))
            ->whereHas('rol', fn ($query) => $query->where('nombre', 'profesional'))
            ->orderBy('name')
            ->get(['id', 'name as nombre_usuario', 'email', 'profesion']);

        return Inertia::render('personas/create', [
            'provincias' => Provincia::orderBy('nombre')->get(['id', 'nombre']),
            'problematicas' => Problematica::orderBy('nombre')->get(['id', 'nombre']),
            'motivosIngreso' => MotivoIngreso::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            // Se expone "name" con el alias "nombre_usuario" para no tocar el front.
            'profesionales' => $profesionales,
        ]);
    }

    public function store(StorePersonaRequest $request)
    {
        $datos = $request->validated();

        try {
            [$persona, $legajo] = DB::transaction(function () use ($datos, $request) {
                $persona = Persona::create([
                    'nombre' => $datos['nombre'],
                    'apellido' => $datos['apellido'],
                    'dni' => $datos['dni'] ?? null,
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
                ]);

                $legajo = null;

                if ($request->boolean('abrir_legajo')) {
                    $persona->update([
                        'numero_legajo' => Persona::generarNumeroLegajo($persona),
                        'motivo_ingreso' => $datos['legajo_motivo_ingreso'],
                        'motivo_ingreso_id' => $datos['legajo_motivo_ingreso_id'] ?? null,
                        'nivel_riesgo' => $datos['legajo_nivel_riesgo'],
                        'origen_derivacion' => $datos['legajo_origen_derivacion'],
                        'profesional_id' => $datos['legajo_profesional_id'] ?? null,
                        'fecha_apertura' => now()->toDateString(),
                        'estado' => 'Abierto',
                        'observaciones_legajo' => $datos['legajo_observaciones'] ?? null,
                    ]);

                    $persona->problematicas()->sync($datos['legajo_problematicas']);
                    $legajo = $persona->fresh();
                }

                return [$persona, $legajo];
            });
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'general' => config('app.debug')
                    ? 'No se pudo guardar: '.$e->getMessage()
                    : 'No se pudo guardar el registro. Intentá nuevamente; si el problema continúa, avisá al administrador.',
            ]);
        }

        $mensaje = "Persona \"{$persona->nombre} {$persona->apellido}\" registrada correctamente.";
        if ($legajo) {
            $mensaje .= " Legajo {$legajo->numero_legajo} abierto.";
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'title' => $legajo ? 'Persona y legajo creados' : 'Persona registrada',
            'message' => $mensaje,
        ]);

        return redirect()->route('personas.index');
    }

    public function show(Persona $persona): Response
    {
        $persona->load('problematicas');
        $legajos = $persona->numero_legajo ? [$this->datosLegajo($persona)] : [];

        return Inertia::render('personas/show', [
            'persona' => $this->datosPersona($persona),
            'legajos' => $legajos,
            'status' => session('status'),
        ]);
    }

    public function edit(Persona $persona): Response
    {
        return Inertia::render('personas/edit', [
            'persona' => $this->datosPersona($persona),
            'provincias' => Provincia::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function update(UpdatePersonaRequest $request, Persona $persona)
    {
        $datos = $request->validated();

        try {
            $persona->update([
                'nombre' => $datos['nombre'],
                'apellido' => $datos['apellido'],
                'dni' => $datos['dni'] ?? null,
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
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'general' => config('app.debug')
                    ? 'No se pudo guardar: '.$e->getMessage()
                    : 'No se pudieron guardar los cambios. Intentá nuevamente; si el problema continúa, avisá al administrador.',
            ]);
        }

        return redirect()->route('personas.show', $persona)
            ->with('status', 'Los datos de la persona se actualizaron correctamente.');
    }

    /**
     * Baja lógica: la persona deja de aparecer en los listados, pero no se borra.
     */
    public function destroy(Persona $persona)
    {
        $tieneLegajoAbierto = $persona->numero_legajo !== null && $persona->estado === 'Abierto';

        if ($tieneLegajoAbierto) {
            return back()->withErrors([
                'general' => 'No se puede dar de baja: la persona tiene legajos abiertos. Cerralos primero.',
            ]);
        }

        $persona->activo = false;
        $persona->save();

        return redirect()->route('personas.index')
            ->with('status', "Se dio de baja a {$persona->apellido}, {$persona->nombre}.");
    }

    private function datosPersona(Persona $persona): array
    {
        $persona->loadMissing(['localidad', 'barrio']);
        $provinciaId = $persona->localidad?->provincia_id;

        return [
            'id' => $persona->id,
            'nombre' => $persona->nombre,
            'apellido' => $persona->apellido,
            'dni' => $persona->dni,
            'fecha_nacimiento' => $persona->fecha_nacimiento?->format('Y-m-d'),
            'fecha_nacimiento_texto' => $persona->fecha_nacimiento?->format('d/m/Y'),
            'edad' => $persona->fecha_nacimiento?->age,
            'genero' => $persona->genero,
            'direccion_calle' => $persona->direccion_calle,
            'direccion_numero' => $persona->direccion_numero,
            'direccion_piso_depto' => $persona->direccion_piso_depto,
            'provincia_id' => $provinciaId,
            'provincia' => $provinciaId ? Provincia::find($provinciaId)?->nombre : null,
            'localidad_id' => $persona->localidad_id,
            'localidad' => $persona->localidad?->nombre,
            'barrio_id' => $persona->barrio_id,
            'barrio' => $persona->barrio?->nombre,
            'telefono' => $persona->telefono,
            'email' => $persona->email,
            'observaciones' => $persona->observaciones,
            'activo' => (bool) $persona->activo,
        ];
    }

    private function datosLegajo(Persona $persona): array
    {
        return [
            'id' => $persona->id,
            'numero_legajo' => $persona->numero_legajo,
            'estado' => $persona->estado,
            'nivel_riesgo' => $persona->nivel_riesgo,
            'fecha_apertura' => $persona->fecha_apertura?->format('d/m/Y'),
            'problematicas' => $persona->problematicas->pluck('nombre')->values(),
        ];
    }
}

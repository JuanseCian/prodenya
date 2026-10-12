<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdatePersonaRequest;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\Problematica;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PersonaController extends Controller
{
    public function index(Request $request): Response
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $digitos = preg_replace('/\D/', '', $buscar);

        $personas = Persona::with(['localidad', 'barrio'])
            ->where('activo', true)
            ->when($buscar !== '', function ($consulta) use ($buscar, $digitos) {
                $consulta->where(function ($q) use ($buscar, $digitos) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('apellido', 'like', "%{$buscar}%")
                        ->orWhereRaw("CONCAT(nombre, ' ', apellido) like ?", ["%{$buscar}%"])
                        ->orWhereRaw("CONCAT(apellido, ' ', nombre) like ?", ["%{$buscar}%"]);

                    if ($digitos !== '') {
                        $q->orWhere('dni', 'like', "%{$digitos}%");
                    }
                });
            })
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        // Números de legajo de las personas de esta página (una sola consulta).
        $legajos = Legajo::whereIn('persona_id', $personas->pluck('id'))
            ->orderBy('numero_legajo')
            ->get(['persona_id', 'numero_legajo'])
            ->groupBy('persona_id');

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
            'legajos' => $legajos->get($persona->id, collect())->pluck('numero_legajo')->values(),
        ]);

        return Inertia::render('personas/index', [
            'personas' => $personas,
            'filtros' => ['buscar' => $buscar !== '' ? $buscar : null],
            'status' => session('status'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('personas/create', [
            'provincias' => Provincia::orderBy('nombre')->get(['id', 'nombre']),
            'problematicas' => Problematica::orderBy('nombre')->get(['id', 'nombre']),
            // Se expone "name" con el alias "nombre_usuario" para no tocar el front.
            'profesionales' => User::where('activo', true)
                ->orderBy('name')
                ->get(['id', 'name as nombre_usuario', 'email']),
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
                    $legajo = Legajo::crearConNumeroUnico($persona, [
                        'motivo_ingreso' => $datos['legajo_motivo_ingreso'],
                        'nivel_riesgo' => $datos['legajo_nivel_riesgo'],
                        'origen_derivacion' => $datos['legajo_origen_derivacion'],
                        'profesional_id' => $datos['legajo_profesional_id'] ?? null,
                        'fecha_apertura' => now()->toDateString(),
                        'estado' => 'Abierto',
                        'observaciones' => $datos['legajo_observaciones'] ?? null,
                        'creado_por' => $request->user()->id,
                    ]);

                    $legajo->problematicas()->sync($datos['legajo_problematicas']);
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

        return redirect()->route('home')->with('status', $mensaje);
    }

    public function show(Persona $persona): Response
    {
        $legajos = Legajo::where('persona_id', $persona->id)
            ->with('problematicas')
            ->latest('creado_en')
            ->get()
            ->map(fn (Legajo $legajo) => [
                'id' => $legajo->id,
                'numero_legajo' => $legajo->numero_legajo,
                'estado' => $legajo->estado,
                'nivel_riesgo' => $legajo->nivel_riesgo,
                'fecha_apertura' => ($legajo->fecha_apertura ?? $legajo->creado_en)?->format('d/m/Y'),
                'problematicas' => $legajo->problematicas->pluck('nombre')->values(),
            ]);

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
        $tieneLegajosAbiertos = Legajo::where('persona_id', $persona->id)
            ->where('estado', 'Abierto')
            ->exists();

        if ($tieneLegajosAbiertos) {
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
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\Problematica;
use App\Models\Provincia;
use App\Models\Usuario;
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
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('personas/create', [
            'provincias' => Provincia::orderBy('nombre')->get(['id', 'nombre']),
            'problematicas' => Problematica::orderBy('nombre')->get(['id', 'nombre']),
            'profesionales' => Usuario::where('activo', true)
                ->orderBy('nombre_usuario')
                ->get(['id', 'nombre_usuario', 'email']),
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
                ]);

                $legajo = null;

                if ($request->boolean('abrir_legajo')) {
                    $legajo = Legajo::create([
                        'persona_id' => $persona->id,
                        'numero_legajo' => Legajo::generarNumero($persona),
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
}

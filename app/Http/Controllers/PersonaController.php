<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\Provincia;
use App\Models\TipoMaltrato;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PersonaController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('personas/create', [
            'provincias' => Provincia::orderBy('nombre')->get(['id', 'nombre']),
            'tiposMaltrato' => TipoMaltrato::orderBy('nombre')->get(['id', 'nombre']),
            'profesionales' => Usuario::where('activo', true)
                ->orderBy('nombre_usuario')
                ->get(['id', 'nombre_usuario', 'email']),
        ]);
    }

    public function store(StorePersonaRequest $request)
    {
        $datos = $request->validated();

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
                    'estado' => 'Abierto',
                    'observaciones' => $datos['legajo_observaciones'] ?? null,
                    'creado_por' => $request->user()->id,
                ]);

                $legajo->tiposMaltrato()->sync($datos['legajo_tipos_maltrato']);
            }

            return [$persona, $legajo];
        });

        $mensaje = "Persona \"{$persona->nombre} {$persona->apellido}\" registrada correctamente.";
        if ($legajo) {
            $mensaje .= " Legajo {$legajo->numero_legajo} abierto.";
        }

        return redirect()->route('dashboard')->with('status', $mensaje);
    }
}

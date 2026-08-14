<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\Provincia;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PersonaController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('personas/create', [
            'provincias' => Provincia::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(StorePersonaRequest $request)
    {
        $datos = $request->validated();

        $persona = DB::transaction(function () use ($datos, $request) {
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

            if ($request->boolean('abrir_legajo')) {
                Legajo::create([
                    'persona_id' => $persona->id,
                    'numero_legajo' => $datos['numero_legajo'],
                    'estado' => 'Abierto',
                    'observaciones' => $datos['legajo_observaciones'] ?? null,
                    'creado_por' => $request->user()->id,
                ]);
            }

            return $persona;
        });

        return redirect()->route('dashboard')
            ->with('status', "Persona \"{$persona->nombre} {$persona->apellido}\" registrada correctamente.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLegajoRequest;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\Problematica;
use App\Models\Usuario;
use Inertia\Inertia;
use Inertia\Response;

class LegajoController extends Controller
{
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
            'problematica' => $datos['problematica'],
            'nivel_riesgo' => $datos['nivel_riesgo'],
            'origen_derivacion' => $datos['origen_derivacion'],
            'profesional_id' => $datos['profesional_id'] ?? null,
            'observaciones' => $datos['observaciones'] ?? null,
            'estado' => 'Abierto',
            'creado_por' => $request->user()->id,
        ]);

        $legajo->problematicas()->sync($datos['problematicas']);

        return redirect()->route('home')
            ->with('status', "Legajo {$legajo->numero_legajo} creado correctamente.");
    }
}

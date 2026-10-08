<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\Problematica;
use App\Models\Provincia;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PersonaController extends Controller
{
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
            // Si algo falla, la transacción se revierte (no queda ni la persona
            // ni el legajo) y se muestra el motivo en el formulario.
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

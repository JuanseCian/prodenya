<?php

namespace App\Http\Controllers;

use App\Models\Legajo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FichaController extends Controller
{
    public function index(Request $request): Response
    {
        $usuario = $request->user();
        $puedeVerPersona = $usuario->puedeVerSeccionLegajo('persona');
        $puedeVerSeguimiento = $usuario->puedeVerSeccionLegajo('seguimiento');
        $puedeVerSituacion = $usuario->puedeVerSeccionLegajo('situacion');
        $buscar = trim((string) $request->query('buscar', ''));
        $legajoId = $request->integer('legajo');
        $digitos = preg_replace('/\D/', '', $buscar);

        $fichas = Legajo::query()
            ->with(['persona.localidad', 'persona.barrio', 'problematicas', 'profesional'])
            ->when($legajoId > 0, fn ($query) => $query->whereKey($legajoId))
            ->when($buscar !== '', function ($query) use ($buscar, $digitos, $puedeVerPersona, $puedeVerSituacion): void {
                $query->where(function ($query) use ($buscar, $digitos, $puedeVerPersona, $puedeVerSituacion): void {
                    $query->where('numero_legajo', 'like', "%{$buscar}%");

                    if ($puedeVerPersona) {
                        $query->orWhereHas('persona', function ($persona) use ($buscar, $digitos): void {
                            $persona->where('nombre', 'like', "%{$buscar}%")
                                ->orWhere('apellido', 'like', "%{$buscar}%");

                            if ($digitos !== '') {
                                $persona->orWhere('dni', 'like', "%{$digitos}%");
                            }
                        });
                    }

                    if ($puedeVerSituacion) {
                        $query->orWhereHas('problematicas', fn ($problematica) => $problematica->where('nombre', 'like', "%{$buscar}%"));
                    }
                });
            })
            ->latest('fecha_apertura')
            ->paginate(12)
            ->withQueryString();

        $fichas->through(function (Legajo $legajo) use ($puedeVerPersona, $puedeVerSeguimiento, $puedeVerSituacion): array {
            $persona = $legajo->persona;

            return [
                'id' => $legajo->id,
                'numero_legajo' => $legajo->numero_legajo,
                'nivel_riesgo' => $puedeVerSituacion ? $legajo->nivel_riesgo : null,
                'estado' => $puedeVerSeguimiento ? $legajo->estado : null,
                'fecha_apertura' => $puedeVerSeguimiento ? $legajo->fecha_apertura?->format('d/m/Y') : null,
                'profesional' => $puedeVerSeguimiento ? $legajo->profesional?->name : null,
                'origen_derivacion' => $puedeVerSeguimiento ? $legajo->origen_derivacion : null,
                'problematicas' => $puedeVerSituacion ? $legajo->problematicas->pluck('nombre')->values()->all() : [],
                'observaciones' => $puedeVerSituacion ? $legajo->observaciones : null,
                'persona' => $puedeVerPersona && $persona ? [
                    'nombre_completo' => "{$persona->apellido}, {$persona->nombre}",
                    'dni' => $persona->dni,
                    'fecha_nacimiento' => $persona->fecha_nacimiento?->format('d/m/Y'),
                    'edad' => $persona->fecha_nacimiento?->age,
                    'domicilio' => collect([
                        $persona->direccion_calle,
                        $persona->direccion_numero,
                        $persona->direccion_piso_depto,
                    ])->filter()->implode(' '),
                    'localidad' => $persona->localidad?->nombre,
                    'barrio' => $persona->barrio?->nombre,
                ] : null,
            ];
        });

        return Inertia::render('fichas/index', [
            'fichas' => $fichas,
            'buscar' => $buscar !== '' ? $buscar : null,
        ]);
    }
}

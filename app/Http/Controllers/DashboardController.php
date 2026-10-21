<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\TeamInvitation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $hoy = now();
        $inicioMes = $hoy->copy()->startOfMonth();

        return Inertia::render('dashboard', [
            'estadisticas' => [
                'personasActivas' => Persona::where('activo', true)->count(),
                'legajosAbiertos' => Persona::whereNotNull('numero_legajo')
                    ->where('estado', 'Abierto')
                    ->count(),
                'riesgoPrioritario' => Persona::whereNotNull('numero_legajo')
                    ->whereIn('nivel_riesgo', ['Alto', 'Critico'])
                    ->count(),
                'aperturasMes' => Persona::whereNotNull('numero_legajo')
                    ->whereDate('fecha_apertura', '>=', $inicioMes)
                    ->whereDate('fecha_apertura', '<=', $hoy)
                    ->count(),
            ],
            'personasRecientes' => Persona::with('localidad')
                ->where('activo', true)
                ->latest('creado_en')
                ->take(5)
                ->get()
                ->map(fn (Persona $persona) => [
                    'id' => $persona->id,
                    'nombreCompleto' => "{$persona->nombre} {$persona->apellido}",
                    'dni' => $persona->dni,
                    'localidad' => $persona->localidad?->nombre,
                    'fechaAlta' => $persona->creado_en->format('d/m/Y'),
                ]),
            'legajosRecientes' => Persona::query()
                ->whereNotNull('numero_legajo')
                ->latest('fecha_apertura')
                ->take(6)
                ->get()
                ->map(fn (Persona $persona) => [
                    'id' => $persona->id,
                    'numero' => $persona->numero_legajo,
                    'persona' => "{$persona->apellido}, {$persona->nombre}",
                    'dni' => $persona->dni,
                    'riesgo' => $persona->nivel_riesgo,
                    'estado' => $persona->estado,
                    'fechaApertura' => $persona->fecha_apertura?->format('d/m/Y'),
                     'motivo' => $persona->motivo_ingreso,
                 ]),
            'pendingInvitations' => TeamInvitation::query()
                ->with(['team', 'inviter'])
                ->where('email', $request->user()->email)
                ->whereNull('accepted_at')
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->get()
                ->map(fn (TeamInvitation $invitation) => [
                    'code' => $invitation->code,
                    'inviterName' => $invitation->inviter->name,
                    'team' => [
                        'name' => $invitation->team->name,
                        'slug' => $invitation->team->slug,
                    ],
                ]),
        ]);
    }
}

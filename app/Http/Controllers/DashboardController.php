<?php

namespace App\Http\Controllers;

use App\Models\Legajo;
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
                'legajosAbiertos' => Legajo::where('estado', 'Abierto')->count(),
                'riesgoPrioritario' => Legajo::whereIn('nivel_riesgo', ['Alto', 'Critico'])->count(),
                'aperturasMes' => Legajo::whereDate('fecha_apertura', '>=', $inicioMes)
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
            'legajosRecientes' => Legajo::with('persona')
                ->latest('fecha_apertura')
                ->take(6)
                ->get()
                ->map(fn (Legajo $legajo) => [
                    'id' => $legajo->id,
                    'numero' => $legajo->numero_legajo,
                    'persona' => $legajo->persona ? "{$legajo->persona->apellido}, {$legajo->persona->nombre}" : null,
                    'dni' => $legajo->persona?->dni,
                    'riesgo' => $legajo->nivel_riesgo,
                    'estado' => $legajo->estado,
                    'fechaApertura' => $legajo->fecha_apertura?->format('d/m/Y'),
                    'motivo' => $legajo->motivo_ingreso,
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

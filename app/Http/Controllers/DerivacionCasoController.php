<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDerivacionCasoRequest;
use App\Models\AuditoriaEvento;
use App\Models\CasoResponsable;
use App\Models\DerivacionCaso;
use App\Models\Legajo;
use App\Models\User;
use App\Notifications\DerivacionCasoNotification;
use App\Services\DerivacionNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DerivacionCasoController extends Controller
{
    /** @var array<int, string> */
    private const RESPONSABLE_ROLES = [
        'admin',
        'oficios',
        'coordinador',
        'profesional',
        'administrativo',
        'operador',
    ];

    public function store(
        StoreDerivacionCasoRequest $request,
        Legajo $legajo,
        DerivacionNotificationService $notificador,
    ): RedirectResponse {
        $datos = $request->validated();
        $destinatario = $this->responsablesQuery()->with('rol')->findOrFail($datos['destinatario_id']);

        abort_if($destinatario->id === $request->user()->id, 422, 'Seleccioná a otra persona del equipo.');
        abort_unless($destinatario->activo && in_array($destinatario->rol?->nombre, self::RESPONSABLE_ROLES, true), 422, 'La persona seleccionada no puede recibir derivaciones.');

        DB::transaction(function () use ($datos, $destinatario, $legajo, $request, $notificador): void {
            if (
                $legajo->profesional_id !== null
                && ! $legajo->responsablesActivos()->where('usuario_id', $legajo->profesional_id)->exists()
            ) {
                $responsableActual = $legajo->responsables()->firstOrNew([
                    'usuario_id' => $legajo->profesional_id,
                ]);
                $responsableActual->fill([
                    'asignado_por' => $request->user()->id,
                    'retirado_por' => null,
                    'es_principal' => true,
                    'activo' => true,
                    'asignado_en' => $responsableActual->asignado_en ?? now(),
                    'retirado_en' => null,
                ])->save();
            }

            $responsablesReemplazados = $this->responsablesActuales($legajo);

            $derivacion = DerivacionCaso::create([
                'legajo_id' => $legajo->id,
                'solicitado_por' => $request->user()->id,
                'destinatario_id' => $destinatario->id,
                'modo' => $datos['modo'],
                'estado' => DerivacionCaso::ESTADO_PENDIENTE,
                'motivo' => $datos['motivo'],
                'detalle' => $datos['detalle'] ?? null,
                'responsables_reemplazados' => $responsablesReemplazados,
                'grupo_derivacion_id' => (string) Str::uuid(),
                'fecha_limite' => $datos['fecha_limite'] ?? null,
            ]);

            $notificador->notificar($derivacion, $legajo, $request->user(), $destinatario);
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "La derivación fue enviada a {$destinatario->name}.",
        ]);

        return back();
    }

    public function aceptar(Request $request, DerivacionCaso $derivacion): RedirectResponse
    {
        $this->autorizarDestinatario($request, $derivacion);
        abort_unless($derivacion->estado === DerivacionCaso::ESTADO_PENDIENTE, 422, 'La derivación ya fue procesada.');

        DB::transaction(function () use ($derivacion, $request): void {
            $derivacion->update([
                'estado' => DerivacionCaso::ESTADO_ACEPTADA,
                'aceptada_en' => now(),
            ]);

            $this->aplicarResponsabilidad($derivacion, $request->user());
            $this->notificarSolicitante(
                $derivacion,
                'derivacion_aceptada',
                'Derivación aceptada',
                "{$request->user()->name} aceptó la derivación del legajo {$derivacion->legajo->numero_legajo}.",
            );
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'La derivación fue aceptada y el responsable principal fue actualizado.',
        ]);

        return back();
    }

    public function rechazar(Request $request, DerivacionCaso $derivacion): RedirectResponse
    {
        $this->autorizarDestinatario($request, $derivacion);
        abort_unless($derivacion->estado === DerivacionCaso::ESTADO_PENDIENTE, 422, 'La derivación ya fue procesada.');

        $datos = $request->validate([
            'motivo_rechazo' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($derivacion, $datos, $request): void {
            $derivacion->update([
                'estado' => DerivacionCaso::ESTADO_RECHAZADA,
                'rechazada_en' => now(),
                'motivo_rechazo' => $datos['motivo_rechazo'],
            ]);

            $this->notificarSolicitante(
                $derivacion,
                'derivacion_rechazada',
                'Derivación rechazada',
                "{$request->user()->name} rechazó la derivación del legajo {$derivacion->legajo->numero_legajo}.",
            );
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'La derivación fue rechazada.',
        ]);

        return back();
    }

    public function cancelar(
        Request $request,
        DerivacionCaso $derivacion,
        DerivacionNotificationService $notificador,
    ): RedirectResponse {
        abort_unless(
            $derivacion->solicitado_por === $request->user()->id
                || $request->user()->tieneRol('admin', 'coordinador'),
            403,
        );
        abort_unless(in_array($derivacion->estado, [
            DerivacionCaso::ESTADO_PENDIENTE,
            DerivacionCaso::ESTADO_ACEPTADA,
        ], true), 422, 'La derivación no se puede cancelar.');

        DB::transaction(function () use ($derivacion, $request, $notificador): void {
            if ($derivacion->estado === DerivacionCaso::ESTADO_ACEPTADA) {
                CasoResponsable::query()
                    ->where('legajo_id', $derivacion->legajo_id)
                    ->where('usuario_id', $derivacion->destinatario_id)
                    ->where('activo', true)
                    ->update([
                        'activo' => false,
                        'retirado_por' => $request->user()->id,
                        'retirado_en' => now(),
                    ]);

                $this->restaurarResponsablesReemplazados($derivacion);
            }

            $derivacion->update([
                'estado' => DerivacionCaso::ESTADO_CANCELADA,
                'cancelada_en' => now(),
            ]);

            $notificador->notificarCancelacion($derivacion, $request->user());
        });

        return back()->with('status', 'La derivación fue cancelada.');
    }

    /** @return array<int, int> */
    private function responsablesActuales(Legajo $legajo): array
    {
        return $legajo->responsablesActivos()
            ->orderByDesc('es_principal')
            ->orderBy('id')
            ->pluck('usuario_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function aplicarResponsabilidad(DerivacionCaso $derivacion, User $usuario): void
    {
        $idsReemplazados = $derivacion->responsables_reemplazados ?? [];

        if ($derivacion->modo === DerivacionCaso::MODO_ADICIONAL) {
            $yaActivo = CasoResponsable::query()
                ->where('legajo_id', $derivacion->legajo_id)
                ->where('usuario_id', $derivacion->destinatario_id)
                ->where('activo', true)
                ->exists();

            abort_if(
                ! $yaActivo && $derivacion->legajo->responsablesActivos()->count() >= CasoResponsable::MAX_ACTIVOS,
                422,
                'El legajo ya alcanzó el máximo de '.CasoResponsable::MAX_ACTIVOS.' responsables activos.',
            );
        }

        if ($derivacion->modo === DerivacionCaso::MODO_REEMPLAZO && $idsReemplazados !== []) {
            CasoResponsable::query()
                ->where('legajo_id', $derivacion->legajo_id)
                ->whereIn('usuario_id', $idsReemplazados)
                ->where('activo', true)
                ->update([
                    'activo' => false,
                    'retirado_por' => $usuario->id,
                    'retirado_en' => now(),
                ]);
        }

        $responsable = CasoResponsable::query()->firstOrNew([
            'legajo_id' => $derivacion->legajo_id,
            'usuario_id' => $derivacion->destinatario_id,
        ]);
        $responsable->fill([
            'asignado_por' => $derivacion->solicitado_por,
            'retirado_por' => null,
            'es_principal' => true,
            'activo' => true,
            'asignado_en' => $responsable->asignado_en ?? now(),
            'retirado_en' => null,
        ])->save();

        AuditoriaEvento::registrar(
            'legajo.responsable_asignado',
            $derivacion->legajo,
            ['responsables_anteriores' => $idsReemplazados],
            [
                'derivacion_id' => $derivacion->id,
                'modo' => $derivacion->modo,
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
            ],
        );

        CasoResponsable::query()
            ->where('legajo_id', $derivacion->legajo_id)
            ->where('id', '!=', $responsable->id)
            ->where('es_principal', true)
            ->where('activo', true)
            ->update(['es_principal' => false]);

        $derivacion->legajo()->update(['profesional_id' => $derivacion->destinatario_id]);
    }

    private function restaurarResponsablesReemplazados(DerivacionCaso $derivacion): void
    {
        CasoResponsable::query()
            ->where('legajo_id', $derivacion->legajo_id)
            ->whereIn('usuario_id', $derivacion->responsables_reemplazados ?? [])
            ->update([
                'activo' => true,
                'es_principal' => false,
                'retirado_por' => null,
                'retirado_en' => null,
            ]);

        $principalId = $derivacion->responsables_reemplazados[0] ?? null;

        CasoResponsable::query()
            ->where('legajo_id', $derivacion->legajo_id)
            ->where('usuario_id', $principalId)
            ->where('activo', true)
            ->update(['es_principal' => true]);

        $derivacion->legajo()->update(['profesional_id' => $principalId]);
    }

    private function autorizarDestinatario(Request $request, DerivacionCaso $derivacion): void
    {
        abort_unless(
            $derivacion->destinatario_id === $request->user()->id
                || $request->user()->tieneRol('admin', 'coordinador'),
            403,
        );
    }

    private function notificarSolicitante(
        DerivacionCaso $derivacion,
        string $evento,
        string $titulo,
        string $mensaje,
    ): void {
        $derivacion->loadMissing(['solicitante', 'legajo']);

        $derivacion->solicitante?->notify(new DerivacionCasoNotification(
            evento: $evento,
            titulo: $titulo,
            mensaje: $mensaje,
            url: route('legajos.show', $derivacion->legajo),
            legajoId: $derivacion->legajo_id,
            derivacionId: $derivacion->id,
        ));
    }

    /** @return Builder<User> */
    private function responsablesQuery(): Builder
    {
        return User::query()
            ->where('activo', true)
            ->whereHas('rol', fn ($query) => $query->whereIn('nombre', self::RESPONSABLE_ROLES));
    }
}

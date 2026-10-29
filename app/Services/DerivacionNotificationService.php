<?php

namespace App\Services;

use App\Models\Conversacion;
use App\Models\DerivacionCaso;
use App\Models\Legajo;
use App\Models\User;
use App\Notifications\DerivacionCasoNotification;

class DerivacionNotificationService
{
    public function notificar(
        DerivacionCaso $derivacion,
        Legajo $legajo,
        User $solicitante,
        User $destinatario,
    ): void {
        $conversacion = Conversacion::query()
            ->where('tipo', 'directa')
            ->whereHas('usuarios', fn ($query) => $query->whereKey($solicitante->id))
            ->whereHas('usuarios', fn ($query) => $query->whereKey($destinatario->id))
            ->withCount('usuarios')
            ->get()
            ->first(fn (Conversacion $conversacion) => $conversacion->usuarios_count === 2);

        if (! $conversacion) {
            $conversacion = Conversacion::create([
                'tipo' => 'directa',
                'creado_por' => $solicitante->id,
            ]);

            $conversacion->usuarios()->attach([
                $solicitante->id => ['ultimo_leido_en' => now()],
                $destinatario->id => ['ultimo_leido_en' => null],
            ]);
        }

        $mensaje = $conversacion->mensajes()->create([
            'usuario_id' => $solicitante->id,
            'cuerpo' => "Nueva derivación del legajo {$legajo->numero_legajo}.\n\n"
                ."Motivo: {$derivacion->motivo}.\n"
                ."Modo: {$derivacion->modo}."
                .($derivacion->detalle ? "\nDetalle: {$derivacion->detalle}." : '')
                .($derivacion->fecha_limite ? "\nFecha límite: {$derivacion->fecha_limite->format('d/m/Y H:i')}." : ''),
        ]);

        $mensaje->recursos()->create([
            'tipo_recurso' => 'legajo',
            'recurso_id' => $legajo->id,
        ]);

        $conversacion->update(['ultimo_mensaje_en' => now()]);
        $derivacion->conversaciones()->syncWithoutDetaching([$conversacion->id]);

        $destinatario->notify(new DerivacionCasoNotification(
            evento: 'derivacion_recibida',
            titulo: 'Nueva derivación',
            mensaje: "Recibiste una derivación del legajo {$legajo->numero_legajo}.",
            url: route('legajos.show', $legajo),
            legajoId: $legajo->id,
            derivacionId: $derivacion->id,
        ));
    }
}

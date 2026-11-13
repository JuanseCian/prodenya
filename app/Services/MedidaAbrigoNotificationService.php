<?php

namespace App\Services;

use App\Models\MedidaAbrigo;
use App\Models\User;
use App\Notifications\MedidaAbrigoVencimientoNotification;

class MedidaAbrigoNotificationService
{
    /**
     * Días antes del vencimiento (y el día mismo, con 0) en los que se avisa.
     *
     * @var array<int, int>
     */
    public const UMBRALES_DIAS = [15, 7, 1, 0];

    public function avisarVencimientosProximos(): int
    {
        $avisadas = 0;

        foreach (self::UMBRALES_DIAS as $diasRestantes) {
            $medidas = MedidaAbrigo::query()
                ->whereDate('fecha_fin', today()->addDays($diasRestantes))
                ->with(['legajo.profesional', 'legajo.responsablesActivos.usuario'])
                ->get();

            foreach ($medidas as $medida) {
                if ($this->avisarMedida($medida, $diasRestantes)) {
                    $avisadas++;
                }
            }
        }

        return $avisadas;
    }

    private function avisarMedida(MedidaAbrigo $medida, int $diasRestantes): bool
    {
        $legajo = $medida->legajo;

        $destinatarios = collect([$legajo->profesional])
            ->merge($legajo->responsablesActivos->pluck('usuario'))
            ->filter()
            ->unique('id');

        if ($destinatarios->isEmpty()) {
            return false;
        }

        $mensaje = $diasRestantes > 0
            ? "La medida de abrigo del legajo {$legajo->numero_legajo} vence en {$diasRestantes} ".($diasRestantes === 1 ? 'día' : 'días').'.'
            : "La medida de abrigo del legajo {$legajo->numero_legajo} vence hoy.";

        $destinatarios->each(fn (User $usuario) => $usuario->notify(new MedidaAbrigoVencimientoNotification(
            titulo: $diasRestantes > 0 ? 'Medida de abrigo por vencer' : 'Medida de abrigo vence hoy',
            mensaje: $mensaje,
            url: route('legajos.show', $legajo),
            legajoId: $legajo->id,
            medidaAbrigoId: $medida->id,
            diasRestantes: $diasRestantes,
        )));

        return true;
    }
}

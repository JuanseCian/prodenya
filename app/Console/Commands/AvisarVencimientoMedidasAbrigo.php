<?php

namespace App\Console\Commands;

use App\Services\MedidaAbrigoNotificationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('medidas-abrigo:avisar-vencimientos')]
#[Description('Avisa a los responsables del legajo cuando una medida de abrigo está por vencer')]
class AvisarVencimientoMedidasAbrigo extends Command
{
    public function handle(MedidaAbrigoNotificationService $notificador): int
    {
        try {
            $avisadas = $notificador->avisarVencimientosProximos();
            $this->info("{$avisadas} medida(s) de abrigo avisada(s).");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}

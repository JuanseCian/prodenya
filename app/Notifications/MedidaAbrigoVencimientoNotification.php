<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class MedidaAbrigoVencimientoNotification extends Notification
{
    public function __construct(
        public readonly string $titulo,
        public readonly string $mensaje,
        public readonly string $url,
        public readonly int $legajoId,
        public readonly int $medidaAbrigoId,
        public readonly int $diasRestantes,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'evento' => 'medida_abrigo_por_vencer',
            'titulo' => $this->titulo,
            'mensaje' => $this->mensaje,
            'url' => $this->url,
            'legajo_id' => $this->legajoId,
            'medida_abrigo_id' => $this->medidaAbrigoId,
            'dias_restantes' => $this->diasRestantes,
        ];
    }
}

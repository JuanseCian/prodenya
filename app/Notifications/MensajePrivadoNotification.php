<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class MensajePrivadoNotification extends Notification
{
    public function __construct(
        public readonly int $conversacionId,
        public readonly string $remitente,
        public readonly string $mensaje,
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
            'evento' => 'mensaje_privado',
            'titulo' => "Nuevo mensaje de {$this->remitente}",
            'mensaje' => $this->mensaje,
            'url' => route('comunicacion.show', $this->conversacionId),
            'conversacion_id' => $this->conversacionId,
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class DerivacionCasoNotification extends Notification
{
    public function __construct(
        public readonly string $evento,
        public readonly string $titulo,
        public readonly string $mensaje,
        public readonly string $url,
        public readonly int $legajoId,
        public readonly int $derivacionId,
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
            'evento' => $this->evento,
            'titulo' => $this->titulo,
            'mensaje' => $this->mensaje,
            'url' => $this->url,
            'legajo_id' => $this->legajoId,
            'derivacion_id' => $this->derivacionId,
        ];
    }
}

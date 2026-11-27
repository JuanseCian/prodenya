<?php

namespace App\Notifications;

use App\Models\Aviso;
use Illuminate\Notifications\Notification;

class AvisoPublicadoNotification extends Notification
{
    public function __construct(
        public readonly Aviso $aviso,
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
            'evento' => 'aviso_publicado',
            'titulo' => $this->aviso->titulo,
            'mensaje' => $this->aviso->mensaje,
            'url' => $this->aviso->url,
            'aviso_id' => $this->aviso->id,
        ];
    }
}

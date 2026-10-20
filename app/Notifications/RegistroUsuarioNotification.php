<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class RegistroUsuarioNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $tipo, private readonly ?string $motivo = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        if ($this->tipo === 'verificacion') {
            $url = URL::temporarySignedRoute(
                'registration.verify-email',
                now()->addDay(),
                ['user' => $notifiable->id, 'hash' => sha1($notifiable->email)],
            );

            return (new MailMessage)
                ->subject('Verificá tu correo para PRODENYA')
                ->greeting('Hola, '.$notifiable->name)
                ->line('Recibimos tu solicitud de registro en PRODENYA.')
                ->line('Antes de que el equipo pueda revisar tu solicitud, necesitamos verificar tu correo electrónico.')
                ->action('Verificar correo', $url)
                ->line('Después de verificarlo, un administrador revisará tu solicitud y te informará la resolución.');
        }

        $subject = match ($this->tipo) {
            'aprobado' => 'Tu cuenta PRODENYA fue aprobada',
            'rechazado' => 'Actualización de tu solicitud PRODENYA',
            'suspendido' => 'Tu cuenta PRODENYA fue suspendida',
            default => 'Actualización de tu cuenta PRODENYA',
        };

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hola, '.$notifiable->name);

        if ($this->tipo === 'aprobado') {
            return $mail
                ->line('Tu cuenta fue aprobada y ya podés ingresar al sistema.')
                ->action('Ingresar a PRODENYA', route('login'));
        }

        return $mail
            ->line($this->tipo === 'rechazado'
                ? 'Tu solicitud de acceso no fue aprobada en esta instancia.'
                : 'Tu acceso al sistema fue suspendido temporalmente.')
            ->when($this->motivo, fn (MailMessage $message) => $message->line('Motivo: '.$this->motivo))
            ->line('Si necesitás más información, comunicate con la administración de PRODENYA.');
    }
}

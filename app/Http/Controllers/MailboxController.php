<?php

namespace App\Http\Controllers;

use App\Models\MailMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MailboxController extends Controller
{
    public function index(Request $request): Response
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $soloNoLeidos = $request->boolean('no_leidos');

        $mensajes = MailMessage::query()
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('subject', 'like', "%{$buscar}%")
                        ->orWhere('from_name', 'like', "%{$buscar}%")
                        ->orWhere('from_email', 'like', "%{$buscar}%")
                        ->orWhere('body_text', 'like', "%{$buscar}%");
                });
            })
            ->when($soloNoLeidos, fn ($query) => $query->where('is_read', false))
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (MailMessage $mensaje) => [
                'id' => $mensaje->id,
                'subject' => $mensaje->subject ?: '(Sin asunto)',
                'from_name' => $mensaje->from_name,
                'from_email' => $mensaje->from_email,
                'received_at' => $mensaje->received_at?->toIso8601String(),
                'is_read' => $mensaje->is_read,
                'preview' => str($mensaje->body_text)->squish()->limit(180)->toString(),
            ]);

        return Inertia::render('mailbox/index', [
            'mensajes' => $mensajes,
            'filtros' => [
                'buscar' => $buscar !== '' ? $buscar : null,
                'no_leidos' => $soloNoLeidos,
            ],
            'noLeidos' => MailMessage::where('is_read', false)->count(),
        ]);
    }

    public function markRead(MailMessage $mailMessage)
    {
        $mailMessage->update(['is_read' => true]);

        return back();
    }
}

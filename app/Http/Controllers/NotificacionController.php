<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificacionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'notificaciones' => $this->notificaciones($request),
            'notificacionesNoLeidas' => (int) $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function marcarLeida(Request $request, DatabaseNotification $notification): RedirectResponse|JsonResponse
    {
        $this->autorizarNotificacion($request, $notification);
        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }

    public function marcarTodasLeidas(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }

    /** @return array<int, array<string, mixed>> */
    private function notificaciones(Request $request): array
    {
        return $request->user()->notifications()
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (DatabaseNotification $notification): array => [
                'id' => $notification->id,
                'titulo' => $notification->data['titulo'] ?? 'Notificación',
                'mensaje' => $notification->data['mensaje'] ?? '',
                'url' => $notification->data['url'] ?? null,
                'leida' => $notification->read_at !== null,
                'creada_en' => $notification->created_at?->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    private function autorizarNotificacion(Request $request, DatabaseNotification $notification): void
    {
        abort_unless(
            $notification->notifiable_type === $request->user()::class
                && (int) $notification->notifiable_id === $request->user()->id,
            403,
        );
    }
}

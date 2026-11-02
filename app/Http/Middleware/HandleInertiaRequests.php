<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    public function rootView(Request $request)
    {
        if ($request->routeIs('legajos.*', 'mayores18.*', 'mailbox.index', 'comunicacion.*', 'tareas.*', 'agenda.*')) {
            return 'layouts.frontend';
        }

        return parent::rootView($request);
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'can' => [
                'accessMailbox' => $user?->tieneRol('admin', 'oficios', 'coordinador') ?? false,
                'accessStatistics' => $user?->tieneRol(
                    'admin',
                    'operador',
                    'consulta',
                    'oficios',
                    'coordinador',
                    'profesional',
                    'administrativo',
                ) ?? false,
                'accessTasks' => $user?->tieneRol(
                    'admin',
                    'operador',
                    'oficios',
                    'coordinador',
                    'profesional',
                    'administrativo',
                ) ?? false,
                'accessAgenda' => $user?->tieneRol(
                    'admin',
                    'coordinador',
                    'profesional',
                    'administrativo',
                ) ?? false,
            ],
            'status' => fn (): ?string => $request->session()->get('status'),
            'comunicacionNoLeidos' => $user
                ? (int) DB::table('mensajes')
                    ->join('conversacion_usuario', 'conversacion_usuario.conversacion_id', '=', 'mensajes.conversacion_id')
                    ->where('conversacion_usuario.usuario_id', $user->id)
                    ->where('mensajes.usuario_id', '!=', $user->id)
                    ->where(function ($query) {
                        $query->whereNull('conversacion_usuario.ultimo_leido_en')
                            ->orWhereColumn('mensajes.created_at', '>', 'conversacion_usuario.ultimo_leido_en');
                    })
                    ->count()
                : 0,
            'notificaciones' => $user
                ? $user->notifications()
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
                    ->all()
                : [],
            'notificacionesNoLeidas' => $user
                ? (int) $user->unreadNotifications()->count()
                : 0,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}

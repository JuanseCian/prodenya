<?php

namespace App\Http\Middleware;

use App\Models\Acta;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
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

    /**
     * Routes that keep the bare `app` root view: the admin panel and the
     * authentication screens, which render their own chrome.
     *
     * @var array<int, string>
     */
    protected const RUTAS_SIN_HEADER_INSTITUCIONAL = [
        'admin.*',
        'login',
        'register',
        'register.*',
        'registration.*',
        'password.*',
        'verification.*',
        'two-factor.*',
    ];

    /**
     * Every authenticated page outside the admin panel shares the
     * institutional header defined in `layouts.frontend`.
     */
    public function rootView(Request $request): string
    {
        if ($request->user() && ! $request->routeIs(...self::RUTAS_SIN_HEADER_INSTITUCIONAL)) {
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
                'accessActas' => $user?->tieneRol(...Acta::ROLES) ?? false,
                'accessAgenda' => $user?->tieneRol(
                    'admin',
                    'coordinador',
                    'profesional',
                    'administrativo',
                ) ?? false,
            ],
            'status' => fn (): ?string => $request->session()->get('status'),
            'comunicacionNoLeidos' => fn (): int => $user?->mensajesSinLeer() ?? 0,
            'notificaciones' => fn (): array => $user
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
            'notificacionesNoLeidas' => fn (): int => $user
                ? (int) $user->unreadNotifications()->count()
                : 0,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}

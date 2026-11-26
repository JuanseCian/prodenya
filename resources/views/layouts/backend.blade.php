<!DOCTYPE html>
<html lang="es" @class(['dark' => ($appearance ?? 'system') === 'dark'])>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/SN.png') }}">

    <script>
        (function() {
            let stored = null;
            try {
                stored = localStorage.getItem('appearance');
            } catch (e) {}
            const cookie = document.cookie.split('; ').find((entry) => entry.startsWith('appearance='));
            const cookieAppearance = cookie ? decodeURIComponent(cookie.slice('appearance='.length)) : null;
            const appearance = ['light', 'dark', 'system'].includes(stored)
                ? stored
                : (['light', 'dark', 'system'].includes(cookieAppearance) ? cookieAppearance : '{{ $appearance ?? "system" }}');

            document.documentElement.classList.toggle(
                'dark',
                appearance === 'dark' || (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches),
            );
        })();
    </script>

    <title>
        @yield('title', 'Panel') | PRODENYA
    </title>

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        /* Misma escala tipográfica que el resto del sistema: cada tamaño lleva 2px más. */
        tailwind.config = {
            darkMode: 'selector',
            theme: {
                extend: {
                    fontSize: {
                        xs: ['calc(0.75rem + 2px)', { lineHeight: '1.25rem' }],
                        sm: ['calc(0.875rem + 2px)', { lineHeight: '1.5rem' }],
                        base: ['calc(1rem + 2px)', { lineHeight: '1.625rem' }],
                        lg: ['calc(1.125rem + 2px)', { lineHeight: '1.875rem' }],
                        xl: ['calc(1.25rem + 2px)', { lineHeight: '2rem' }],
                        '2xl': ['calc(1.5rem + 2px)', { lineHeight: '2.125rem' }],
                        '3xl': ['calc(1.875rem + 2px)', { lineHeight: '2.375rem' }],
                        '4xl': ['calc(2.25rem + 2px)', { lineHeight: '2.625rem' }],
                    },
                },
            },
        };
    </script>

    {{-- Bootstrap Icons --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    @stack('styles')

    <style>
        :root {
            --admin-ink: #162b3a;
            --admin-muted: #6b7d88;
            --admin-line: #dce8e3;
            --admin-soft: #f3f8f5;
            --admin-green: #4b8b6b;
            --admin-green-dark: #286047;
            --admin-navy: #153448;
            --admin-surface: #ffffff;
            --admin-topbar: rgba(255, 255, 255, 0.82);
        }

        html.dark {
            --admin-ink: #edf6fb;
            --admin-muted: #a9bdc7;
            --admin-line: #304753;
            --admin-soft: #172832;
            --admin-green: #8bc39a;
            --admin-green-dark: #b9d5e1;
            --admin-navy: #142630;
            --admin-surface: #14232c;
            --admin-topbar: rgba(16, 26, 32, 0.82);
        }

        /* Safety net for raw Tailwind utility classes used across admin Blade
         * pages that predate dark mode. New pages should prefer native
         * `dark:` utilities now that tailwind.config sets darkMode:'selector'. */
        html.dark [class~="bg-white"],
        html.dark [class~="bg-slate-50"] { background-color: var(--admin-soft) !important; }
        html.dark [class~="bg-slate-100"] { background-color: #1d2f39 !important; }
        html.dark [class~="border-slate-100"],
        html.dark [class~="border-slate-200"],
        html.dark [class~="divide-slate-100"] { border-color: var(--admin-line) !important; }
        html.dark [class~="text-slate-900"],
        html.dark [class~="text-slate-800"] { color: var(--admin-ink) !important; }
        html.dark [class~="text-slate-700"],
        html.dark [class~="text-slate-600"] { color: #c3d2d9 !important; }
        html.dark [class~="text-slate-500"],
        html.dark [class~="text-slate-400"],
        html.dark [class~="text-slate-300"] { color: var(--admin-muted) !important; }
        html.dark [class~="hover:bg-slate-50"]:hover,
        html.dark [class~="hover:bg-slate-100"]:hover,
        html.dark [class~="hover:bg-slate-50/70"]:hover { background-color: #22343e !important; }

        html.dark [class~="bg-indigo-50"] { background-color: rgba(139, 195, 154, 0.14) !important; }
        html.dark [class~="text-indigo-500"],
        html.dark [class~="text-indigo-600"],
        html.dark [class~="text-indigo-700"] { color: #8bc39a !important; }
        html.dark [class~="bg-indigo-600"] { background-color: #2f6350 !important; }
        html.dark [class~="hover:bg-indigo-700"]:hover,
        html.dark [class~="hover:bg-indigo-100"]:hover { background-color: #26503f !important; }
        html.dark [class~="hover:bg-indigo-50"]:hover { background-color: rgba(139, 195, 154, 0.1) !important; }
        html.dark [class~="hover:text-indigo-600"]:hover { color: #b9e8cc !important; }
        html.dark [class~="focus:border-indigo-400"]:focus { border-color: #7db493 !important; }
        html.dark [class~="focus:ring-indigo-100"]:focus { --tw-ring-color: rgba(139, 195, 154, 0.18) !important; }

        html.dark [class~="bg-amber-50"] { background-color: rgba(229, 186, 108, 0.16) !important; }
        html.dark [class~="text-amber-700"] { color: #e5ba6c !important; }
        html.dark [class~="ring-amber-200"] { --tw-ring-color: rgba(229, 186, 108, 0.4) !important; }
        html.dark [class~="bg-emerald-50"] { background-color: rgba(139, 195, 154, 0.16) !important; }
        html.dark [class~="text-emerald-700"] { color: #8bc39a !important; }
        html.dark [class~="ring-emerald-200"],
        html.dark [class~="border-emerald-200"] { --tw-ring-color: rgba(139, 195, 154, 0.4) !important; border-color: rgba(139, 195, 154, 0.4) !important; }
        html.dark [class~="bg-rose-50"] { background-color: rgba(213, 128, 112, 0.16) !important; }
        html.dark [class~="text-rose-700"] { color: #d58070 !important; }
        html.dark [class~="ring-rose-200"],
        html.dark [class~="border-rose-200"] { --tw-ring-color: rgba(213, 128, 112, 0.4) !important; border-color: rgba(213, 128, 112, 0.4) !important; }
        html.dark [class~="bg-orange-50"] { background-color: rgba(229, 186, 108, 0.16) !important; }
        html.dark [class~="text-orange-700"] { color: #e5ba6c !important; }
        html.dark [class~="ring-orange-200"] { --tw-ring-color: rgba(229, 186, 108, 0.4) !important; }

        body.backend-body {
            background: #f3f8f5;
            color: var(--admin-ink);
        }

        html.dark body.backend-body {
            background: #101a20;
        }

        /* ---------- Sidebar ---------- */

        .backend-sidebar {
            background: linear-gradient(180deg, #112f42 0%, #173e50 56%, #1c4b4d 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 18px 0 45px rgba(21, 52, 72, 0.08);
            color: #fff;
            transition: transform 220ms ease, width 220ms ease;
        }

        .backend-brand {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .backend-brand-mark {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
            flex-shrink: 0;
            height: 42px;
            object-fit: contain;
            padding: 5px;
            width: 42px;
        }

        .backend-brand-copy small {
            color: rgba(188, 235, 208, 0.68);
        }

        .backend-sidebar-icon-button {
            align-items: center;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            color: rgba(235, 248, 242, 0.72);
            display: flex;
            flex-shrink: 0;
            height: 34px;
            justify-content: center;
            transition: background-color 180ms ease, color 180ms ease;
            width: 34px;
        }

        .backend-sidebar-icon-button:hover,
        .backend-sidebar-icon-button:focus-visible {
            background: rgba(156, 224, 191, 0.18);
            color: #fff;
            outline: none;
        }

        .backend-sidebar-icon-button i {
            transition: transform 180ms ease;
        }

        .backend-nav-section {
            color: rgba(188, 235, 208, 0.52);
        }

        .backend-nav-link {
            align-items: center;
            border: 1px solid transparent;
            border-radius: 0.75rem;
            color: rgba(235, 248, 242, 0.72);
            display: flex;
            font-size: calc(0.875rem + 2px);
            gap: 0.75rem;
            min-height: 44px;
            padding: 0.5rem 0.75rem;
            position: relative;
            transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease, transform 160ms ease;
        }

        .backend-nav-link i {
            font-size: calc(1.05rem + 2px);
            text-align: center;
            width: 1.25rem;
        }

        .backend-nav-link:hover,
        .backend-nav-link:focus-visible {
            background: rgba(255, 255, 255, 0.09);
            border-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            outline: none;
            transform: translateX(2px);
        }

        .backend-nav-link.is-active {
            background: linear-gradient(100deg, rgba(156, 224, 191, 0.22), rgba(156, 224, 191, 0.06));
            border-color: rgba(156, 224, 191, 0.2);
            box-shadow: inset 3px 0 #9ce0bf;
            color: #fff;
            font-weight: 600;
        }

        .backend-nav-badge {
            background: #fbbf24;
            border-radius: 999px;
            color: #0f172a;
            font-size: calc(0.65rem + 2px);
            font-weight: 700;
            line-height: 1;
            padding: 0.3rem 0.5rem;
        }

        .backend-sidebar-account {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .backend-avatar {
            align-items: center;
            border-radius: 999px;
            display: flex;
            flex-shrink: 0;
            font-size: calc(0.8rem + 2px);
            font-weight: 700;
            height: 2.25rem;
            justify-content: center;
            letter-spacing: 0.02em;
            width: 2.25rem;
        }

        .backend-sidebar .backend-avatar {
            background: rgba(156, 224, 191, 0.18);
            border: 1px solid rgba(156, 224, 191, 0.3);
            color: #d8f5e5;
        }

        .backend-sidebar-account small {
            color: rgba(188, 235, 208, 0.68);
        }

        .backend-content-shell {
            transition: margin-left 220ms ease;
        }

        @media (min-width: 1024px) {
            body.backend-sidebar-collapsed .backend-sidebar {
                width: 5.5rem;
            }

            body.backend-sidebar-collapsed .backend-content-shell {
                margin-left: 5.5rem;
            }

            body.backend-sidebar-collapsed .backend-brand {
                flex-direction: column;
                gap: 0.625rem;
                height: auto;
                justify-content: center;
                min-height: 5rem;
                padding: 0.875rem 0.75rem;
            }

            body.backend-sidebar-collapsed .backend-brand-copy,
            body.backend-sidebar-collapsed .backend-nav-section,
            body.backend-sidebar-collapsed .backend-nav-link > span,
            body.backend-sidebar-collapsed .backend-account-copy,
            body.backend-sidebar-collapsed .backend-account-actions span {
                display: none;
            }

            body.backend-sidebar-collapsed .backend-nav-section-divider {
                display: block;
            }

            body.backend-sidebar-collapsed #backend-sidebar-collapse i {
                transform: rotate(180deg);
            }

            body.backend-sidebar-collapsed #backend-sidebar-collapse {
                margin-left: 0;
            }

            body.backend-sidebar-collapsed .backend-nav {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            body.backend-sidebar-collapsed .backend-nav-link {
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
            }

            body.backend-sidebar-collapsed .backend-nav-link .backend-nav-badge {
                height: 0.6rem;
                padding: 0;
                position: absolute;
                right: 0.6rem;
                top: 0.55rem;
                width: 0.6rem;
                font-size: 0;
            }

            body.backend-sidebar-collapsed .backend-sidebar-account,
            body.backend-sidebar-collapsed .backend-account-actions {
                align-items: center;
                flex-direction: column;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            body.backend-sidebar-collapsed .backend-account-actions a,
            body.backend-sidebar-collapsed .backend-account-actions button {
                justify-content: center;
                width: 2.75rem;
            }
        }

        .backend-nav-section-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: none;
            margin: 1rem 0.5rem 0.75rem;
        }

        .backend-account-actions a,
        .backend-account-actions button {
            align-items: center;
            border-radius: 0.625rem;
            color: rgba(235, 248, 242, 0.72);
            display: flex;
            font-size: calc(0.8rem + 2px);
            gap: 0.5rem;
            min-height: 36px;
            padding: 0.375rem 0.625rem;
            transition: background-color 160ms ease, color 160ms ease;
        }

        .backend-account-actions a:hover,
        .backend-account-actions button:hover,
        .backend-account-actions a:focus-visible,
        .backend-account-actions button:focus-visible {
            background: rgba(255, 255, 255, 0.09);
            color: #fff;
            outline: none;
        }

        /* ---------- Topbar ---------- */

        .backend-topbar {
            background: var(--admin-topbar);
            border-bottom: 1px solid var(--admin-line);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            position: sticky;
            top: 0;
            transition: box-shadow 200ms ease, background-color 200ms ease;
            z-index: 20;
        }

        .backend-topbar.is-scrolled {
            box-shadow: 0 10px 30px rgba(21, 52, 72, 0.08);
        }

        .backend-topbar-eyebrow {
            color: var(--admin-green);
        }

        .backend-topbar h1 {
            color: var(--admin-ink);
            letter-spacing: -0.035em;
        }

        .backend-topbar-description {
            color: var(--admin-muted);
        }

        .backend-icon-button {
            align-items: center;
            background: var(--admin-surface);
            border: 1px solid var(--admin-line);
            border-radius: 0.75rem;
            color: var(--admin-muted);
            display: flex;
            flex-shrink: 0;
            height: 2.5rem;
            justify-content: center;
            position: relative;
            transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease;
            width: 2.5rem;
        }

        .backend-icon-button:hover,
        .backend-icon-button:focus-visible {
            background: var(--admin-soft);
            border-color: #b9d4c5;
            color: var(--admin-green-dark);
            outline: none;
        }

        .backend-icon-badge {
            background: #e11d48;
            border: 2px solid var(--admin-surface);
            border-radius: 999px;
            color: #fff;
            font-size: calc(0.6rem + 2px);
            font-weight: 700;
            line-height: 1;
            min-width: 1.15rem;
            padding: 0.15rem 0.3rem;
            position: absolute;
            right: -0.35rem;
            text-align: center;
            top: -0.35rem;
        }

        .backend-theme-icon-dark,
        html.dark .backend-theme-icon-light {
            display: none;
        }

        html.dark .backend-theme-icon-dark {
            display: inline-block;
        }

        .backend-user-trigger {
            align-items: center;
            border: 1px solid transparent;
            border-radius: 999px;
            display: flex;
            gap: 0.75rem;
            padding: 0.25rem 0.75rem 0.25rem 0.25rem;
            transition: background-color 160ms ease, border-color 160ms ease;
        }

        .backend-user-trigger:hover,
        .backend-user-trigger:focus-visible,
        .backend-user-trigger[aria-expanded="true"] {
            background: var(--admin-soft);
            border-color: var(--admin-line);
            outline: none;
        }

        .backend-topbar .backend-avatar {
            background: linear-gradient(135deg, #286047, #153448);
            color: #fff;
        }

        .backend-user-name {
            color: var(--admin-ink);
        }

        .backend-user-role {
            color: var(--admin-muted);
        }

        .backend-user-menu {
            background: var(--admin-surface);
            border: 1px solid var(--admin-line);
            border-radius: 1rem;
            box-shadow: 0 20px 45px rgba(21, 52, 72, 0.16);
            color: var(--admin-ink);
            min-width: 15rem;
            position: absolute;
            right: 0;
            top: calc(100% + 0.5rem);
            transform-origin: top right;
            z-index: 30;
        }

        .backend-user-menu:not(.hidden) {
            animation: backend-pop 140ms ease-out;
        }

        .backend-user-menu-header {
            border-bottom: 1px solid var(--admin-line);
        }

        .backend-user-menu a,
        .backend-user-menu button {
            align-items: center;
            border-radius: 0.625rem;
            display: flex;
            font-size: calc(0.875rem + 2px);
            gap: 0.625rem;
            padding: 0.55rem 0.75rem;
            text-align: left;
            transition: background-color 140ms ease, color 140ms ease;
            width: 100%;
        }

        .backend-user-menu a:hover,
        .backend-user-menu button:hover,
        .backend-user-menu a:focus-visible,
        .backend-user-menu button:focus-visible {
            background: var(--admin-soft);
            color: var(--admin-green-dark);
            outline: none;
        }

        .backend-user-menu .backend-logout {
            color: #be123c;
        }

        html.dark .backend-user-menu .backend-logout {
            color: #fda4af;
        }

        @keyframes backend-pop {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(-4px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .backend-main-content {
            background: radial-gradient(circle at 100% 0, rgba(156, 224, 191, 0.12), transparent 28rem);
            min-height: calc(100vh - 5rem);
        }

        .backend-alerts {
            position: relative;
            z-index: 1;
        }

        @media (max-width: 1023px) {
            .backend-sidebar {
                box-shadow: 18px 0 50px rgba(21, 52, 72, 0.22);
            }
        }

        @media (max-width: 640px) {
            .backend-topbar {
                min-height: 4.5rem;
            }

            .backend-main-content {
                min-height: calc(100vh - 4.5rem);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .backend-sidebar,
            .backend-content-shell,
            .backend-nav-link,
            .backend-user-menu:not(.hidden) {
                animation: none;
                transition: none;
            }
        }
    </style>

</head>

@php
    $usuarioActual = auth()->user();
    $esGestion = $usuarioActual?->tieneRol('admin', 'coordinador') ?? false;
    $esAdmin = $usuarioActual?->esAdmin() ?? false;

    $usuariosPendientes = $esGestion
        ? \App\Models\User::where('estado_cuenta', \App\Models\User::ESTADO_PENDIENTE)->count()
        : 0;

    $notificacionesNoLeidas = $usuarioActual ? (int) $usuarioActual->unreadNotifications()->count() : 0;

    $nombreUsuario = $usuarioActual?->name ?? '';
    $inicialesUsuario = collect(preg_split('/\s+/', trim($nombreUsuario)))
        ->filter()
        ->take(2)
        ->map(fn (string $parte): string => mb_strtoupper(mb_substr($parte, 0, 1)))
        ->implode('');
    $rolUsuario = $usuarioActual?->rol?->nombre ? ucfirst($usuarioActual->rol->nombre) : 'Usuario';

    $catalogoActual = request()->route('catalog');
    $esCatalogo = fn (string $catalogo): bool => request()->routeIs('admin.catalogos.*') && $catalogoActual === $catalogo;

    /** @var array<int, array{label: string, items: array<int, array{label: string, href: string, icon: string, active: bool, badge?: int}>}> $secciones */
    $secciones = array_values(array_filter([
        [
            'label' => 'General',
            'items' => array_values(array_filter([
                $esAdmin ? ['label' => 'Panel', 'href' => route('admin.home'), 'icon' => 'bi-grid-1x2', 'active' => request()->routeIs('admin.home')] : null,
                $esAdmin ? ['label' => 'Auditoría', 'href' => route('admin.auditoria.index'), 'icon' => 'bi-clock-history', 'active' => request()->routeIs('admin.auditoria.*')] : null,
            ])),
        ],
        [
            'label' => 'Gestión',
            'items' => array_values(array_filter([
                $esGestion ? ['label' => 'Usuarios', 'href' => route('admin.usuarios.index'), 'icon' => 'bi-person-gear', 'active' => request()->routeIs('admin.usuarios.*'), 'badge' => $usuariosPendientes] : null,
                $esGestion && Route::has('estadisticas.ranking') ? ['label' => 'Ranking', 'href' => route('estadisticas.ranking'), 'icon' => 'bi-trophy', 'active' => request()->routeIs('estadisticas.ranking')] : null,
            ])),
        ],
        [
            'label' => 'Catálogos',
            'items' => $esAdmin ? collect([
                'roles' => ['Roles', 'bi-shield-lock'],
                'provincias' => ['Provincias', 'bi-map'],
                'localidades' => ['Localidades', 'bi-geo-alt'],
                'barrios' => ['Barrios', 'bi-signpost-2'],
                'tipos-documento' => ['Tipos de documento', 'bi-card-text'],
                'problematicas' => ['Problemáticas', 'bi-journal-medical'],
                'escuelas' => ['Escuelas', 'bi-building'],
            ])->map(fn (array $catalogo, string $clave): array => [
                'label' => $catalogo[0],
                'href' => route('admin.catalogos.index', ['catalog' => $clave]),
                'icon' => $catalogo[1],
                'active' => $esCatalogo($clave),
            ])->values()->all() : [],
        ],
    ], fn (array $seccion): bool => $seccion['items'] !== []));
@endphp

<body class="backend-body bg-slate-100 text-slate-800">

    <a
        href="#backend-main"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-slate-900 focus:shadow-lg"
    >
        Saltar al contenido
    </a>

    <div class="backend-shell flex min-h-screen">

        {{-- SIDEBAR --}}
        <aside
            id="backend-sidebar"
            class="backend-sidebar fixed inset-y-0 left-0 z-40 flex w-72 flex-col -translate-x-full lg:translate-x-0"
            aria-label="Menú administrativo"
        >

            {{-- Marca --}}
            <div class="backend-brand flex h-20 items-center gap-3 px-5">

                <a
                    href="{{ $esAdmin ? route('admin.home') : route('home') }}"
                    class="flex min-w-0 items-center gap-3"
                    title="PRODENYA"
                >
                    <img
                        src="{{ asset('assets/img/SN.png') }}"
                        alt="San Nicolás"
                        class="backend-brand-mark"
                    >

                    <div class="backend-brand-copy min-w-0 leading-tight">
                        <div class="truncate text-base font-bold tracking-wide">PRODENYA</div>
                        <small class="block truncate text-xs">Panel administrativo</small>
                    </div>
                </a>

                <button
                    type="button"
                    id="backend-sidebar-collapse"
                    class="backend-sidebar-icon-button ml-auto hidden lg:flex"
                    aria-label="Contraer menú administrativo"
                    aria-expanded="true"
                    title="Contraer menú"
                >
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                </button>

                <button
                    type="button"
                    id="backend-sidebar-close"
                    class="backend-sidebar-icon-button ml-auto lg:hidden"
                    aria-label="Cerrar menú administrativo"
                >
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>

            </div>

            {{-- Navegación --}}
            <nav class="backend-nav flex-1 overflow-y-auto px-4 py-5" aria-label="Navegación administrativa">
                @foreach ($secciones as $seccion)
                    <div @class(['mt-6' => ! $loop->first])>
                        @unless ($loop->first)
                            <div class="backend-nav-section-divider" aria-hidden="true"></div>
                        @endunless

                        <p class="backend-nav-section mb-2 px-3 text-[length:calc(0.65rem_+_2px)] font-bold tracking-[0.16em] uppercase">
                            {{ $seccion['label'] }}
                        </p>

                        <ul class="space-y-1">
                            @foreach ($seccion['items'] as $item)
                                <li>
                                    <a
                                        href="{{ $item['href'] }}"
                                        title="{{ $item['label'] }}"
                                        @class(['backend-nav-link', 'is-active' => $item['active']])
                                        @if ($item['active']) aria-current="page" @endif
                                    >
                                        <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                                        <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                        @if (($item['badge'] ?? 0) > 0)
                                            <span class="backend-nav-badge" aria-label="{{ $item['badge'] }} pendientes">
                                                {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>

            {{-- Cuenta --}}
            @auth
                <div class="backend-sidebar-account px-4 py-4">

                    <div class="flex items-center gap-3 px-1">
                        <div class="backend-avatar" title="{{ $nombreUsuario }}">{{ $inicialesUsuario }}</div>

                        <div class="backend-account-copy min-w-0">
                            <div class="truncate text-sm font-semibold">{{ $nombreUsuario }}</div>
                            <small class="block truncate text-xs">{{ $usuarioActual->email }}</small>
                        </div>
                    </div>

                    <div class="backend-account-actions mt-3 flex gap-1">
                        <a href="{{ route('home') }}" class="flex-1" title="Ir al sistema">
                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                            <span>Ir al sistema</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full" title="Cerrar sesión">
                                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                <span>Salir</span>
                            </button>
                        </form>
                    </div>

                </div>
            @endauth

        </aside>

        <div
            id="backend-sidebar-backdrop"
            class="fixed inset-0 z-30 hidden bg-slate-950/40 backdrop-blur-[2px] lg:hidden"
            aria-hidden="true"
        ></div>


        {{-- CONTENIDO DERECHO --}}
        <div class="backend-content-shell min-w-0 flex-1 lg:ml-72">

            {{-- Navbar superior --}}
            <header
                id="backend-topbar"
                class="backend-topbar flex h-20 items-center justify-between gap-4 px-5 md:px-8"
            >

                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        id="backend-menu-toggle"
                        class="backend-icon-button lg:hidden"
                        aria-label="Abrir menú administrativo"
                        aria-expanded="false"
                        aria-controls="backend-sidebar"
                    >
                        <i class="bi bi-list text-lg" aria-hidden="true"></i>
                    </button>

                    <div class="min-w-0">
                        <p class="backend-topbar-eyebrow hidden text-[length:calc(0.68rem_+_2px)] font-bold tracking-[0.14em] uppercase sm:block">
                            Panel administrativo
                        </p>

                        <h1 class="truncate text-lg font-semibold sm:text-xl">
                            @yield('page-title', 'Panel')
                        </h1>

                        @hasSection('page-description')
                            <p class="backend-topbar-description hidden truncate text-sm md:block">
                                @yield('page-description')
                            </p>
                        @endif
                    </div>
                </div>


                <div class="flex shrink-0 items-center gap-2 sm:gap-3">

                    {{-- Tema --}}
                    <button
                        type="button"
                        id="backend-theme-toggle"
                        class="backend-icon-button"
                        aria-label="Cambiar tema claro u oscuro"
                        title="Cambiar tema"
                    >
                        <i class="bi bi-moon-stars backend-theme-icon-light" aria-hidden="true"></i>
                        <i class="bi bi-sun backend-theme-icon-dark" aria-hidden="true"></i>
                    </button>

                    {{-- Notificaciones --}}
                    <a
                        href="{{ route('home') }}"
                        class="backend-icon-button"
                        aria-label="{{ $notificacionesNoLeidas > 0 ? $notificacionesNoLeidas.' notificaciones sin leer' : 'Notificaciones' }}"
                        title="Ver notificaciones en el sistema"
                    >
                        <i class="bi bi-bell" aria-hidden="true"></i>
                        @if ($notificacionesNoLeidas > 0)
                            <span class="backend-icon-badge">{{ $notificacionesNoLeidas > 9 ? '9+' : $notificacionesNoLeidas }}</span>
                        @endif
                    </a>

                    @auth
                        {{-- Menú de usuario --}}
                        <div class="relative">
                            <button
                                type="button"
                                id="backend-user-toggle"
                                class="backend-user-trigger"
                                aria-haspopup="menu"
                                aria-expanded="false"
                                aria-controls="backend-user-menu"
                            >
                                <span class="backend-avatar">{{ $inicialesUsuario }}</span>

                                <span class="hidden text-left leading-tight md:block">
                                    <span class="backend-user-name block max-w-[10rem] truncate text-sm font-semibold">{{ $nombreUsuario }}</span>
                                    <span class="backend-user-role block text-xs">{{ $rolUsuario }}</span>
                                </span>

                                <i class="bi bi-chevron-down backend-user-role hidden text-xs md:inline-block" aria-hidden="true"></i>
                            </button>

                            <div
                                id="backend-user-menu"
                                class="backend-user-menu hidden"
                                role="menu"
                                aria-labelledby="backend-user-toggle"
                            >
                                <div class="backend-user-menu-header px-4 py-3">
                                    <div class="truncate text-sm font-semibold">{{ $nombreUsuario }}</div>
                                    <div class="backend-user-role truncate text-xs">{{ $usuarioActual->email }}</div>
                                </div>

                                <div class="p-1.5">
                                    <a href="{{ route('profile.edit') }}" role="menuitem">
                                        <i class="bi bi-person-circle" aria-hidden="true"></i>
                                        Mi cuenta
                                    </a>
                                    <a href="{{ route('home') }}" role="menuitem">
                                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                        Ir al sistema
                                    </a>
                                </div>

                                <div class="border-t p-1.5" style="border-color: var(--admin-line)">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="backend-logout" role="menuitem">
                                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                            Cerrar sesión
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth

                </div>

            </header>


            {{-- Alertas --}}
            <div class="backend-alerts px-5 pt-5 md:px-8">

                @if (session('success'))

                    <div
                        role="status"
                        aria-live="polite"
                        class="mb-4 p-4
                               bg-emerald-50
                               border border-emerald-200
                               text-emerald-700
                               rounded-xl"
                    >
                        {{ session('success') }}
                    </div>

                @endif


                @if (session('error'))

                    <div
                        role="alert"
                        class="mb-4 p-4
                               bg-red-50
                               border border-red-200
                               text-red-700
                               rounded-xl"
                    >
                        {{ session('error') }}
                    </div>

                @endif

            </div>


            {{-- Contenido --}}
            <main id="backend-main" class="backend-main-content p-5 md:p-8" tabindex="-1">

                @yield('content')

            </main>

        </div>

    </div>


    @stack('scripts')
    <script>
        (() => {
            const sidebar = document.getElementById('backend-sidebar');
            const toggle = document.getElementById('backend-menu-toggle');
            const closeButton = document.getElementById('backend-sidebar-close');
            const backdrop = document.getElementById('backend-sidebar-backdrop');
            const collapseToggle = document.getElementById('backend-sidebar-collapse');
            const topbar = document.getElementById('backend-topbar');
            const themeToggle = document.getElementById('backend-theme-toggle');
            const userToggle = document.getElementById('backend-user-toggle');
            const userMenu = document.getElementById('backend-user-menu');

            const storage = {
                get(key) {
                    try {
                        return window.localStorage.getItem(key);
                    } catch (e) {
                        return null;
                    }
                },
                set(key, value) {
                    try {
                        window.localStorage.setItem(key, value);
                    } catch (e) {}
                },
            };

            /* Sombra del topbar al hacer scroll */
            const syncTopbarShadow = () => topbar?.classList.toggle('is-scrolled', window.scrollY > 8);
            syncTopbarShadow();
            window.addEventListener('scroll', syncTopbarShadow, { passive: true });

            /* Tema claro / oscuro (comparte la preferencia con el sistema principal) */
            themeToggle?.addEventListener('click', () => {
                const nextAppearance = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
                document.documentElement.classList.toggle('dark', nextAppearance === 'dark');
                storage.set('appearance', nextAppearance);
                document.cookie = `appearance=${nextAppearance};path=/;max-age=${60 * 60 * 24 * 365};SameSite=Lax`;
            });

            /* Menú de usuario */
            const setUserMenu = (isOpen) => {
                if (!userMenu || !userToggle) return;
                userMenu.classList.toggle('hidden', !isOpen);
                userToggle.setAttribute('aria-expanded', String(isOpen));
            };

            userToggle?.addEventListener('click', (event) => {
                event.stopPropagation();
                setUserMenu(userMenu.classList.contains('hidden'));
            });

            document.addEventListener('click', (event) => {
                if (userMenu && !userMenu.contains(event.target)) setUserMenu(false);
            });

            if (!sidebar || !toggle || !backdrop) return;

            /* Sidebar */
            const collapsedStorageKey = 'prodenya.backend.sidebar.collapsed';
            const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

            const setCollapsed = (collapsed, persist = true) => {
                const shouldCollapse = isDesktop() && collapsed;
                document.body.classList.toggle('backend-sidebar-collapsed', shouldCollapse);

                if (collapseToggle) {
                    collapseToggle.setAttribute('aria-expanded', String(!shouldCollapse));
                    collapseToggle.setAttribute('aria-label', shouldCollapse ? 'Expandir menú administrativo' : 'Contraer menú administrativo');
                    collapseToggle.setAttribute('title', shouldCollapse ? 'Expandir menú' : 'Contraer menú');
                }

                if (persist && isDesktop()) {
                    storage.set(collapsedStorageKey, String(shouldCollapse));
                }
            };

            const setSidebarState = (isOpen) => {
                sidebar.classList.toggle('-translate-x-full', !isOpen);
                backdrop.classList.toggle('hidden', !isOpen);
                toggle.setAttribute('aria-expanded', String(isOpen));
                document.body.classList.toggle('overflow-hidden', isOpen);

                if (isOpen) closeButton?.focus();
            };

            const closeSidebar = () => {
                if (sidebar.classList.contains('-translate-x-full')) return;
                setSidebarState(false);
                toggle.focus();
            };

            toggle.addEventListener('click', () => setSidebarState(sidebar.classList.contains('-translate-x-full')));
            closeButton?.addEventListener('click', closeSidebar);
            backdrop.addEventListener('click', closeSidebar);
            sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
                if (!isDesktop()) setSidebarState(false);
            }));
            collapseToggle?.addEventListener('click', () => {
                setCollapsed(!document.body.classList.contains('backend-sidebar-collapsed'));
            });
            setCollapsed(storage.get(collapsedStorageKey) === 'true', false);
            window.addEventListener('resize', () => {
                if (!isDesktop()) {
                    document.body.classList.remove('backend-sidebar-collapsed');
                    return;
                }

                setSidebarState(false);
                setCollapsed(storage.get(collapsedStorageKey) === 'true', false);
            });
            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;
                setUserMenu(false);
                if (!isDesktop()) closeSidebar();
            });
        })();
    </script>

</body>

</html>

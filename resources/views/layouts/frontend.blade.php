<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PRODENYA')</title>
    <meta name="description" content="@yield('description', 'Sistema de Promoción y Protección de los Derechos de Niños, Niñas y Adolescentes')">

    <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        @if (isset($page))
            @viteReactRefresh
            @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
            <x-inertia::head />
        @endif

    <style>
        :root {
            --brand: #6c63c7;
            --brand-dark: #514b9f;
            --brand-deep: #403b80;
            --ink: #283044;
            --muted: #70798e;
            --surface: #ffffff;
            --page: #f4f6fb;
            --line: #e4e7f0;
            --shadow: 0 16px 40px rgba(56, 54, 116, 0.1);
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            background: var(--page);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            min-height: 100vh;
        }

        .site-shell { min-height: 100vh; }

        .site-header {
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
            box-shadow: 0 5px 22px rgba(57, 51, 119, 0.2);
            color: #fff;
            position: relative;
            z-index: 10;
        }

        .site-header-inner {
            align-items: center;
            display: flex;
            gap: 28px;
            justify-content: space-between;
            margin: 0 auto;
            max-width: 1240px;
            min-height: 82px;
            padding: 10px 24px;
        }

        .brand-lockup {
            align-items: center;
            color: #fff;
            display: inline-flex;
            flex-shrink: 0;
            gap: 12px;
            text-decoration: none;
        }

        .brand-divider { background: rgba(255, 255, 255, 0.3); height: 42px; width: 1px; }

        .brand-mark {
            align-items: center;
            background: rgba(255, 255, 255, 0.17);
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 15px;
            box-shadow: 0 6px 16px rgba(38, 33, 94, 0.2);
            display: flex;
            font-size: 1.35rem;
            font-weight: 800;
            height: 50px;
            justify-content: center;
            letter-spacing: -0.08em;
            width: 50px;
        }

        .brand-mark-image { object-fit: contain; padding: 7px; }

        .brand-name {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            line-height: 1.1;
        }

        .brand-caption {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.7rem;
            letter-spacing: 0.02em;
            margin-top: 4px;
        }

        .site-nav {
            align-items: center;
            display: flex;
            gap: 5px;
            margin-left: auto;
        }

        .site-nav-link {
            align-items: center;
            border-radius: 10px;
            color: rgba(255, 255, 255, 0.86);
            display: inline-flex;
            font-size: 0.9rem;
            font-weight: 600;
            gap: 7px;
            padding: 10px 13px;
            text-decoration: none;
            transition: background-color 180ms ease, color 180ms ease, transform 180ms ease;
        }

        .site-nav-link:hover {
            background: rgba(255, 255, 255, 0.13);
            color: #fff;
            transform: translateY(-1px);
        }

        .site-nav-link.is-active { background: rgba(255, 255, 255, 0.16); color: #fff; }

        .site-user {
            align-items: center;
            border-left: 1px solid rgba(255, 255, 255, 0.22);
            display: flex;
            gap: 10px;
            margin-left: 10px;
            padding-left: 18px;
        }

        .site-user-name {
            color: rgba(255, 255, 255, 0.82);
            font-size: 0.82rem;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .site-user-avatar {
            align-items: center;
            background: #fff;
            border-radius: 50%;
            color: var(--brand-dark);
            display: flex;
            font-size: 0.75rem;
            font-weight: 800;
            height: 32px;
            justify-content: center;
            width: 32px;
        }

        .site-user-avatar-image { object-fit: cover; }

        .site-account { position: relative; }

        .site-account-trigger {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: 12px;
            color: #fff;
            cursor: pointer;
            display: flex;
            gap: 9px;
            list-style: none;
            padding: 5px 7px;
        }

        .site-account-trigger::-webkit-details-marker { display: none; }
        .site-account-trigger:hover, .site-account[open] .site-account-trigger { background: rgba(255, 255, 255, 0.13); }
        .site-account-chevron { font-size: 0.8rem; transition: transform 180ms ease; }
        .site-account[open] .site-account-chevron { transform: rotate(180deg); }

        .site-account-menu {
            background: #fff;
            border: 1px solid #e2e5ed;
            border-radius: 14px;
            box-shadow: 0 18px 45px rgba(35, 33, 76, 0.18);
            color: var(--ink);
            min-width: 278px;
            padding: 8px;
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            z-index: 30;
        }

        .site-account-menu-header { border-bottom: 1px solid #edf0f5; padding: 10px 10px 13px; }
        .site-account-menu-name { color: #2d3348; font-size: 0.9rem; font-weight: 800; }
        .site-account-menu-email { color: #778096; font-size: 0.75rem; margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .site-account-menu-meta { color: #6673a9; font-size: 0.7rem; font-weight: 700; margin-top: 8px; }
        .site-account-menu-link, .site-account-menu-logout { align-items: center; border-radius: 9px; display: flex; font-size: 0.82rem; font-weight: 600; gap: 10px; padding: 10px; text-decoration: none; width: 100%; }
        .site-account-menu-link { color: #3e455d; }
        .site-account-menu-link:hover { background: #f4f5fb; color: var(--brand-dark); }
        .site-account-menu-logout { background: transparent; border: 0; color: #b14657; cursor: pointer; text-align: left; }
        .site-account-menu-logout:hover { background: #fff1f2; }
        .site-account-menu-separator { border-top: 1px solid #edf0f5; margin: 6px 2px; }

        .logout-button {
            background: rgba(43, 38, 91, 0.32);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            color: #fff;
            cursor: pointer;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 8px 13px;
            transition: background-color 180ms ease;
        }

        .logout-button:hover { background: rgba(43, 38, 91, 0.55); }

        .site-main { min-height: calc(100vh - 164px); }

        .site-footer {
            background: var(--brand-dark);
            color: rgba(255, 255, 255, 0.78);
            margin-top: 64px;
        }

        .site-footer-inner {
            align-items: center;
            display: flex;
            gap: 20px;
            justify-content: space-between;
            margin: 0 auto;
            max-width: 1240px;
            padding: 22px 24px;
        }

        .site-footer strong { color: #fff; }
        .site-footer small { font-size: 0.78rem; }
        .footer-municipal-logo { height: 58px; object-fit: contain; object-position: left center; width: 225px; }

        @media (max-width: 900px) {
            .site-header-inner { align-items: flex-start; flex-wrap: wrap; gap: 10px 18px; padding-bottom: 14px; }
            .site-nav { flex-basis: 100%; margin-left: 0; overflow-x: auto; padding-bottom: 2px; }
            .site-user { margin-left: auto; }
        }

        @media (max-width: 560px) {
            .site-header-inner { padding-left: 16px; padding-right: 16px; }
            .brand-divider, .brand-caption, .site-user-name { display: none; }
            .site-user { border-left: 0; margin-left: auto; padding-left: 0; }
            .site-nav-link { font-size: 0.82rem; padding: 9px 10px; }
            .site-account-menu { max-width: calc(100vw - 32px); min-width: 250px; }
            .site-footer-inner { align-items: flex-start; flex-direction: column; padding: 20px 16px; }
        }

        .site-shell { background: var(--page); position: relative; }
        .site-page { display: flex; flex-direction: column; min-height: 100vh; margin-left: 0; }
        .site-sidebar {
            background: linear-gradient(170deg, #302b68 0%, #403b80 52%, #514b9f 100%);
            bottom: 0;
            box-shadow: -12px 0 32px rgba(49, 43, 104, 0.12);
            color: #fff;
            display: flex;
            flex-direction: column;
            left: auto;
            overflow-y: auto;
            padding: 22px 15px 16px;
            position: fixed;
            top: 0;
            right: 0;
            width: 276px;
            z-index: 40;
        }
        .site-sidebar-brand { align-items: center; display: flex; gap: 11px; padding: 3px 10px 24px; }
        .site-sidebar-brand .brand-mark { flex-shrink: 0; height: 44px; width: 44px; }
        .site-sidebar-brand .brand-name { font-size: 1.02rem; }
        .site-sidebar-brand .brand-caption { color: rgba(255, 255, 255, 0.6); font-size: 0.64rem; }
        .site-sidebar-rule { background: rgba(255, 255, 255, 0.15); height: 1px; margin: 0 10px 23px; }
        .site-sidebar-label { color: rgba(255, 255, 255, 0.45); font-size: 0.64rem; font-weight: 800; letter-spacing: 0.15em; padding: 0 12px 9px; text-transform: uppercase; }
        .site-sidebar-nav { display: flex; flex-direction: column; gap: 5px; }
        .site-sidebar-link {
            align-items: center;
            border: 1px solid transparent;
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.72);
            display: flex;
            font-size: 0.86rem;
            font-weight: 650;
            gap: 12px;
            padding: 12px 13px;
            text-decoration: none;
            transition: background-color 180ms ease, color 180ms ease, transform 180ms ease;
        }
        .site-sidebar-link i { color: rgba(255, 255, 255, 0.56); font-size: 1.03rem; width: 20px; }
        .site-sidebar-link:hover { background: rgba(255, 255, 255, 0.1); color: #fff; transform: translateX(2px); }
        .site-sidebar-link:hover i, .site-sidebar-link.is-active i { color: #fff; }
        .site-sidebar-link.is-active { background: rgba(255, 255, 255, 0.14); border-color: rgba(255, 255, 255, 0.1); box-shadow: inset 3px 0 0 #9ce0bf; color: #fff; }
        .site-sidebar-link.is-admin { background: rgba(156, 224, 191, 0.12); color: #dff8e9; margin-top: 6px; }
        .site-sidebar-link.is-admin i { color: #9ce0bf; }
        .site-sidebar-link.is-admin:hover { background: rgba(156, 224, 191, 0.2); }
        .site-sidebar-spacer { flex: 1; min-height: 28px; }
        .site-sidebar-user { align-items: center; background: rgba(0, 0, 0, 0.13); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 15px; display: flex; gap: 10px; padding: 11px; }
        .site-sidebar-user-copy { min-width: 0; }
        .site-sidebar-user-name { color: #fff; font-size: 0.78rem; font-weight: 750; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .site-sidebar-user-meta { color: rgba(255, 255, 255, 0.56); font-size: 0.67rem; margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .site-sidebar-account { position: relative; width: 100%; }
        .site-sidebar-account-trigger { gap: 10px; justify-content: flex-start; padding: 8px; width: 100%; }
        .site-sidebar-account-trigger .site-user-name { flex: 1; min-width: 0; text-align: left; }
        .site-sidebar-account .site-account-menu { bottom: calc(100% + 10px); left: 0; min-width: 0; right: auto; top: auto; transform-origin: bottom left; width: 100%; }
        .site-header { position: sticky; top: 0; }
        .site-header-inner { max-width: none; }
        .site-header-brand { display: none; }
        .site-admin-quicklink { align-items: center; background: rgba(255, 255, 255, 0.14); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 10px; color: #fff; display: inline-flex; font-size: 0.79rem; font-weight: 750; gap: 7px; padding: 9px 12px; text-decoration: none; transition: background-color 180ms ease, transform 180ms ease; }
        .site-admin-quicklink:hover { background: rgba(255, 255, 255, 0.23); color: #fff; transform: translateY(-1px); }
        .site-sidebar-backdrop { background: rgba(24, 21, 57, 0.42); inset: 0; opacity: 0; pointer-events: none; position: fixed; transition: opacity 180ms ease; z-index: 35; }

        @media (max-width: 1020px) {
            .site-page { margin-left: 0; }
            .site-sidebar { transform: translateX(105%); transition: transform 220ms ease; }
            .site-shell.sidebar-open .site-sidebar { transform: translateX(0); }
            .site-shell.sidebar-open .site-sidebar-backdrop { opacity: 1; pointer-events: auto; }
            .site-header-brand { align-items: center; color: #fff; display: inline-flex; flex: 1; gap: 8px; margin-left: 4px; text-decoration: none; }
            .site-header-brand .brand-mark { height: 36px; width: 36px; }
            .site-header-brand .brand-name { font-size: 0.9rem; }
            .site-header-brand .brand-caption { display: none; }
            .site-header-brand .brand-divider { height: 28px; }
            .site-user { margin-left: 8px; }
        }

        @media (max-width: 560px) {
            .site-header-inner { min-height: 68px; padding: 9px 16px; }
            .site-admin-quicklink { font-size: 0; gap: 0; height: 38px; padding: 9px 10px; width: 38px; }
            .site-admin-quicklink i { font-size: 0.95rem; }
            .site-user { border-left: 0; margin-left: 2px; padding-left: 0; }
        }

        .site-page { margin-left: 0; transition: margin-left 220ms ease; }
        .site-sidebar { transform: translateX(105%); transition: transform 220ms ease; }
        .site-shell.sidebar-open .site-sidebar { transform: translateX(0); }
        .site-header-brand { display: inline-flex; }
        .site-user-toggle { align-items: center; background: transparent; border: 0; border-radius: 999px; cursor: pointer; display: inline-flex; justify-content: center; padding: 2px; transition: box-shadow 180ms ease, transform 180ms ease; }
        .site-user-toggle:hover, .site-user-toggle[aria-expanded="true"] { box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.17); transform: translateY(-1px); }
        .site-user-toggle .site-user-avatar { height: 36px; width: 36px; }

        .site-shell.sidebar-open .site-sidebar-backdrop { opacity: 1; pointer-events: auto; }
    </style>

    @stack('styles')
</head>

<body>
    <div class="site-shell" id="site-shell">
        <aside class="site-sidebar" id="site-sidebar" aria-label="Menú principal">
            <a href="{{ route('home') }}" class="site-sidebar-brand" aria-label="Ir al inicio de Prodenya">
                <img src="{{ asset('assets/img/SN.png') }}" class="brand-mark brand-mark-image" alt="San Nicolás">
                <span>
                    <span class="brand-name block">PRODENYA</span>
                    <span class="brand-caption block">Gestión con propósito</span>
                </span>
            </a>
            <div class="site-sidebar-rule" aria-hidden="true"></div>

            <div class="site-sidebar-label">Espacios de trabajo</div>
            <nav class="site-sidebar-nav">
                <a href="{{ route('home') }}" class="site-sidebar-link {{ request()->routeIs('home') ? 'is-active' : '' }}">
                    <i class="bi bi-house-door" aria-hidden="true"></i>
                    Inicio
                </a>

                @auth
                    <a href="{{ route('estadisticas') }}" class="site-sidebar-link {{ request()->routeIs('estadisticas', 'dashboard') ? 'is-active' : '' }}">
                        <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                        Estadísticas
                    </a>

                    @if (Route::has('legajos.index') && auth()->user()->tieneRol('admin', 'operador', 'consulta'))
                        <a href="{{ route('legajos.index') }}" class="site-sidebar-link {{ request()->routeIs('legajos.*') ? 'is-active' : '' }}">
                            <i class="bi bi-folder2-open" aria-hidden="true"></i>
                            Legajos
                        </a>
                        <a href="{{ route('mayores18.index') }}" class="site-sidebar-link {{ request()->routeIs('mayores18.*') ? 'is-active' : '' }}">
                            <i class="bi bi-person-check" aria-hidden="true"></i>
                            +18
                        </a>
                    @endif

                    @if (Route::has('mailbox.index') && auth()->user()->tieneRol('admin'))
                        <a href="{{ route('mailbox.index') }}" class="site-sidebar-link {{ request()->routeIs('mailbox.*') ? 'is-active' : '' }}">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            Mailbox
                        </a>
                    @endif

                    @if (Route::has('admin.home') && auth()->user()->tieneRol('admin'))
                        <a href="{{ route('admin.home') }}" class="site-sidebar-link is-admin {{ request()->routeIs('admin.*') ? 'is-active' : '' }}">
                            <i class="bi bi-speedometer2" aria-hidden="true"></i>
                            Administrador
                        </a>
                    @endif
                @endauth
            </nav>

            <div class="site-sidebar-spacer"></div>

            @auth
                @php
                    $sidebarUser = auth()->user();
                    $sidebarTeam = $sidebarUser->currentTeam;
                    $sidebarRole = $sidebarTeam ? $sidebarUser->teamRole($sidebarTeam)?->label() : null;
                    $sidebarInitials = collect(explode(' ', trim($sidebarUser->name)))
                        ->filter()
                        ->take(2)
                        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                        ->join('');
                @endphp
                <details class="site-account site-sidebar-account">
                    <summary class="site-account-trigger site-sidebar-account-trigger" aria-label="Abrir control de perfil">
                        @if ($sidebarUser->avatar)
                            <img src="{{ $sidebarUser->avatar }}" class="site-user-avatar site-user-avatar-image" alt="">
                        @else
                            <span class="site-user-avatar" aria-hidden="true">{{ $sidebarInitials }}</span>
                        @endif
                        <span class="site-user-name">{{ $sidebarUser->name }}</span>
                        <i class="bi bi-chevron-up site-account-chevron" aria-hidden="true"></i>
                    </summary>
                    <div class="site-account-menu">
                        <div class="site-account-menu-header">
                            <div class="site-account-menu-name">{{ $sidebarUser->name }}</div>
                            <div class="site-account-menu-email">{{ $sidebarUser->email }}</div>
                            @if ($sidebarTeam || $sidebarRole)
                                <div class="site-account-menu-meta">
                                    {{ $sidebarTeam?->name ?? 'Sin equipo' }}{{ $sidebarRole ? ' · '.$sidebarRole : '' }}
                                </div>
                            @endif
                        </div>

                        @if (Route::has('profile.edit'))
                            <a href="{{ route('profile.edit') }}" class="site-account-menu-link">
                                <i class="bi bi-person-circle" aria-hidden="true"></i>
                                Mi perfil
                            </a>
                        @endif
                        @if (Route::has('security.edit'))
                            <a href="{{ route('security.edit') }}" class="site-account-menu-link">
                                <i class="bi bi-shield-lock" aria-hidden="true"></i>
                                Seguridad y contraseña
                            </a>
                        @endif

                        <div class="site-account-menu-separator"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="site-account-menu-logout">
                                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </details>
            @endauth
        </aside>
        <div class="site-sidebar-backdrop" data-sidebar-close aria-hidden="true"></div>

        <div class="site-page">
        <header class="site-header">
            <div class="site-header-inner">
                <a href="{{ route('home') }}" class="brand-lockup site-header-brand" aria-label="Ir al inicio de Prodenya">
                    <img src="{{ asset('assets/img/SN.png') }}" class="brand-mark brand-mark-image" alt="San Nicolás">
                    <span class="brand-divider" aria-hidden="true"></span>
                    <span>
                        <span class="brand-name block">PRODENYA</span>
                        <span class="brand-caption block">Promoción y Protección de Derechos</span>
                    </span>
                </a>

                <div class="site-user">
                    @auth
                        @php
                            $accountUser = auth()->user();
                            $accountInitials = collect(explode(' ', trim($accountUser->name)))
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                ->join('');
                        @endphp

                        <button type="button" class="site-user-toggle" data-sidebar-toggle aria-expanded="false" aria-label="Abrir menú lateral">
                            @if ($accountUser->avatar)
                                <img src="{{ $accountUser->avatar }}" class="site-user-avatar site-user-avatar-image" alt="">
                            @else
                                <span class="site-user-avatar" aria-hidden="true">{{ $accountInitials }}</span>
                            @endif
                        </button>

                        @if (Route::has('admin.home') && auth()->user()->tieneRol('admin'))
                            <a href="{{ route('admin.home') }}" class="site-admin-quicklink">
                                <i class="bi bi-shield-lock" aria-hidden="true"></i>
                                <span>Administrador</span>
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="site-nav-link">
                            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                            Ingresar
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="site-main flex-1">
            @if (isset($page))
                <x-inertia::app />
            @else
                @yield('content')
            @endif
        </main>

        <footer class="site-footer">
            <div class="site-footer-inner">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('assets/img/municipalidad_logo.png') }}" class="footer-municipal-logo" alt="Municipalidad de San Nicolás de los Arroyos">
                    <small><strong>PRODENYA</strong> · Promoción y Protección de Derechos</small>
                </div>
                <small>© {{ date('Y') }} · Uso institucional</small>
            </div>
        </footer>
        </div>
    </div>

    @stack('scripts')
    <script>
        (() => {
            const shell = document.getElementById('site-shell');
            const toggle = document.querySelector('[data-sidebar-toggle]');
            const close = document.querySelector('[data-sidebar-close]');

            if (!shell || !toggle || !close) return;

            const setSidebarState = (isOpen) => {
                shell.classList.toggle('sidebar-open', isOpen);
                toggle.setAttribute('aria-expanded', String(isOpen));
                toggle.setAttribute('aria-label', isOpen ? 'Cerrar menú' : 'Abrir menú');
            };
            const closeSidebar = () => setSidebarState(false);
            toggle.addEventListener('click', () => setSidebarState(!shell.classList.contains('sidebar-open')));
            close.addEventListener('click', closeSidebar);
            document.querySelectorAll('.site-sidebar-link').forEach((link) => link.addEventListener('click', closeSidebar));
        })();
    </script>
</body>

</html>

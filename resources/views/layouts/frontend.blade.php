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
    </style>

    @stack('styles')
</head>

<body>
    <div class="site-shell flex flex-col">
        <header class="site-header">
            <div class="site-header-inner">
                <a href="{{ route('home') }}" class="brand-lockup" aria-label="Ir al inicio de Prodenya">
                    <img src="{{ asset('assets/img/SN.png') }}" class="brand-mark brand-mark-image" alt="San Nicolás">
                    <span class="brand-divider" aria-hidden="true"></span>
                    <span>
                        <span class="brand-name block">PRODENYA</span>
                        <span class="brand-caption block">Promoción y Protección de Derechos</span>
                    </span>
                </a>

                <nav class="site-nav" aria-label="Navegación principal">
                    <a href="{{ route('home') }}" class="site-nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}">
                        <i class="bi bi-house-door" aria-hidden="true"></i>
                        Inicio
                    </a>

                    @auth
                        @if (Route::has('personas.create'))
                            <a href="{{ route('personas.create') }}" class="site-nav-link {{ request()->routeIs('personas.*') ? 'is-active' : '' }}">
                                <i class="bi bi-people" aria-hidden="true"></i>
                                Personas
                            </a>
                        @endif

                        @if (Route::has('legajos.create'))
                            <a href="{{ route('legajos.create') }}" class="site-nav-link {{ request()->routeIs('legajos.*') ? 'is-active' : '' }}">
                                <i class="bi bi-folder2-open" aria-hidden="true"></i>
                                Legajos
                            </a>
                        @endif
                    @endauth
                </nav>

                <div class="site-user">
                    @auth
                        @php
                            $accountUser = auth()->user();
                            $accountTeam = $accountUser->currentTeam;
                            $accountRole = $accountTeam ? $accountUser->teamRole($accountTeam)?->label() : null;
                            $accountInitials = collect(explode(' ', trim($accountUser->name)))
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                ->join('');
                        @endphp

                        <details class="site-account">
                            <summary class="site-account-trigger" aria-label="Abrir menú de cuenta">
                                <span class="site-user-name">{{ $accountUser->name }}</span>
                                @if ($accountUser->avatar)
                                    <img src="{{ $accountUser->avatar }}" class="site-user-avatar site-user-avatar-image" alt="">
                                @else
                                    <span class="site-user-avatar" aria-hidden="true">{{ $accountInitials }}</span>
                                @endif
                                <i class="bi bi-chevron-down site-account-chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="site-account-menu">
                                <div class="site-account-menu-header">
                                    <div class="site-account-menu-name">{{ $accountUser->name }}</div>
                                    <div class="site-account-menu-email">{{ $accountUser->email }}</div>
                                    @if ($accountTeam || $accountRole)
                                        <div class="site-account-menu-meta">
                                            {{ $accountTeam?->name ?? 'Sin equipo' }}{{ $accountRole ? ' · '.$accountRole : '' }}
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

    @stack('scripts')
</body>

</html>

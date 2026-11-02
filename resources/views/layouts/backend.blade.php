<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', 'Panel') | PRODENYA
    </title>

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>

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
        }

        body.backend-body {
            background: #f3f8f5;
            color: var(--admin-ink);
        }

        .backend-sidebar {
            background: linear-gradient(180deg, #112f42 0%, #173e50 56%, #1c4b4d 100%) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 18px 0 45px rgba(21, 52, 72, 0.08);
        }

        .backend-sidebar .backend-brand-mark {
            align-items: center;
            background: rgba(156, 224, 191, 0.12);
            border: 1px solid rgba(156, 224, 191, 0.28);
            border-radius: 14px;
            color: #bcebd0;
            display: flex;
            height: 42px;
            justify-content: center;
            width: 42px;
        }

        .backend-sidebar nav a {
            border: 1px solid transparent;
            color: rgba(235, 248, 242, 0.7) !important;
            min-height: 44px;
        }

        .backend-sidebar nav a:hover {
            background: rgba(255, 255, 255, 0.09) !important;
            border-color: rgba(255, 255, 255, 0.1);
            color: #fff !important;
            transform: translateX(2px);
        }

        .backend-sidebar nav a.bg-slate-800 {
            background: linear-gradient(100deg, rgba(156, 224, 191, 0.2), rgba(156, 224, 191, 0.06)) !important;
            border-color: rgba(156, 224, 191, 0.18);
            box-shadow: inset 3px 0 #9ce0bf;
            color: #fff !important;
        }

        .backend-sidebar nav p {
            color: rgba(188, 235, 208, 0.52) !important;
        }

        .backend-sidebar .backend-sidebar-collapse {
            align-items: center;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            color: rgba(235, 248, 242, 0.72);
            display: flex;
            height: 34px;
            justify-content: center;
            transition: background-color 180ms ease, color 180ms ease;
            width: 34px;
        }

        .backend-sidebar .backend-sidebar-collapse:hover {
            background: rgba(156, 224, 191, 0.18);
            color: #fff;
        }

        .backend-sidebar .backend-sidebar-collapse i {
            transition: transform 180ms ease;
        }

        .backend-sidebar .backend-brand-copy,
        .backend-sidebar .backend-sidebar-account {
            transition: opacity 160ms ease;
        }

        .backend-content-shell {
            transition: margin-left 220ms ease;
        }

        @media (min-width: 1024px) {
            body.backend-sidebar-collapsed .backend-sidebar {
                width: 5.5rem !important;
            }

            body.backend-sidebar-collapsed .backend-content-shell {
                margin-left: 5.5rem;
            }

            body.backend-sidebar-collapsed .backend-sidebar > div:first-child {
                justify-content: center;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            body.backend-sidebar-collapsed .backend-sidebar .backend-brand-copy,
            body.backend-sidebar-collapsed .backend-sidebar nav p,
            body.backend-sidebar-collapsed .backend-sidebar nav a > span,
            body.backend-sidebar-collapsed .backend-sidebar .backend-sidebar-account {
                display: none;
            }

            body.backend-sidebar-collapsed .backend-sidebar .backend-sidebar-collapse i {
                transform: rotate(180deg);
            }

            body.backend-sidebar-collapsed .backend-sidebar .backend-sidebar-collapse {
                margin-left: 0;
            }

            body.backend-sidebar-collapsed .backend-sidebar nav {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            body.backend-sidebar-collapsed .backend-sidebar nav a {
                justify-content: center;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
        }

        .backend-topbar {
            background: rgba(255, 255, 255, 0.86) !important;
            border-bottom-color: var(--admin-line) !important;
            box-shadow: 0 8px 24px rgba(21, 52, 72, 0.04);
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(16px);
        }

        .backend-topbar h1 {
            color: var(--admin-ink) !important;
            letter-spacing: -0.035em;
        }

        .backend-main-content {
            background: radial-gradient(circle at 100% 0, rgba(156, 224, 191, 0.12), transparent 28rem);
            min-height: calc(100vh - 5rem);
        }

        .backend-alerts {
            position: relative;
            z-index: 1;
        }

        .backend-icon-button {
            border-color: var(--admin-line) !important;
            color: var(--admin-muted) !important;
        }

        .backend-icon-button:hover {
            background: var(--admin-soft) !important;
            border-color: #b9d4c5 !important;
            color: var(--admin-green-dark) !important;
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
    </style>

</head>


<body class="backend-body bg-slate-100 text-slate-800">

    <div class="backend-shell flex min-h-screen">

        {{-- SIDEBAR --}}
        <aside
            id="backend-sidebar"
            class="backend-sidebar fixed inset-y-0 left-0 z-40 flex w-72 flex-col
                   -translate-x-full lg:translate-x-0 transition-transform duration-200"
        >

            {{-- Logo --}}
            <div
                class="h-20 px-6
                       border-b border-slate-700
                       flex items-center"
            >

                <a
                    href="{{ route('home') }}"
                    class="flex items-center gap-3"
                >

                    <div
                        class="backend-brand-mark
                               bg-blue-600
                               flex items-center justify-center
                               font-bold"
                    >
                        P
                    </div>

                    <div class="backend-brand-copy">
                        <div class="font-bold text-lg">
                            PRODENYA
                        </div>

                        <div class="text-xs text-slate-400">
                            Gestión Integral
                        </div>
                    </div>

                </a>

                <button
                    type="button"
                    id="backend-sidebar-collapse"
                    class="backend-sidebar-collapse ml-auto hidden lg:flex"
                    aria-label="Contraer menú administrativo"
                    aria-expanded="true"
                    title="Contraer menú"
                >
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                </button>

            </div>


            @php
                $usuariosPendientes = auth()->user()?->tieneRol('admin', 'coordinador')
                    ? \App\Models\User::where('estado_cuenta', \App\Models\User::ESTADO_PENDIENTE)->count()
                    : 0;
            @endphp

            <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-6" aria-label="Navegación administrativa">
                @if (auth()->user()?->tieneRol('admin', 'coordinador'))
                    <p class="mb-3 px-3 text-[0.65rem] font-bold tracking-[0.16em] text-slate-500 uppercase">Gestión</p>
                    <a href="{{ route('admin.usuarios.index') }}" title="Usuarios" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.usuarios.*') ? 'bg-slate-800 text-white' : '' }}">
                        <i class="bi bi-person-gear w-5"></i>
                        <span class="flex-1">Usuarios</span>
                        @if ($usuariosPendientes > 0)
                            <span class="rounded-full bg-amber-400 px-2 py-0.5 text-[0.65rem] font-bold text-slate-950">{{ $usuariosPendientes > 99 ? '99+' : $usuariosPendientes }}</span>
                        @endif
                    </a>
                @endif

                @if (auth()->user()?->esAdmin())
                    <p class="mt-7 mb-3 px-3 text-[0.65rem] font-bold tracking-[0.16em] text-slate-500 uppercase">Catálogos</p>

                <a href="{{ route('admin.catalogos.index', ['catalog' => 'roles']) }}" title="Roles" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.catalogos.*') && request()->route('catalog') === 'roles' ? 'bg-slate-800 text-white' : '' }}">
                    <i class="bi bi-shield-lock w-5"></i>
                    <span>Roles</span>
                </a>
                <a href="{{ route('admin.catalogos.index', ['catalog' => 'provincias']) }}" title="Provincias" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->route('catalog') === 'provincias' ? 'bg-slate-800 text-white' : '' }}">
                    <i class="bi bi-map w-5"></i>
                    <span>Provincias</span>
                </a>
                <a href="{{ route('admin.catalogos.index', ['catalog' => 'localidades']) }}" title="Localidades" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->route('catalog') === 'localidades' ? 'bg-slate-800 text-white' : '' }}">
                    <i class="bi bi-geo-alt w-5"></i>
                    <span>Localidades</span>
                </a>
                <a href="{{ route('admin.catalogos.index', ['catalog' => 'barrios']) }}" title="Barrios" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->route('catalog') === 'barrios' ? 'bg-slate-800 text-white' : '' }}">
                    <i class="bi bi-signpost-2 w-5"></i>
                    <span>Barrios</span>
                </a>
                <a href="{{ route('admin.catalogos.index', ['catalog' => 'tipos-documento']) }}" title="Tipos de documento" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->route('catalog') === 'tipos-documento' ? 'bg-slate-800 text-white' : '' }}">
                    <i class="bi bi-card-text w-5"></i>
                    <span>Tipos de documento</span>
                </a>
                <a href="{{ route('admin.catalogos.index', ['catalog' => 'problematicas']) }}" title="Problemáticas" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->route('catalog') === 'problematicas' ? 'bg-slate-800 text-white' : '' }}">
                    <i class="bi bi-journal-medical w-5"></i>
                    <span>Problemáticas</span>
                </a>
                @endif
            </nav>


            {{-- Usuario --}}
            @auth

                <div
                    class="backend-sidebar-account border-t border-slate-700
                           px-5 py-4"
                >

                    <div class="text-sm font-medium">
                        {{ auth()->user()->name }}
                    </div>

                    <div
                        class="text-xs text-slate-400
                               truncate mt-1"
                    >
                        {{ auth()->user()->email }}
                    </div>

                </div>

            @endauth

        </aside>

        <div
            id="backend-sidebar-backdrop"
            class="fixed inset-0 z-30 hidden bg-slate-950/40 lg:hidden"
        ></div>


        {{-- CONTENIDO DERECHO --}}
        <div class="backend-content-shell flex-1 lg:ml-72">

            {{-- Navbar superior --}}
            <header
                class="backend-topbar flex h-20 items-center justify-between
                       border-b border-slate-200
                       px-5 md:px-8"
            >

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        id="backend-menu-toggle"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 transition hover:bg-slate-50 lg:hidden"
                        aria-label="Abrir menú administrativo"
                        aria-expanded="false"
                        aria-controls="backend-sidebar"
                    >
                        <i class="bi bi-list text-lg"></i>
                    </button>

                    <div>

                    <h1 class="font-semibold text-xl text-slate-900">
                        @yield('page-title', 'Panel')
                    </h1>

                    @hasSection('page-description')

                        <p class="text-sm text-slate-500">
                            @yield('page-description')
                        </p>

                    @endif

                    </div>
                </div>


                <div class="flex items-center gap-4">

                    {{-- Notificaciones --}}
                    <button
                        type="button"
                        class="backend-icon-button flex h-10 w-10 items-center justify-center
                               rounded-xl border border-slate-200
                               hover:bg-slate-50 transition"
                    >
                        <i class="bi bi-bell"></i>
                    </button>


                    @auth

                        <div
                            class="hidden md:flex
                                   items-center gap-3"
                        >

                            <div
                                class="w-10 h-10
                                       rounded-full
                                       bg-slate-200
                                       flex items-center
                                       justify-center
                                       font-semibold"
                            >
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>

                            <div>
                                <div
                                    class="text-sm font-medium
                                           text-slate-800"
                                >
                                    {{ auth()->user()->name }}
                                </div>

                                <div class="text-xs text-slate-500">
                                    Usuario
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
            <main class="backend-main-content p-5 md:p-8">

                @yield('content')

            </main>

        </div>

    </div>


    @stack('scripts')
    <script>
        (() => {
            const sidebar = document.getElementById('backend-sidebar');
            const toggle = document.getElementById('backend-menu-toggle');
            const backdrop = document.getElementById('backend-sidebar-backdrop');
            const collapseToggle = document.getElementById('backend-sidebar-collapse');

            if (!sidebar || !toggle || !backdrop) return;

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
                    window.localStorage.setItem(collapsedStorageKey, String(shouldCollapse));
                }
            };

            const setSidebarState = (isOpen) => {
                sidebar.classList.toggle('-translate-x-full', !isOpen);
                backdrop.classList.toggle('hidden', !isOpen);
                toggle.setAttribute('aria-expanded', String(isOpen));
                document.body.classList.toggle('overflow-hidden', isOpen);
            };

            const closeSidebar = () => {
                setSidebarState(false);
            };

            toggle.addEventListener('click', () => setSidebarState(sidebar.classList.contains('-translate-x-full')));
            backdrop.addEventListener('click', closeSidebar);
            sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeSidebar));
            collapseToggle?.addEventListener('click', () => {
                setCollapsed(!document.body.classList.contains('backend-sidebar-collapsed'));
            });
            setCollapsed(window.localStorage.getItem(collapsedStorageKey) === 'true', false);
            window.addEventListener('resize', () => {
                if (!isDesktop()) {
                    document.body.classList.remove('backend-sidebar-collapsed');
                    return;
                }

                setCollapsed(window.localStorage.getItem(collapsedStorageKey) === 'true', false);
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeSidebar();
            });
        })();
    </script>

</body>

</html>

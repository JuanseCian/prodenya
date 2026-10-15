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

</head>


<body class="bg-slate-100 text-slate-800">

    <div class="min-h-screen flex">

        {{-- SIDEBAR --}}
        <aside
            id="backend-sidebar"
            class="flex w-72 bg-slate-900 text-white flex-col fixed inset-y-0 left-0 z-40
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
                        class="w-10 h-10 rounded-xl
                               bg-blue-600
                               flex items-center justify-center
                               font-bold"
                    >
                        P
                    </div>

                    <div>
                        <div class="font-bold text-lg">
                            PRODENYA
                        </div>

                        <div class="text-xs text-slate-400">
                            Gestión Integral
                        </div>
                    </div>

                </a>

            </div>


            <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-6" aria-label="Navegación administrativa">
                <p class="mb-3 px-3 text-[0.65rem] font-bold tracking-[0.16em] text-slate-500 uppercase">Catálogos</p>

                <a href="{{ route('admin.catalogos.index', ['catalog' => 'roles']) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.catalogos.*') && request()->route('catalog') === 'roles' ? 'bg-slate-800 text-white' : '' }}">
                    <i class="bi bi-shield-lock w-5"></i>
                    Roles
                </a>
                <a href="{{ route('admin.catalogos.index', ['catalog' => 'provincias']) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->route('catalog') === 'provincias' ? 'bg-slate-800 text-white' : '' }}">
                    <i class="bi bi-map w-5"></i>
                    Provincias
                </a>
                <a href="{{ route('admin.catalogos.index', ['catalog' => 'localidades']) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->route('catalog') === 'localidades' ? 'bg-slate-800 text-white' : '' }}">
                    <i class="bi bi-geo-alt w-5"></i>
                    Localidades
                </a>
                <a href="{{ route('admin.catalogos.index', ['catalog' => 'barrios']) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->route('catalog') === 'barrios' ? 'bg-slate-800 text-white' : '' }}">
                    <i class="bi bi-signpost-2 w-5"></i>
                    Barrios
                </a>
                <a href="{{ route('admin.catalogos.index', ['catalog' => 'tipos-documento']) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->route('catalog') === 'tipos-documento' ? 'bg-slate-800 text-white' : '' }}">
                    <i class="bi bi-card-text w-5"></i>
                    Tipos de documento
                </a>
                <a href="{{ route('admin.catalogos.index', ['catalog' => 'motivos-ingreso']) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->route('catalog') === 'motivos-ingreso' ? 'bg-slate-800 text-white' : '' }}">
                    <i class="bi bi-box-arrow-in-right w-5"></i>
                    Motivos de ingreso
                </a>
                <a href="{{ route('admin.catalogos.index', ['catalog' => 'problematicas']) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->route('catalog') === 'problematicas' ? 'bg-slate-800 text-white' : '' }}">
                    <i class="bi bi-journal-medical w-5"></i>
                    Problemáticas
                </a>
            </nav>


            {{-- Usuario --}}
            @auth

                <div
                    class="border-t border-slate-700
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
        <div class="flex-1 lg:ml-72">

            {{-- Navbar superior --}}
            <header
                class="h-20 bg-white
                       border-b border-slate-200
                       flex items-center
                       justify-between
                       px-5 md:px-8"
            >

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        id="backend-menu-toggle"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 transition hover:bg-slate-50 lg:hidden"
                        aria-label="Abrir menú administrativo"
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
                        class="w-10 h-10
                               flex items-center justify-center
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
            <div class="px-5 md:px-8 pt-5">

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
            <main class="p-5 md:p-8">

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

            if (!sidebar || !toggle || !backdrop) return;

            const closeSidebar = () => {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            };

            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                backdrop.classList.toggle('hidden');
            });
            backdrop.addEventListener('click', closeSidebar);
        })();
    </script>

</body>

</html>

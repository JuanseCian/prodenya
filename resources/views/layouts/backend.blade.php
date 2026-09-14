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
            class="hidden lg:flex
                   w-64 bg-slate-900 text-white
                   flex-col fixed inset-y-0 left-0"
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


            {{-- Navegación --}}
            <nav class="flex-1 px-4 py-6 space-y-1">

                <a
                    href="#"
                    class="flex items-center gap-3
                           px-4 py-3 rounded-xl
                           bg-slate-800 text-white"
                >
                    <i class="bi bi-grid"></i>

                    <span>
                        Dashboard
                    </span>
                </a>


                <a
                    href="#"
                    class="flex items-center gap-3
                           px-4 py-3 rounded-xl
                           text-slate-300
                           hover:bg-slate-800
                           hover:text-white transition"
                >
                    <i class="bi bi-folder2-open"></i>

                    <span>
                        Legajos
                    </span>
                </a>


                <a
                    href="#"
                    class="flex items-center gap-3
                           px-4 py-3 rounded-xl
                           text-slate-300
                           hover:bg-slate-800
                           hover:text-white transition"
                >
                    <i class="bi bi-people"></i>

                    <span>
                        Personas
                    </span>
                </a>


                <a
                    href="#"
                    class="flex items-center gap-3
                           px-4 py-3 rounded-xl
                           text-slate-300
                           hover:bg-slate-800
                           hover:text-white transition"
                >
                    <i class="bi bi-journal-text"></i>

                    <span>
                        Intervenciones
                    </span>
                </a>


                <a
                    href="#"
                    class="flex items-center gap-3
                           px-4 py-3 rounded-xl
                           text-slate-300
                           hover:bg-slate-800
                           hover:text-white transition"
                >
                    <i class="bi bi-file-earmark-text"></i>

                    <span>
                        Oficios
                    </span>
                </a>


                <a
                    href="#"
                    class="flex items-center gap-3
                           px-4 py-3 rounded-xl
                           text-slate-300
                           hover:bg-slate-800
                           hover:text-white transition"
                >
                    <i class="bi bi-arrow-left-right"></i>

                    <span>
                        Derivaciones
                    </span>
                </a>


                <a
                    href="#"
                    class="flex items-center gap-3
                           px-4 py-3 rounded-xl
                           text-slate-300
                           hover:bg-slate-800
                           hover:text-white transition"
                >
                    <i class="bi bi-bar-chart"></i>

                    <span>
                        Estadísticas
                    </span>
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



        {{-- CONTENIDO DERECHO --}}
        <div class="flex-1 lg:ml-64">

            {{-- Navbar superior --}}
            <header
                class="h-20 bg-white
                       border-b border-slate-200
                       flex items-center
                       justify-between
                       px-5 md:px-8"
            >

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

</body>

</html>
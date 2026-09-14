<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', 'PRODENYA')
    </title>

    <meta
        name="description"
        content="@yield('description', 'Sistema de Promoción y Protección de los Derechos de Niños, Niñas y Adolescentes')"
    >

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Bootstrap Icons --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    @stack('styles')
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

    {{-- Navbar --}}
    <header class="bg-white border-b border-slate-200">

        <div
            class="max-w-7xl mx-auto px-5 md:px-6 py-4
                   flex items-center justify-between gap-4"
        >

            {{-- Marca --}}
            <a
                href="{{ route('home') }}"
                class="flex items-center gap-3"
            >

                <div
                    class="w-11 h-11 rounded-xl
                           bg-blue-600 text-white
                           flex items-center justify-center
                           font-bold text-lg"
                >
                    P
                </div>

                <div>
                    <h1
                        class="font-bold text-lg md:text-xl
                               text-slate-900 leading-tight"
                    >
                        PRODENYA
                    </h1>

                    <p class="hidden sm:block text-xs text-slate-500">
                        Promoción y Protección de Derechos
                    </p>
                </div>

            </a>


            {{-- Acciones --}}
            <nav class="flex items-center gap-3">

                @auth

                    @if (Route::has('dashboard'))
                        <a
                            href="{{ route('dashboard') }}"
                            class="px-4 py-2 rounded-lg
                                   bg-blue-600 text-white
                                   text-sm font-medium
                                   hover:bg-blue-700 transition"
                        >
                            Ir al sistema
                        </a>
                    @endif

                @else

                    @if (Route::has('login'))

                        <a
                            href="{{ route('login') }}"
                            class="px-4 py-2 rounded-lg
                                   border border-slate-300
                                   bg-white text-slate-700
                                   text-sm font-medium
                                   hover:bg-slate-100 transition"
                        >
                            Iniciar sesión
                        </a>

                    @endif

                @endauth

            </nav>

        </div>

    </header>


    {{-- Contenido --}}
    <main class="flex-1">
        @yield('content')
    </main>


    {{-- Footer --}}
    <footer class="bg-white border-t border-slate-200">

        <div
            class="max-w-7xl mx-auto px-6 py-6
                   text-sm text-slate-500
                   flex flex-col md:flex-row
                   justify-between gap-2"
        >

            <span>
                PRODENYA · Promoción y Protección de Derechos
            </span>

            <span>
                © {{ date('Y') }}
            </span>

        </div>

    </footer>


    @stack('scripts')

</body>

</html>
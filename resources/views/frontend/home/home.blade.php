@extends('layouts.frontend')

@section('title', 'Inicio | PRODENYA')

@section(
    'description',
    'Sistema integral de Promoción y Protección de los Derechos de Niños, Niñas y Adolescentes'
)


@section('content')

    {{-- HERO --}}
    <section class="py-20 md:py-28">

        <div class="max-w-7xl mx-auto px-6">

            <div class="max-w-3xl">

                <span
                    class="inline-flex items-center gap-2
                           mb-5 px-3 py-1.5
                           bg-blue-50 text-blue-700
                           border border-blue-100
                           rounded-full
                           text-sm font-medium"
                >

                    <i class="bi bi-shield-check"></i>

                    Sistema Integral de Gestión

                </span>


                <h2
                    class="text-4xl md:text-6xl
                           font-bold tracking-tight
                           text-slate-900"
                >

                    Protección de derechos con una gestión más
                    <span class="text-blue-600">
                        organizada e integral.
                    </span>

                </h2>


                <p
                    class="mt-6
                           text-lg md:text-xl
                           leading-8
                           text-slate-600
                           max-w-2xl"
                >

                    Plataforma destinada al registro, seguimiento
                    y gestión de situaciones vinculadas a la
                    promoción y protección de los derechos de
                    niños, niñas y adolescentes.

                </p>


                <div class="mt-9 flex flex-wrap gap-3">

                    @auth

                        @if (Route::has('dashboard'))

                            <a
                                href="{{ route('dashboard') }}"
                                class="inline-flex items-center gap-2
                                       px-6 py-3
                                       rounded-xl
                                       bg-blue-600
                                       text-white
                                       font-medium
                                       hover:bg-blue-700
                                       transition"
                            >

                                Ingresar al sistema

                                <i class="bi bi-arrow-right"></i>

                            </a>

                        @endif

                    @else

                        @if (Route::has('login'))

                            <a
                                href="{{ route('login') }}"
                                class="inline-flex items-center gap-2
                                       px-6 py-3
                                       rounded-xl
                                       bg-blue-600
                                       text-white
                                       font-medium
                                       hover:bg-blue-700
                                       transition"
                            >

                                Iniciar sesión

                                <i class="bi bi-box-arrow-in-right"></i>

                            </a>

                        @endif

                    @endauth

                </div>

            </div>

        </div>

    </section>



    {{-- MÓDULOS --}}
    <section class="pb-24">

        <div class="max-w-7xl mx-auto px-6">

            <div class="mb-10">

                <p
                    class="text-sm font-semibold
                           uppercase tracking-wider
                           text-blue-600"
                >
                    Plataforma
                </p>

                <h3
                    class="mt-2
                           text-3xl font-bold
                           text-slate-900"
                >
                    Gestión centralizada
                </h3>

                <p
                    class="mt-3
                           text-slate-500
                           max-w-2xl"
                >
                    Información, actuaciones y seguimiento
                    concentrados en una única plataforma.
                </p>

            </div>


            <div
                class="grid
                       grid-cols-1
                       md:grid-cols-2
                       lg:grid-cols-3
                       gap-5"
            >


                {{-- Legajos --}}
                <div
                    class="bg-white
                           border border-slate-200
                           rounded-2xl
                           p-6
                           hover:shadow-md
                           hover:-translate-y-1
                           transition"
                >

                    <div
                        class="w-12 h-12
                               mb-5
                               rounded-xl
                               bg-blue-50
                               text-blue-600
                               flex items-center
                               justify-center
                               text-xl"
                    >
                        <i class="bi bi-folder2-open"></i>
                    </div>

                    <h4 class="font-semibold text-lg">
                        Legajos
                    </h4>

                    <p
                        class="mt-2
                               text-sm leading-6
                               text-slate-500"
                    >
                        Registro y seguimiento de los legajos
                        vinculados a cada niño, niña o adolescente.
                    </p>

                </div>



                {{-- Intervenciones --}}
                <div
                    class="bg-white
                           border border-slate-200
                           rounded-2xl
                           p-6
                           hover:shadow-md
                           hover:-translate-y-1
                           transition"
                >

                    <div
                        class="w-12 h-12
                               mb-5
                               rounded-xl
                               bg-emerald-50
                               text-emerald-600
                               flex items-center
                               justify-center
                               text-xl"
                    >
                        <i class="bi bi-journal-text"></i>
                    </div>

                    <h4 class="font-semibold text-lg">
                        Intervenciones
                    </h4>

                    <p
                        class="mt-2
                               text-sm leading-6
                               text-slate-500"
                    >
                        Registro cronológico de las actuaciones
                        realizadas por los equipos profesionales.
                    </p>

                </div>



                {{-- Oficios --}}
                <div
                    class="bg-white
                           border border-slate-200
                           rounded-2xl
                           p-6
                           hover:shadow-md
                           hover:-translate-y-1
                           transition"
                >

                    <div
                        class="w-12 h-12
                               mb-5
                               rounded-xl
                               bg-amber-50
                               text-amber-600
                               flex items-center
                               justify-center
                               text-xl"
                    >
                        <i class="bi bi-file-earmark-text"></i>
                    </div>

                    <h4 class="font-semibold text-lg">
                        Oficios
                    </h4>

                    <p
                        class="mt-2
                               text-sm leading-6
                               text-slate-500"
                    >
                        Recepción y seguimiento de documentación
                        y comunicaciones institucionales.
                    </p>

                </div>



                {{-- Derivaciones --}}
                <div
                    class="bg-white
                           border border-slate-200
                           rounded-2xl
                           p-6
                           hover:shadow-md
                           hover:-translate-y-1
                           transition"
                >

                    <div
                        class="w-12 h-12
                               mb-5
                               rounded-xl
                               bg-violet-50
                               text-violet-600
                               flex items-center
                               justify-center
                               text-xl"
                    >
                        <i class="bi bi-arrow-left-right"></i>
                    </div>

                    <h4 class="font-semibold text-lg">
                        Derivaciones
                    </h4>

                    <p
                        class="mt-2
                               text-sm leading-6
                               text-slate-500"
                    >
                        Distribución y asignación de situaciones
                        entre profesionales y equipos.
                    </p>

                </div>



                {{-- Seguimiento --}}
                <div
                    class="bg-white
                           border border-slate-200
                           rounded-2xl
                           p-6
                           hover:shadow-md
                           hover:-translate-y-1
                           transition"
                >

                    <div
                        class="w-12 h-12
                               mb-5
                               rounded-xl
                               bg-rose-50
                               text-rose-600
                               flex items-center
                               justify-center
                               text-xl"
                    >
                        <i class="bi bi-search"></i>
                    </div>

                    <h4 class="font-semibold text-lg">
                        Seguimiento
                    </h4>

                    <p
                        class="mt-2
                               text-sm leading-6
                               text-slate-500"
                    >
                        Estado actual, responsables, actuaciones
                        pendientes y evolución de cada situación.
                    </p>

                </div>



                {{-- Estadísticas --}}
                <div
                    class="bg-white
                           border border-slate-200
                           rounded-2xl
                           p-6
                           hover:shadow-md
                           hover:-translate-y-1
                           transition"
                >

                    <div
                        class="w-12 h-12
                               mb-5
                               rounded-xl
                               bg-cyan-50
                               text-cyan-600
                               flex items-center
                               justify-center
                               text-xl"
                    >
                        <i class="bi bi-bar-chart"></i>
                    </div>

                    <h4 class="font-semibold text-lg">
                        Estadísticas
                    </h4>

                    <p
                        class="mt-2
                               text-sm leading-6
                               text-slate-500"
                    >
                        Indicadores para el análisis institucional
                        y planificación de acciones.
                    </p>

                </div>

            </div>

        </div>

    </section>

@endsection
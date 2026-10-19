@extends('layouts.frontend')

@section('title', 'Inicio | PRODENYA')

@section('description', 'PRODENYA · Promoción y Protección de Derechos')

@section('content')
    @php
        $homeName = trim(auth()->user()->name ?? '');
        $homeFirstName = explode(' ', $homeName)[0] ?: 'equipo';
    @endphp

    <div class="home-page">
        @if (session('status'))
            <div class="home-container home-status-alert" role="status">
                <i class="bi bi-check-circle" aria-hidden="true"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <main class="home-container home-content">
            <section class="home-intro">
                <div>
                    <p class="home-brand">PRODENYA</p>
                    <h1>Hola, <span>{{ $homeFirstName }}</span></h1>
                    <p class="home-copy">Este es tu espacio de trabajo para consultar legajos, cargar información y seguir cada situación.</p>
                </div>
                <div class="home-intro-note">
                    <p>Promoción y Protección de Derechos</p>
                    <strong>El trabajo de cada día, más ordenado.</strong>
                </div>
            </section>

            <section class="home-work" aria-labelledby="home-work-title">
                <div class="home-work-heading">
                    <div>
                        <p class="home-section-label">Accesos rápidos</p>
                        <h2 id="home-work-title">Empezá por acá</h2>
                    </div>
                    <p>Elegí una acción para continuar.</p>
                </div>

                <nav class="home-links" aria-label="Acciones principales">
                    @if (Route::has('legajos.index'))
                        <a href="{{ route('legajos.index') }}" class="home-link">
                            <i class="bi bi-folder2-open" aria-hidden="true"></i>
                            <span>
                                <strong>Consultar legajos</strong>
                                <small>Buscá y revisá la información existente.</small>
                            </span>
                            <i class="bi bi-arrow-right home-link-arrow" aria-hidden="true"></i>
                        </a>
                    @endif
                    @if (Route::has('legajos.create'))
                        <a href="{{ route('legajos.create') }}" class="home-link">
                            <i class="bi bi-folder-plus" aria-hidden="true"></i>
                            <span>
                                <strong>Nuevo legajo</strong>
                                <small>Registrá una nueva situación.</small>
                            </span>
                            <i class="bi bi-arrow-right home-link-arrow" aria-hidden="true"></i>
                        </a>
                    @endif
                    @if (Route::has('estadisticas'))
                        <a href="{{ route('estadisticas') }}" class="home-link">
                            <i class="bi bi-bar-chart" aria-hidden="true"></i>
                            <span>
                                <strong>Estadísticas</strong>
                                <small>Consultá los datos generales de trabajo.</small>
                            </span>
                            <i class="bi bi-arrow-right home-link-arrow" aria-hidden="true"></i>
                        </a>
                    @endif
                </nav>
            </section>
        </main>
    </div>

    <style>
        .home-page { background: #f4f7fb; color: #173247; min-height: calc(100vh - 164px); overflow: hidden; }
        .home-container { margin: 0 auto; max-width: 1180px; padding-left: 24px; padding-right: 24px; }
        .home-content { padding-bottom: 100px; }
        .home-status-alert { align-items: center; background: #e8f7ef; border: 1px solid #bde3cb; border-radius: 12px; box-shadow: 0 8px 20px rgba(35, 113, 72, 0.07); color: #267148; display: flex; font-size: 0.85rem; font-weight: 700; gap: 9px; margin-top: 20px; padding: 12px 15px; }
        .home-intro { align-items: end; display: flex; gap: 60px; justify-content: space-between; padding-bottom: 68px; padding-top: 82px; position: relative; }
        .home-intro::after { background: #e8ecff; border-radius: 50%; content: ''; height: 280px; pointer-events: none; position: absolute; right: -125px; top: 10px; width: 280px; z-index: 0; }
        .home-intro > div { position: relative; z-index: 1; }
        .home-intro > div:first-child { max-width: 570px; }
        .home-brand, .home-section-label { color: var(--brand); font-size: 0.75rem; font-weight: 800; letter-spacing: 0.16em; margin: 0; text-transform: uppercase; }
        .home-brand { background: #eeecf7; border: 1px solid #ddd9f5; border-radius: 999px; display: inline-flex; padding: 8px 12px; }
        .home-intro h1 { color: #173247; font-size: clamp(2.2rem, 4vw, 3.5rem); font-weight: 800; letter-spacing: -0.055em; line-height: 1.05; margin: 18px 0 0; }
        .home-intro h1 span { color: #6c63c7; }
        .home-copy { color: #5d7180; font-size: 1rem; line-height: 1.6; margin: 18px 0 0; max-width: 500px; }
        .home-intro-note { border-left: 2px solid #a9d5df; max-width: 245px; padding: 5px 0 5px 18px; }
        .home-intro-note p { color: var(--brand); font-size: 0.7rem; font-weight: 800; letter-spacing: 0.08em; line-height: 1.4; margin: 0; text-transform: uppercase; }
        .home-intro-note strong { color: #3a5667; display: block; font-size: 0.9rem; font-weight: 600; line-height: 1.45; margin-top: 9px; }
        .home-work { border-top: 1px solid #dce7ed; max-width: 900px; padding: 29px 0 0; }
        .home-work-heading { align-items: end; display: flex; gap: 25px; justify-content: space-between; }
        .home-work h2 { color: #173247; font-size: 1.35rem; font-weight: 700; letter-spacing: -0.025em; margin: 8px 0 17px; }
        .home-work-heading > p { color: #718594; font-size: 0.8rem; margin: 0 0 18px; }
        .home-links { display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .home-link { align-items: center; background: #fff; border: 1px solid #dce7ed; border-radius: 14px; box-shadow: 0 7px 18px rgba(37, 65, 84, 0.04); color: #2c4a5e; display: flex; gap: 13px; min-height: 96px; padding: 17px; text-decoration: none; transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease; }
        .home-link:hover { border-color: #b6b0eb; box-shadow: 0 12px 24px rgba(81, 75, 159, 0.11); color: #514b9f; transform: translateY(-3px); }
        .home-link > i:first-child { align-items: center; background: #eeecf7; border-radius: 11px; color: var(--brand); display: flex; flex-shrink: 0; font-size: 1.1rem; height: 40px; justify-content: center; width: 40px; }
        .home-link span { display: flex; flex: 1; flex-direction: column; gap: 3px; }
        .home-link strong { font-size: 0.9rem; font-weight: 700; }
        .home-link small { color: #718594; font-size: 0.76rem; line-height: 1.35; }
        .home-link-arrow { color: #8fa4b1; font-size: 0.8rem; margin-left: auto; }
        .dark .home-page { background: #0d1620; color: #edf6fb; }
        .dark .home-intro h1, .dark .home-work h2 { color: #edf6fb; }
        .dark .home-copy, .dark .home-work-heading > p { color: #9bb2c1; }
        .dark .home-intro-note { border-color: #3b7188; }
        .dark .home-intro-note strong { color: #d9e7ee; }
        .dark .home-work { border-color: #294252; }
        .dark .home-link { background: #152532; border-color: #294252; color: #d9e7ee; }
        .dark .home-link > i:first-child { background: #2a294d; color: #aaa4e5; }
        .dark .home-link:hover { color: #8ed4f5; }
        .dark .home-link small { color: #9bb2c1; }
        .dark .home-link-arrow { color: #7192a3; }
        @media (max-width: 560px) {
            .home-container { padding-left: 16px; padding-right: 16px; }
            .home-intro { align-items: flex-start; flex-direction: column; gap: 34px; padding-bottom: 58px; padding-top: 72px; }
            .home-intro h1 { font-size: 2.35rem; }
            .home-intro-note { max-width: 290px; }
            .home-work-heading { align-items: flex-start; flex-direction: column; gap: 0; }
            .home-work-heading > p { margin: -8px 0 17px; }
            .home-links { display: block; }
            .home-link + .home-link { margin-top: 10px; }
        }
    </style>
@endsection

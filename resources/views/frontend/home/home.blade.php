@extends('layouts.frontend')

@section('title', 'Inicio | PRODENYA')

@section('description', 'Sistema integral de Promoción y Protección de los Derechos de Niños, Niñas y Adolescentes')

@section('content')
    <div class="home-page">
        @if (session('status'))
            <div class="home-container home-status-alert" role="status">
                <i class="bi bi-check-circle" aria-hidden="true"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <section class="home-hero">
            <div class="home-hero-glow home-hero-glow-one" aria-hidden="true"></div>
            <div class="home-hero-glow home-hero-glow-two" aria-hidden="true"></div>

            <div class="home-container home-hero-grid">
                <div class="home-hero-copy">
                    <span class="home-eyebrow">
                        <span class="home-eyebrow-dot" aria-hidden="true"></span>
                        Plataforma institucional
                    </span>

                    <h1>
                        Un espacio para ordenar el trabajo y
                        <span>cuidar cada historia.</span>
                    </h1>

                    <p class="home-hero-lead">
                        PRODENYA reúne legajos y documentación en un mismo lugar para acompañar
                        cada situación con información clara y seguimiento responsable.
                    </p>

                    <div class="home-actions">
                        @if (Route::has('legajos.create'))
                            <a href="{{ route('legajos.create') }}" class="home-button home-button-primary">
                                <i class="bi bi-folder-plus" aria-hidden="true"></i>
                                Nuevo legajo
                                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="home-hero-panel" aria-label="Resumen de la plataforma">
                    <div class="home-panel-orbit home-panel-orbit-one" aria-hidden="true"></div>
                    <div class="home-panel-orbit home-panel-orbit-two" aria-hidden="true"></div>
                    <div class="home-panel-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></div>
                    <p class="home-panel-kicker">Gestión con propósito</p>
                    <h2>Información que acompaña decisiones.</h2>
                    <p>Un recorrido simple para registrar, consultar y dar seguimiento.</p>

                    <div class="home-panel-status">
                        <span class="home-status-indicator" aria-hidden="true"></span>
                        <span>Sistema disponible</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="home-container home-section" aria-labelledby="areas-title">
            <div class="home-section-heading">
                <div>
                    <span class="home-section-kicker">Áreas de trabajo</span>
                    <h2 id="areas-title">Todo lo que necesitás, más cerca</h2>
                </div>
                <p>Accesos rápidos para organizar la información y continuar el trabajo del equipo.</p>
            </div>

            <div class="home-area-grid">
                @if (Route::has('legajos.index'))
                    <a href="{{ route('legajos.index') }}" class="home-area-card home-area-card-blue">
                        <span class="home-area-icon"><i class="bi bi-folder2-open" aria-hidden="true"></i></span>
                        <span class="home-area-content">
                            <strong>Legajos</strong>
                            <span>Consultá y buscá los legajos abiertos.</span>
                        </span>
                        <i class="bi bi-arrow-up-right home-area-arrow" aria-hidden="true"></i>
                    </a>
                @endif

                <div class="home-area-card home-area-card-cyan">
                    <span class="home-area-icon"><i class="bi bi-journal-check" aria-hidden="true"></i></span>
                    <span class="home-area-content">
                        <strong>Intervenciones</strong>
                        <span>Registro ordenado de las actuaciones de cada equipo.</span>
                    </span>
                    <span class="home-area-tag">Próximamente</span>
                </div>

                <div class="home-area-card home-area-card-green">
                    <span class="home-area-icon"><i class="bi bi-journal-check" aria-hidden="true"></i></span>
                    <span class="home-area-content">
                        <strong>Seguimiento</strong>
                        <span>Consultá el estado y la evolución de cada intervención.</span>
                    </span>
                    <span class="home-area-tag">Próximamente</span>
                </div>

                <div class="home-area-card home-area-card-amber">
                    <span class="home-area-icon"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
                    <span class="home-area-content">
                        <strong>Documentación</strong>
                        <span>Centralizá archivos y comunicaciones institucionales.</span>
                    </span>
                    <span class="home-area-tag">Próximamente</span>
                </div>

                <div class="home-area-card home-area-card-rose">
                    <span class="home-area-icon"><i class="bi bi-bar-chart" aria-hidden="true"></i></span>
                    <span class="home-area-content">
                        <strong>Indicadores</strong>
                        <span>Información para planificar acciones con perspectiva.</span>
                    </span>
                    <span class="home-area-tag">Próximamente</span>
                </div>
            </div>
        </section>

        <section class="home-container home-note" aria-label="Mensaje institucional">
            <div class="home-note-icon"><i class="bi bi-heart-pulse" aria-hidden="true"></i></div>
            <div>
                <strong>Una herramienta para trabajar mejor.</strong>
                <p>La información ordenada permite que cada equipo pueda concentrarse en lo más importante: acompañar.</p>
            </div>
        </section>
    </div>

    <style>
        .home-page { color: var(--ink); overflow: hidden; }
        .home-container { margin: 0 auto; max-width: 1180px; padding-left: 24px; padding-right: 24px; }
        .home-status-alert { align-items: center; background: #e8f7ef; border: 1px solid #bde3cb; border-radius: 12px; color: #267148; display: flex; font-size: 0.88rem; font-weight: 700; gap: 10px; margin-top: 20px; padding: 13px 16px; }
        .home-status-alert i { font-size: 1.05rem; }

        .home-hero {
            background: linear-gradient(135deg, #f3f1ff 0%, #f8f9ff 54%, #edf7fc 100%);
            min-height: 450px;
            overflow: hidden;
            padding: 70px 0 76px;
            position: relative;
        }

        .home-hero::after {
            background: linear-gradient(90deg, rgba(108, 99, 199, 0.08), transparent);
            border-radius: 50%;
            content: "";
            height: 470px;
            position: absolute;
            right: -160px;
            top: -210px;
            transform: rotate(-28deg);
            width: 570px;
        }

        .home-hero-grid { align-items: center; display: grid; gap: 70px; grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr); position: relative; z-index: 1; }
        .home-hero-copy { max-width: 680px; }
        .home-eyebrow, .home-section-kicker { color: var(--brand); font-size: 0.77rem; font-weight: 800; letter-spacing: 0.13em; text-transform: uppercase; }
        .home-eyebrow { align-items: center; background: rgba(108, 99, 199, 0.09); border: 1px solid rgba(108, 99, 199, 0.14); border-radius: 999px; display: inline-flex; gap: 8px; padding: 8px 13px; }
        .home-eyebrow-dot { background: #70c89a; border-radius: 50%; box-shadow: 0 0 0 4px rgba(112, 200, 154, 0.14); height: 7px; width: 7px; }
        .home-hero h1 { color: #252b43; font-size: clamp(2.4rem, 5vw, 4.3rem); font-weight: 800; letter-spacing: -0.055em; line-height: 1.06; margin: 22px 0 0; max-width: 760px; }
        .home-hero h1 span { color: var(--brand); display: block; }
        .home-hero-lead { color: var(--muted); font-size: 1.05rem; line-height: 1.8; margin: 22px 0 0; max-width: 620px; }
        .home-actions { display: flex; flex-wrap: wrap; gap: 11px; margin-top: 31px; }
        .home-button { align-items: center; border-radius: 11px; display: inline-flex; font-size: 0.9rem; font-weight: 700; gap: 9px; padding: 13px 18px; text-decoration: none; transition: all 180ms ease; }
        .home-button-primary { background: var(--brand); box-shadow: 0 9px 18px rgba(108, 99, 199, 0.25); color: #fff; }
        .home-button-primary:hover { background: var(--brand-dark); box-shadow: 0 12px 24px rgba(108, 99, 199, 0.3); color: #fff; transform: translateY(-2px); }
        .home-button-light { background: #fff; border: 1px solid #e0e3f1; color: var(--brand-dark); }
        .home-button-light:hover { border-color: var(--brand); color: var(--brand-dark); transform: translateY(-2px); }

        .home-hero-panel { background: linear-gradient(145deg, var(--brand-dark), var(--brand-deep)); border-radius: 27px; box-shadow: 0 24px 50px rgba(65, 59, 128, 0.25); color: #fff; min-height: 305px; overflow: hidden; padding: 34px; position: relative; }
        .home-hero-panel::before { border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 50%; content: ""; height: 280px; position: absolute; right: -120px; top: -90px; width: 280px; }
        .home-panel-icon { align-items: center; background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 15px; display: flex; font-size: 1.55rem; height: 55px; justify-content: center; position: relative; width: 55px; }
        .home-panel-kicker { color: rgba(255, 255, 255, 0.66); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.14em; margin: 33px 0 0; text-transform: uppercase; }
        .home-hero-panel h2 { font-size: 1.7rem; font-weight: 750; letter-spacing: -0.035em; line-height: 1.15; margin: 9px 0 0; max-width: 250px; position: relative; }
        .home-hero-panel > p:not(.home-panel-kicker) { color: rgba(255, 255, 255, 0.7); font-size: 0.9rem; line-height: 1.6; margin: 12px 0 0; max-width: 260px; position: relative; }
        .home-panel-status { align-items: center; color: rgba(255, 255, 255, 0.82); display: flex; font-size: 0.75rem; gap: 9px; margin-top: 27px; position: relative; }
        .home-status-indicator { background: #78d6a1; border-radius: 50%; box-shadow: 0 0 0 4px rgba(120, 214, 161, 0.14); height: 8px; width: 8px; }
        .home-panel-orbit { border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 50%; position: absolute; }
        .home-panel-orbit-one { height: 220px; right: -105px; top: 46px; width: 220px; }
        .home-panel-orbit-two { height: 155px; right: -73px; top: 78px; width: 155px; }

        .home-section { padding-top: 71px; }
        .home-section-heading { align-items: end; display: flex; gap: 30px; justify-content: space-between; margin-bottom: 28px; }
        .home-section-heading h2 { color: #2b3046; font-size: 1.75rem; font-weight: 800; letter-spacing: -0.04em; margin: 7px 0 0; }
        .home-section-heading p { color: var(--muted); font-size: 0.9rem; line-height: 1.6; margin: 0; max-width: 345px; }
        .home-area-grid { display: grid; gap: 15px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .home-area-card { align-items: flex-start; background: #fff; border: 1px solid var(--line); border-radius: 17px; box-shadow: 0 5px 14px rgba(49, 53, 82, 0.035); display: flex; gap: 14px; min-height: 149px; padding: 21px; position: relative; text-decoration: none; transition: box-shadow 180ms ease, transform 180ms ease, border-color 180ms ease; }
        .home-area-card:hover { border-color: transparent; box-shadow: var(--shadow); color: inherit; transform: translateY(-5px); }
        .home-area-icon { align-items: center; border-radius: 12px; display: flex; flex-shrink: 0; font-size: 1.25rem; height: 45px; justify-content: center; width: 45px; }
        .home-area-content { display: flex; flex-direction: column; gap: 6px; padding-right: 12px; }
        .home-area-content strong { color: #30364d; font-size: 1rem; font-weight: 800; }
        .home-area-content span { color: var(--muted); font-size: 0.78rem; line-height: 1.55; }
        .home-area-arrow { color: #a0a6b8; font-size: 0.9rem; position: absolute; right: 17px; top: 20px; }
        .home-area-tag { color: #a1a6b5; font-size: 0.65rem; font-weight: 800; letter-spacing: 0.04em; margin-left: auto; position: absolute; right: 17px; text-transform: uppercase; top: 21px; }
        .home-area-card-purple .home-area-icon { background: #efedff; color: #7169d0; }
        .home-area-card-blue .home-area-icon { background: #e8f4ff; color: #4b9fda; }
        .home-area-card-cyan .home-area-icon { background: #e8f9fa; color: #34a8ae; }
        .home-area-card-green .home-area-icon { background: #e8f7ef; color: #47ad74; }
        .home-area-card-amber .home-area-icon { background: #fff5e4; color: #cf9941; }
        .home-area-card-rose .home-area-icon { background: #fff0f1; color: #d77b89; }
        .home-note { align-items: center; background: #fff; border: 1px solid var(--line); border-radius: 17px; display: flex; gap: 15px; margin-top: 53px; padding: 18px 21px; }
        .home-note-icon { align-items: center; background: #f2efff; border-radius: 12px; color: var(--brand); display: flex; flex-shrink: 0; font-size: 1.1rem; height: 42px; justify-content: center; width: 42px; }
        .home-note strong { color: #353b51; font-size: 0.9rem; }
        .home-note p { color: var(--muted); font-size: 0.78rem; margin: 3px 0 0; }

        @media (max-width: 900px) {
            .home-hero { padding: 57px 0 62px; }
            .home-hero-grid { gap: 40px; grid-template-columns: 1fr; }
            .home-hero-panel { max-width: 520px; }
            .home-area-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 560px) {
            .home-container { padding-left: 16px; padding-right: 16px; }
            .home-hero { padding: 42px 0 48px; }
            .home-hero h1 { font-size: 2.45rem; }
            .home-hero-lead { font-size: 0.95rem; }
            .home-actions { align-items: stretch; flex-direction: column; }
            .home-button { justify-content: center; }
            .home-hero-panel { border-radius: 20px; padding: 25px; }
            .home-section { padding-top: 50px; }
            .home-section-heading { align-items: flex-start; flex-direction: column; gap: 10px; }
            .home-section-heading h2 { font-size: 1.55rem; }
            .home-area-grid { grid-template-columns: 1fr; }
            .home-note { align-items: flex-start; margin-top: 36px; }
        }
    </style>
@endsection

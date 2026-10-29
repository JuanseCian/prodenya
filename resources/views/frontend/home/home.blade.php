@extends('layouts.frontend')

@section('title', 'Inicio | PRODENYA')

@section('description', 'PRODENYA · Promoción y Protección de Derechos')

@section('content')
    @php
        $homeDate = now()->translatedFormat('l d \\d\\e F');
    @endphp

    <div class="home-page">
        <div class="home-toast-region" id="home-toast-region" aria-live="polite" aria-atomic="true">
            @if (session('status'))
                <div class="home-status-alert home-toast home-toast--success" data-home-toast role="status">
                    <i class="bi bi-check-circle" aria-hidden="true"></i>
                    <span>{{ session('status') }}</span>
                    <button type="button" data-home-toast-close aria-label="Cerrar notificación">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>
            @endif
        </div>

        <main class="home-container">
            <h1 class="sr-only">Inicio</h1>

            <section class="home-overview" aria-label="Acciones principales">
                <div class="home-overview-copy">
                    <p class="home-eyebrow">CENTRO DE TRABAJO</p>
                    <p class="home-overview-date"><i class="bi bi-calendar3" aria-hidden="true"></i> {{ ucfirst($homeDate) }}</p>
                </div>
                <div class="home-overview-actions">
                        <button type="button" class="home-panel-launch" data-personal-panel-open>
                            <span class="home-panel-launch-icon"><i class="bi bi-grid-1x2" aria-hidden="true"></i></span>
                            <span><strong>Abrir mi panel</strong><small>Prioridades y agenda de los próximos 3 días</small></span>
                        <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                    </button>
                </div>
            </section>

            <section class="home-shortcuts" aria-labelledby="home-shortcuts-title">
                <div class="home-section-heading">
                    <div>
                        <p class="home-eyebrow">NAVEGACIÓN</p>
                        <h2 id="home-shortcuts-title">Accesos directos</h2>
                    </div>
                </div>
                <div class="home-shortcut-grid">
                    @if (Route::has('legajos.index'))
                        <a href="{{ route('legajos.index') }}" class="home-shortcut">
                            <i class="bi bi-folder2-open" aria-hidden="true"></i>
                            <span><strong>Consultar legajos</strong><small>Personas, intervenciones y seguimiento</small></span>
                            <i class="bi bi-arrow-up-right home-shortcut-arrow" aria-hidden="true"></i>
                        </a>
                    @endif
                    @if (Route::has('legajos.create'))
                        <a href="{{ route('legajos.create') }}" class="home-shortcut">
                            <i class="bi bi-folder-plus" aria-hidden="true"></i>
                            <span><strong>Nuevo legajo</strong><small>Iniciar un nuevo registro</small></span>
                            <i class="bi bi-arrow-up-right home-shortcut-arrow" aria-hidden="true"></i>
                        </a>
                    @endif
                    @if (Route::has('agenda.index'))
                        <a href="{{ route('agenda.index') }}" class="home-shortcut">
                            <i class="bi bi-calendar3" aria-hidden="true"></i>
                            <span><strong>Agenda completa</strong><small>Calendarios personal y general</small></span>
                            <i class="bi bi-arrow-up-right home-shortcut-arrow" aria-hidden="true"></i>
                        </a>
                    @endif
                    @if (Route::has('mailbox.index') && auth()->user()->tieneRol('admin', 'oficios', 'coordinador'))
                        <a href="{{ route('mailbox.index') }}" class="home-shortcut">
                            <i class="bi bi-inbox" aria-hidden="true"></i>
                            <span><strong>Correo institucional</strong><small>Oficios y comunicaciones recibidas</small></span>
                            <i class="bi bi-arrow-up-right home-shortcut-arrow" aria-hidden="true"></i>
                        </a>
                    @endif
                </div>
            </section>

            <section class="home-daily-preview" data-home-preview aria-labelledby="home-daily-title">
                <div class="home-daily-header">
                    <div>
                        <p class="home-eyebrow">SEGUIMIENTO</p>
                        <h2 id="home-daily-title">Prioridades de los próximos días</h2>
                    </div>
                    <span class="home-daily-date" data-panel-date>{{ ucfirst($homeDate) }}</span>
                </div>
                <div class="home-preview-loading" data-home-preview-loading>
                    <div class="home-preview-skeleton-summary" aria-hidden="true">
                        <span class="home-skeleton home-skeleton-stat"></span>
                        <span class="home-skeleton home-skeleton-stat"></span>
                        <span class="home-skeleton home-skeleton-stat"></span>
                    </div>
                    <div class="home-preview-skeleton-columns" aria-hidden="true">
                        <span class="home-skeleton home-skeleton-line"></span>
                        <span class="home-skeleton home-skeleton-line"></span>
                    </div>
                    <span class="sr-only">Cargando información</span>
                </div>
                <div data-home-preview-content hidden>
                    <div class="home-preview-summary" aria-label="Resumen de obligaciones">
                        <div><strong data-panel-pending>0</strong><span>pendientes</span></div>
                        <div class="is-warning"><strong data-panel-overdue>0</strong><span>vencidas</span></div>
                        <div><strong data-panel-upcoming>0</strong><span>vencen pronto</span></div>
                        <div><strong data-panel-events-count>0</strong><span>eventos próximos</span></div>
                    </div>
                    <div class="home-preview-columns">
                        <section class="home-preview-section" aria-labelledby="home-preview-tasks-title">
                            <div class="home-preview-section-heading"><h3 id="home-preview-tasks-title">Tareas por atender</h3><a href="{{ route('tareas.index') }}">Ver todas <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
                            <div class="home-control-list" data-panel-tasks></div>
                        </section>
                        <section class="home-preview-section" aria-labelledby="home-preview-events-title">
                            <div class="home-preview-section-heading"><h3 id="home-preview-events-title">Agenda próxima</h3><a href="{{ route('agenda.index') }}">Abrir agenda <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
                            <div class="home-control-list" data-panel-events></div>
                        </section>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div class="home-panel-backdrop" data-personal-panel-close aria-hidden="true"></div>
    <aside id="personal-control-panel" class="home-control-panel" role="dialog" aria-modal="true" aria-labelledby="personal-panel-title" aria-hidden="true">
        <div class="home-control-header">
            <div>
                <p class="home-eyebrow">CENTRO PERSONAL</p>
                <h2 id="personal-panel-title">Mi panel</h2>
            </div>
            <button type="button" class="home-panel-close" data-personal-panel-close aria-label="Cerrar mi panel"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </div>

        <div class="home-control-body">
            <div class="home-panel-loading" data-panel-loading>
                <div class="home-panel-skeleton" aria-hidden="true">
                    <span class="home-skeleton home-skeleton-panel-title"></span>
                    <div class="home-preview-skeleton-summary">
                        <span class="home-skeleton home-skeleton-stat"></span>
                        <span class="home-skeleton home-skeleton-stat"></span>
                        <span class="home-skeleton home-skeleton-stat"></span>
                    </div>
                    <span class="home-skeleton home-skeleton-panel-line"></span>
                    <span class="home-skeleton home-skeleton-panel-line short"></span>
                    <span class="home-skeleton home-skeleton-panel-line"></span>
                </div>
                <span class="sr-only">Cargando información</span>
            </div>
            <div class="home-panel-error" data-panel-error hidden role="alert">
                <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                <span>No pudimos cargar tu panel. Intentá nuevamente.</span>
            </div>
            <div data-panel-content hidden>
                <p class="home-control-date" data-panel-date></p>
                <div class="home-control-summary" aria-label="Resumen de obligaciones">
                    <div><strong data-panel-pending>0</strong><span>pendientes</span></div>
                    <div class="is-warning"><strong data-panel-overdue>0</strong><span>vencidas</span></div>
                    <div><strong data-panel-upcoming>0</strong><span>vencen pronto</span></div>
                    <div><strong data-panel-events-count>0</strong><span>eventos próximos</span></div>
                </div>

                <div class="home-control-sections">
                    <section class="home-control-section" aria-labelledby="panel-tasks-title">
                        <div class="home-control-section-heading"><h3 id="panel-tasks-title">Tareas por atender</h3><a href="{{ route('tareas.index') }}">Ver todas <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
                        <div class="home-control-list" data-panel-tasks></div>
                    </section>
                    <section class="home-control-section" aria-labelledby="panel-events-title">
                        <div class="home-control-section-heading"><h3 id="panel-events-title">Agenda próxima</h3><a href="{{ route('agenda.index') }}">Abrir agenda <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
                        <div class="home-control-list" data-panel-events></div>
                    </section>
                </div>
            </div>
        </div>
    </aside>

    <style>
        .home-page { animation: home-page-enter 520ms cubic-bezier(0.22, 1, 0.36, 1) both; background: #f4f7fb; color: #173247; min-height: calc(100vh - 164px); }
        .home-container { margin: 0 auto; max-width: 1180px; padding-left: 24px; padding-right: 24px; }
        .home-toast-region { display: grid; gap: 10px; position: fixed; right: 22px; top: 22px; width: min(calc(100vw - 32px), 390px); z-index: 80; }
        .home-status-alert { align-items: center; background: #e8f7ef; border: 1px solid #bde3cb; border-radius: 14px; box-shadow: 0 16px 34px rgba(35, 113, 72, 0.14); color: #267148; display: flex; font-size: 0.85rem; font-weight: 700; gap: 9px; margin: 0; padding: 13px 14px; }
        .home-toast { animation: home-toast-enter 360ms cubic-bezier(0.22, 1, 0.36, 1) both; }
        .home-toast.is-leaving { animation: home-toast-leave 180ms ease both; }
        .home-toast > span { flex: 1; }
        .home-toast button { align-items: center; background: transparent; border: 0; border-radius: 8px; color: currentColor; cursor: pointer; display: inline-flex; height: 28px; justify-content: center; opacity: 0.6; padding: 0; width: 28px; }
        .home-toast button:hover { background: rgba(38, 113, 72, 0.1); opacity: 1; }
        .home-toast--error { background: #fff1ef; border-color: #f1c5bf; color: #a8493c; }
        .home-overview { align-items: center; animation: home-section-enter 560ms 80ms cubic-bezier(0.22, 1, 0.36, 1) both; border-bottom: 1px solid #dce7ed; display: flex; gap: 24px; justify-content: space-between; min-height: 182px; padding: 42px 0 36px; }
        .home-overview-copy { min-width: 0; }
        .home-overview-date { align-items: center; color: #526873; display: flex; font-size: 0.9rem; gap: 8px; margin: 10px 0 0; text-transform: capitalize; }
        .home-overview-date i { color: #397461; }
        .home-eyebrow { color: #397461; font-size: 0.68rem; font-weight: 800; letter-spacing: 0.16em; margin: 0; text-transform: uppercase; }
        .home-overview-actions { flex: 0 0 auto; }
        .home-panel-launch { align-items: center; background: #173247; border: 0; border-radius: 14px; box-shadow: 0 15px 28px rgba(23, 50, 71, 0.18); color: #fff; cursor: pointer; display: flex; gap: 12px; max-width: 290px; padding: 12px 14px; text-align: left; transition: box-shadow 160ms ease, transform 160ms ease; width: 100%; }
        .home-panel-launch:hover { box-shadow: 0 18px 35px rgba(23, 50, 71, 0.25); transform: translateY(-2px); }
        .home-panel-launch:focus-visible, .home-panel-close:focus-visible, .home-shortcut:focus-visible { outline: 3px solid rgba(107, 143, 113, 0.45); outline-offset: 3px; }
        .home-panel-launch-icon { align-items: center; background: rgba(185, 232, 204, 0.16); border-radius: 10px; color: #b9e8cc; display: flex; font-size: 1.15rem; height: 42px; justify-content: center; width: 42px; }
        .home-panel-launch > span:nth-child(2) { display: flex; flex: 1; flex-direction: column; gap: 3px; }
        .home-panel-launch strong { font-size: 0.88rem; }
        .home-panel-launch small { color: rgba(255, 255, 255, 0.67); font-size: 0.72rem; }
        .home-panel-launch > i { color: #b9e8cc; font-size: 0.85rem; }
        .home-shortcuts { animation: home-section-enter 560ms 160ms cubic-bezier(0.22, 1, 0.36, 1) both; border-top: 1px solid #dce7ed; padding: 34px 0 100px; }
        .home-section-heading h2 { color: #173247; font-size: 1.45rem; font-weight: 700; letter-spacing: -0.03em; margin: 9px 0 22px; }
        .home-shortcut-grid { display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .home-shortcut { align-items: center; background: #fff; border: 1px solid #dce7ed; border-radius: 14px; box-shadow: 0 7px 18px rgba(37, 65, 84, 0.04); color: #2c4a5e; display: flex; gap: 13px; min-height: 88px; padding: 16px; text-decoration: none; transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease; }
        .home-shortcut:hover { border-color: #a9cbb5; box-shadow: 0 12px 24px rgba(57, 116, 97, 0.11); color: #173247; transform: translateY(-2px); }
        .home-shortcut > i:first-child { align-items: center; background: #eaf3ed; border-radius: 11px; color: #397461; display: flex; flex-shrink: 0; font-size: 1.1rem; height: 40px; justify-content: center; width: 40px; }
        .home-shortcut span { display: flex; flex: 1; flex-direction: column; gap: 3px; min-width: 0; }
        .home-shortcut strong { font-size: 0.88rem; font-weight: 700; }
        .home-shortcut small { color: #5d727e; font-size: 0.74rem; line-height: 1.35; }
        .home-shortcut-arrow { color: #8fa4b1; font-size: 0.8rem; }
        .home-daily-preview { animation: home-section-enter 560ms 240ms cubic-bezier(0.22, 1, 0.36, 1) both; border: 1px solid #dce7ed; border-radius: 18px; background: #fff; box-shadow: 0 10px 24px rgba(37, 65, 84, 0.04); margin-bottom: 80px; padding: 22px; }
        .home-daily-header { align-items: flex-start; display: flex; gap: 16px; justify-content: space-between; }
        .home-daily-header h2 { color: #173247; font-size: 1.25rem; font-weight: 750; letter-spacing: -0.03em; margin: 8px 0 0; }
        .home-daily-date { color: #397461; font-size: 0.78rem; font-weight: 750; text-align: right; text-transform: capitalize; }
        .home-preview-loading { min-height: 150px; padding-top: 20px; }
        .home-preview-skeleton-summary { display: grid; gap: 9px; grid-template-columns: repeat(3, 1fr); }
        .home-preview-skeleton-columns { display: grid; gap: 28px; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-top: 24px; }
        .home-skeleton { animation: home-skeleton-shimmer 1.2s ease-in-out infinite; background: linear-gradient(100deg, #e9f1ec 20%, #f7fbf8 45%, #e9f1ec 70%); background-size: 240% 100%; border-radius: 8px; display: block; }
        .home-skeleton-stat { height: 58px; }
        .home-skeleton-line { height: 94px; }
        .home-panel-skeleton { display: grid; gap: 12px; }
        .home-skeleton-panel-title { height: 18px; margin-bottom: 6px; max-width: 180px; }
        .home-skeleton-panel-line { height: 48px; }
        .home-skeleton-panel-line.short { max-width: 82%; }
        .home-preview-summary { display: grid; gap: 9px; grid-template-columns: repeat(4, 1fr); margin-top: 20px; }
        .home-preview-summary div { background: #f7fbf8; border: 1px solid #e0eae4; border-radius: 13px; padding: 13px 14px; }
        .home-preview-summary strong, .home-preview-summary span { display: block; }
        .home-preview-summary strong { color: #173247; font-size: 1.35rem; letter-spacing: -0.04em; }
        .home-preview-summary span { color: #5d727e; font-size: 0.7rem; margin-top: 2px; }
        .home-preview-summary .is-warning strong { color: #b27432; }
        .home-preview-columns { display: grid; gap: 28px; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-top: 24px; }
        .home-preview-section { border-top: 1px solid #dfe9e3; padding-top: 16px; }
        .home-preview-section-heading { align-items: center; display: flex; gap: 12px; justify-content: space-between; }
        .home-preview-section-heading h3 { color: #244357; font-size: 0.94rem; font-weight: 750; margin: 0; }
        .home-preview-section-heading a { color: #397461; font-size: 0.7rem; font-weight: 750; text-decoration: none; white-space: nowrap; }
        .home-preview-section-heading a:hover { text-decoration: underline; }

        .home-panel-backdrop { background: rgba(17, 34, 46, 0.38); inset: 0; opacity: 0; pointer-events: none; position: fixed; transition: opacity 360ms cubic-bezier(0.22, 1, 0.36, 1); z-index: 60; }
        .home-control-panel { background: rgba(247, 251, 249, 0.97); border: 1px solid rgba(255, 255, 255, 0.8); border-radius: 28px; bottom: 22px; box-shadow: -18px 20px 60px rgba(23, 50, 71, 0.22); color: #173247; display: flex; flex-direction: column; max-width: calc(100vw - 32px); opacity: 0; overflow: hidden; position: fixed; right: 22px; top: 22px; transform: translate3d(32px, 0, 0) scale(0.985); transition: opacity 360ms cubic-bezier(0.22, 1, 0.36, 1), transform 440ms cubic-bezier(0.22, 1, 0.36, 1), visibility 0s linear 440ms; visibility: hidden; width: 560px; z-index: 61; }
        .home-panel-open .home-panel-backdrop { opacity: 1; pointer-events: auto; }
        .home-panel-open .home-control-panel { opacity: 1; transform: translate3d(0, 0, 0) scale(1); transition-delay: 0s; visibility: visible; }
        .home-control-header { align-items: flex-start; border-bottom: 1px solid rgba(220, 227, 223, 0.8); display: flex; justify-content: space-between; padding: 28px 28px 22px; }
        .home-control-header h2 { font-size: 1.8rem; font-weight: 750; letter-spacing: -0.045em; margin: 7px 0 0; }
        .home-panel-close { align-items: center; background: rgba(225, 234, 229, 0.8); border: 0; border-radius: 50%; color: #49616d; cursor: pointer; display: flex; height: 38px; justify-content: center; width: 38px; }
        .home-panel-close:hover { background: #dce9e1; color: #173247; }
        .home-control-body { flex: 1; min-height: 0; overflow-y: auto; padding: 24px 28px 30px; }
        .home-panel-loading, .home-panel-error { color: #5d727e; display: flex; flex-direction: column; font-size: 0.82rem; gap: 10px; justify-content: center; min-height: 260px; text-align: center; }
        .home-panel-error { color: #9b5964; }
        .home-panel-error i { font-size: 1.5rem; }
        .home-control-date { color: #397461; font-size: 0.82rem; font-weight: 750; margin: 0 0 17px; text-transform: capitalize; }
        .home-control-summary { display: grid; gap: 9px; grid-template-columns: repeat(4, 1fr); margin-bottom: 27px; }
        .home-control-summary div { background: rgba(255, 255, 255, 0.75); border: 1px solid #e0eae4; border-radius: 14px; padding: 13px 14px; }
        .home-control-summary strong, .home-control-summary span { display: block; }
        .home-control-summary strong { color: #173247; font-size: 1.45rem; letter-spacing: -0.04em; }
        .home-control-summary span { color: #5d727e; font-size: 0.7rem; margin-top: 2px; }
        .home-control-summary .is-warning strong { color: #b27432; }
        .home-control-sections { display: grid; gap: 24px; }
        .home-control-section { border-top: 1px solid #dfe9e3; padding-top: 17px; }
        .home-control-section-heading { align-items: center; display: flex; justify-content: space-between; gap: 12px; }
        .home-control-section-heading h3 { color: #244357; font-size: 0.95rem; font-weight: 750; margin: 0; }
        .home-control-section-heading a { color: #397461; font-size: 0.7rem; font-weight: 750; text-decoration: none; white-space: nowrap; }
        .home-control-section-heading a:hover { text-decoration: underline; }
        .home-control-list { margin-top: 9px; }
        .home-obligation { align-items: center; border-bottom: 1px solid #e9efeb; display: flex; gap: 10px; min-height: 58px; padding: 9px 0; }
        .home-obligation:last-child { border-bottom: 0; }
        .home-obligation-dot { background: #80b8a8; border-radius: 50%; flex-shrink: 0; height: 8px; width: 8px; }
        .home-obligation-dot.urgente { background: #d9786d; }
        .home-obligation-dot.alta { background: #d49a58; }
        .home-obligation--vencida { background: #fff7f5; border-radius: 10px; padding-left: 9px; padding-right: 9px; }
        .home-obligation--vencida .home-obligation-copy strong { color: #9b4c40; }
        .home-obligation--hoy { background: #fffaf0; border-radius: 10px; padding-left: 9px; padding-right: 9px; }
        .home-obligation--hoy .home-obligation-copy strong { color: #8e6425; }
        .home-obligation-time { color: #397461; font-size: 0.76rem; font-variant-numeric: tabular-nums; font-weight: 800; min-width: 40px; }
        .home-obligation-copy { display: flex; flex: 1; flex-direction: column; gap: 3px; min-width: 0; }
        .home-obligation-copy strong { color: #2d4a5c; font-size: 0.8rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .home-obligation-copy small { color: #5d727e; font-size: 0.7rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .home-obligation-link { color: #8ba1ad; font-size: 0.8rem; padding: 6px; }
        .home-obligation-link:hover { color: #397461; }
        .home-control-empty { color: #5b707a; font-size: 0.78rem; padding: 18px 0 8px; }
        @keyframes home-page-enter { from { opacity: 0; } to { opacity: 1; } }
        @keyframes home-section-enter { from { opacity: 0; transform: translateY(13px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes home-toast-enter { from { opacity: 0; transform: translate3d(18px, -8px, 0) scale(0.96); } to { opacity: 1; transform: translate3d(0, 0, 0) scale(1); } }
        @keyframes home-toast-leave { from { opacity: 1; transform: translate3d(0, 0, 0) scale(1); } to { opacity: 0; transform: translate3d(18px, -6px, 0) scale(0.97); } }
        @keyframes home-skeleton-shimmer { 0% { background-position: 100% 0; } 100% { background-position: -100% 0; } }
        @keyframes home-panel-spin { to { transform: rotate(360deg); } }

        .dark .home-page { background: #0d1620; }
        .dark .home-eyebrow { color: #9ce0bf; }
        .dark .home-section-heading h2 { color: #edf6fb; }
        .dark .home-shortcuts { border-color: #294252; }
        .dark .home-shortcut { background: #152532; border-color: #294252; color: #d9e7ee; }
        .dark .home-shortcut small { color: #9bb2c1; }
        .dark .home-overview { border-color: #294252; }
        .dark .home-overview-date, .dark .home-daily-date { color: #9bb2c1; }
        .dark .home-daily-preview { background: #152532; border-color: #294252; }
        .dark .home-daily-header h2, .dark .home-preview-section-heading h3, .dark .home-preview-summary strong { color: #d9e7ee; }
        .dark .home-preview-summary div { background: #1b303f; border-color: #294252; }
        .dark .home-preview-summary span { color: #9bb2c1; }
        .dark .home-preview-section { border-color: #294252; }
        .dark .home-preview-loading, .dark .home-panel-loading, .dark .home-control-empty { color: #a8bbc5; }
        .dark .home-skeleton { background: linear-gradient(100deg, #1c3544 20%, #294252 45%, #1c3544 70%); background-size: 240% 100%; }
        .dark .home-control-date { color: #9ccfb2; }
        .dark .home-preview-section-heading a, .dark .home-obligation-link { color: #9ccfb2; }
        .dark .home-control-panel { background: rgba(21, 37, 50, 0.98); border-color: #294252; color: #d9e7ee; }
        .dark .home-control-header, .dark .home-control-section { border-color: #294252; }
        .dark .home-control-header h2, .dark .home-control-section-heading h3, .dark .home-control-summary strong, .dark .home-obligation-copy strong { color: #d9e7ee; }
        .dark .home-control-summary div { background: #1b303f; border-color: #294252; }
        .dark .home-control-summary span, .dark .home-obligation-copy small { color: #9bb2c1; }
        .dark .home-obligation { border-color: #294252; }

        @media (max-width: 720px) {
            .home-container { padding-left: 16px; padding-right: 16px; }
            .home-overview { align-items: stretch; flex-direction: column; gap: 22px; min-height: auto; padding-bottom: 28px; padding-top: 32px; }
            .home-overview-actions, .home-panel-launch { max-width: none; }
            .home-shortcut-grid { grid-template-columns: 1fr; }
            .home-daily-preview { margin-bottom: 56px; padding: 18px; }
            .home-preview-columns { grid-template-columns: 1fr; }
            .home-control-panel { bottom: 10px; left: 10px; right: 10px; top: 10px; width: auto; }
        }
        @media (max-width: 420px) {
            .home-control-header { padding-left: 20px; padding-right: 20px; }
            .home-control-body { padding-left: 20px; padding-right: 20px; }
            .home-control-summary, .home-preview-summary { gap: 6px; grid-template-columns: repeat(2, 1fr); }
            .home-control-summary div { padding: 11px 9px; }
            .home-control-summary strong { font-size: 1.25rem; }
            .home-preview-summary div { padding: 11px 9px; }
            .home-preview-summary strong { font-size: 1.2rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .home-page, .home-toast, .home-overview, .home-shortcuts, .home-daily-preview { animation: none; }
            .home-panel-backdrop, .home-control-panel, .home-panel-launch, .home-shortcut { transition: none; }
            .home-skeleton { animation: none; background: #e9f1ec; }
            .dark .home-skeleton { background: #1c3544; }
        }
    </style>

    @push('scripts')
        <script>
            (() => {
                const shell = document.querySelector('.home-page');
                const panel = document.getElementById('personal-control-panel');
                const preview = document.querySelector('[data-home-preview]');
                const previewLoading = preview?.querySelector('[data-home-preview-loading]');
                const previewContent = preview?.querySelector('[data-home-preview-content]');
                const openButtons = [...document.querySelectorAll('[data-personal-panel-open]')];
                const closeButtons = [...document.querySelectorAll('[data-personal-panel-close]')];
                const loading = panel?.querySelector('[data-panel-loading]');
                const error = panel?.querySelector('[data-panel-error]');
                const content = panel?.querySelector('[data-panel-content]');

                if (!shell || !panel || !openButtons.length || !loading || !error || !content) return;

                 let lastFocus = null;
                 let previousOverflow = '';
                 let loaded = false;
                 let panelRequest = null;
                const focusableSelector = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

                 const text = (tag, className, value) => {
                     const element = document.createElement(tag);
                     element.className = className;
                     element.textContent = value;
                     return element;
                 };

                 const toastRegion = document.getElementById('home-toast-region');
                 const dismissToast = (toast) => {
                     toast.classList.add('is-leaving');
                     window.setTimeout(() => toast.remove(), 180);
                 };
                 const showToast = (message) => {
                     if (!toastRegion) return;

                     const toast = document.createElement('div');
                     toast.className = 'home-toast home-toast--error';
                     toast.setAttribute('role', 'alert');
                     toast.append(text('i', 'bi bi-exclamation-circle', ''));
                     toast.append(text('span', '', message));
                     const close = document.createElement('button');
                     close.type = 'button';
                     close.setAttribute('aria-label', 'Cerrar notificación');
                     close.append(text('i', 'bi bi-x-lg', ''));
                     close.addEventListener('click', () => dismissToast(toast));
                     toast.append(close);
                     toastRegion.append(toast);
                     window.setTimeout(() => {
                         if (toast.isConnected) dismissToast(toast);
                     }, 6000);
                 };

                 document.querySelectorAll('[data-home-toast]').forEach((toast) => {
                     toast.querySelector('[data-home-toast-close]')?.addEventListener('click', () => dismissToast(toast));
                     window.setTimeout(() => {
                         if (toast.isConnected) dismissToast(toast);
                     }, 6000);
                 });

                const renderEmpty = (container, message) => {
                    container.replaceChildren(text('p', 'home-control-empty', message));
                };

                const renderList = (container, items, emptyMessage, isPreview, renderItem) => {
                    container.replaceChildren();
                    const visibleItems = isPreview ? items.slice(0, 3) : items;
                    if (!visibleItems.length) {
                        renderEmpty(container, emptyMessage);
                        return;
                    }
                    visibleItems.forEach(renderItem);
                };

                 const renderRoot = (root, data, isPreview = false) => {
                     const date = root.querySelector('[data-panel-date]');
                     if (date) date.textContent = `${data.fecha} · ${data.rango?.etiqueta ?? 'Próximos 3 días'}`;
                     root.querySelector('[data-panel-pending]').textContent = data.tareas_pendientes;
                     root.querySelector('[data-panel-overdue]').textContent = data.tareas_vencidas;
                     root.querySelector('[data-panel-upcoming]').textContent = data.tareas_proximas;
                     root.querySelector('[data-panel-events-count]').textContent = data.eventos_proximos;

                     const tasks = root.querySelector('[data-panel-tasks]');
                     const allClear = data.tareas.length === 0 && data.eventos.length === 0;
                     renderList(tasks, data.tareas, allClear ? 'Jornada al día.' : 'No hay tareas por atender en este período.', isPreview, (task) => {
                             const row = document.createElement('div');
                             row.className = `home-obligation home-obligation--${task.urgencia ?? 'proxima'}`;
                             row.append(text('span', `home-obligation-dot ${task.prioridad ?? ''}`, ''));
                             const copy = document.createElement('div');
                             copy.className = 'home-obligation-copy';
                             copy.append(text('strong', '', task.titulo));
                             copy.append(text('small', '', `${task.alerta ?? 'Tarea'}${task.fecha_limite ? ` · ${task.fecha_limite}` : ''}${task.legajo ? ` · ${task.legajo}` : ''}`));
                             row.append(copy);
                            if (task.legajo_url) {
                                const link = document.createElement('a');
                                link.className = 'home-obligation-link';
                                link.href = task.legajo_url;
                                link.setAttribute('aria-label', 'Abrir legajo');
                                link.append(text('i', 'bi bi-arrow-up-right', ''));
                                row.append(link);
                            }
                            tasks.append(row);
                     });

                     const events = root.querySelector('[data-panel-events]');
                     renderList(events, data.eventos, allClear ? 'Jornada al día.' : 'No hay actividades en este período.', isPreview, (event) => {
                             const row = document.createElement('div');
                             row.className = 'home-obligation';
                             row.append(text('time', 'home-obligation-time', event.es_hoy ? (event.hora ?? '--:--') : `${event.fecha ?? '--/--'} · ${event.hora ?? '--:--'}`));
                             const copy = document.createElement('div');
                            copy.className = 'home-obligation-copy';
                            copy.append(text('strong', '', event.titulo));
                            copy.append(text('small', '', `${event.tipo ? event.tipo.charAt(0).toUpperCase() + event.tipo.slice(1) : 'Evento'}${event.lugar ? ` · ${event.lugar}` : ''}${event.visibilidad === 'general' ? ' · General' : ' · Personal'}`));
                            row.append(copy);
                            const link = document.createElement('a');
                            link.className = 'home-obligation-link';
                            link.href = event.agenda_url;
                            link.setAttribute('aria-label', 'Abrir agenda');
                            link.append(text('i', 'bi bi-arrow-up-right', ''));
                            row.append(link);
                            events.append(row);
                    });
                };

                const renderPanel = (data) => {
                    renderRoot(panel, data);
                    if (preview) renderRoot(preview, data, true);
                };

                const loadPanel = () => {
                    if (loaded || panelRequest) return;
                    panelRequest = fetch(@json(route('personal-panel')), { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                        .then((response) => response.ok ? response.json() : Promise.reject(new Error('panel')))
                        .then((data) => {
                            renderPanel(data);
                            loaded = true;
                            loading.hidden = true;
                            error.hidden = true;
                            content.hidden = false;
                            if (previewLoading) previewLoading.hidden = true;
                            if (previewContent) previewContent.hidden = false;
                        })
                         .catch(() => {
                             loading.hidden = true;
                             error.hidden = false;
                             if (previewLoading) {
                                 previewLoading.textContent = 'No pudimos cargar la información.';
                             }
                             showToast('No pudimos cargar tu panel. Intentá nuevamente.');
                         })
                        .finally(() => {
                            panelRequest = null;
                        });
                };

                if (preview) loadPanel();

                const setOpen = (isOpen) => {
                    document.body.classList.toggle('home-panel-open', isOpen);
                    panel.setAttribute('aria-hidden', String(!isOpen));
                    if (isOpen) {
                         lastFocus = document.activeElement;
                         previousOverflow = document.body.style.overflow;
                         document.body.style.overflow = 'hidden';
                         loadPanel();
                        window.requestAnimationFrame(() => panel.querySelector('[data-personal-panel-close]')?.focus());
                    } else {
                         document.body.style.overflow = previousOverflow;
                        if (lastFocus instanceof HTMLElement) {
                            lastFocus.focus();
                            lastFocus = null;
                        }
                    }
                };

                openButtons.forEach((button) => button.addEventListener('click', () => setOpen(true)));
                closeButtons.forEach((button) => button.addEventListener('click', () => setOpen(false)));
                document.addEventListener('keydown', (event) => {
                    if (!document.body.classList.contains('home-panel-open')) return;
                    if (event.key === 'Escape') {
                        setOpen(false);
                        return;
                    }
                    if (event.key !== 'Tab') return;
                    const focusable = [...panel.querySelectorAll(focusableSelector)].filter((element) => element.offsetParent !== null);
                    if (!focusable.length) return;
                    const first = focusable[0];
                    const last = focusable[focusable.length - 1];
                    if (event.shiftKey && document.activeElement === first) {
                        event.preventDefault();
                        last.focus();
                    } else if (!event.shiftKey && document.activeElement === last) {
                        event.preventDefault();
                        first.focus();
                    }
                });
            })();
        </script>
    @endpush
@endsection

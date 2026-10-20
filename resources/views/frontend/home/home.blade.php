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

        <main class="home-container">
            <section class="home-hero" aria-labelledby="home-title">
                <div class="home-hero-copy">
                    <p class="home-eyebrow">PRODENYA · ESPACIO DE TRABAJO</p>
                    <h1 id="home-title">Hola, <span>{{ $homeFirstName }}</span></h1>
                    <p class="home-copy">Accedé a tus herramientas y abrí tu panel personal cuando quieras revisar tareas y obligaciones.</p>
                    <button type="button" class="home-panel-launch" data-personal-panel-open>
                        <span class="home-panel-launch-icon"><i class="bi bi-grid-1x2" aria-hidden="true"></i></span>
                        <span><strong>Mi panel</strong><small>Ver tareas y agenda de hoy</small></span>
                        <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="home-hero-mark" aria-hidden="true">
                    <i class="bi bi-shield-check"></i>
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
                <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                <span>Cargando tu jornada...</span>
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
                    <div><strong data-panel-events-count>0</strong><span>eventos hoy</span></div>
                </div>

                <div class="home-control-sections">
                    <section class="home-control-section" aria-labelledby="panel-tasks-title">
                        <div class="home-control-section-heading"><h3 id="panel-tasks-title">Tareas asignadas</h3><a href="{{ route('tareas.index') }}">Ver todas <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
                        <div class="home-control-list" data-panel-tasks></div>
                    </section>
                    <section class="home-control-section" aria-labelledby="panel-events-title">
                        <div class="home-control-section-heading"><h3 id="panel-events-title">Agenda de hoy</h3><a href="{{ route('agenda.index') }}">Abrir agenda <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
                        <div class="home-control-list" data-panel-events></div>
                    </section>
                </div>
            </div>
        </div>
    </aside>

    <style>
        .home-page { background: #f4f7fb; color: #173247; min-height: calc(100vh - 164px); }
        .home-container { margin: 0 auto; max-width: 1180px; padding-left: 24px; padding-right: 24px; }
        .home-status-alert { align-items: center; background: #e8f7ef; border: 1px solid #bde3cb; border-radius: 12px; box-shadow: 0 8px 20px rgba(35, 113, 72, 0.07); color: #267148; display: flex; font-size: 0.85rem; font-weight: 700; gap: 9px; margin-top: 20px; padding: 12px 15px; }
        .home-hero { align-items: center; display: flex; justify-content: space-between; min-height: 435px; padding: 72px 0 82px; position: relative; }
        .home-hero-copy { max-width: 570px; position: relative; z-index: 1; }
        .home-eyebrow { color: #6b8f71; font-size: 0.68rem; font-weight: 800; letter-spacing: 0.16em; margin: 0; text-transform: uppercase; }
        .home-hero h1 { color: #173247; font-size: clamp(2.5rem, 5vw, 4.2rem); font-weight: 750; letter-spacing: -0.065em; line-height: 0.98; margin: 19px 0 0; }
        .home-hero h1 span { color: #6b8f71; }
        .home-copy { color: #607785; font-size: 1rem; line-height: 1.65; margin: 20px 0 0; max-width: 490px; }
        .home-hero-mark { align-items: center; background: linear-gradient(145deg, #e6f2eb, #cfe5d8); border-radius: 42% 58% 55% 45%; color: #397461; display: flex; font-size: 5rem; height: 220px; justify-content: center; margin-right: 8%; opacity: 0.9; transform: rotate(8deg); width: 220px; }
        .home-hero-mark i { transform: rotate(-8deg); }
        .home-panel-launch { align-items: center; background: #173247; border: 0; border-radius: 14px; box-shadow: 0 15px 28px rgba(23, 50, 71, 0.18); color: #fff; cursor: pointer; display: flex; gap: 12px; margin-top: 30px; max-width: 290px; padding: 12px 14px; text-align: left; transition: box-shadow 160ms ease, transform 160ms ease; width: 100%; }
        .home-panel-launch:hover { box-shadow: 0 18px 35px rgba(23, 50, 71, 0.25); transform: translateY(-2px); }
        .home-panel-launch:focus-visible, .home-panel-close:focus-visible, .home-shortcut:focus-visible { outline: 3px solid rgba(107, 143, 113, 0.45); outline-offset: 3px; }
        .home-panel-launch-icon { align-items: center; background: rgba(185, 232, 204, 0.16); border-radius: 10px; color: #b9e8cc; display: flex; font-size: 1.15rem; height: 42px; justify-content: center; width: 42px; }
        .home-panel-launch > span:nth-child(2) { display: flex; flex: 1; flex-direction: column; gap: 3px; }
        .home-panel-launch strong { font-size: 0.88rem; }
        .home-panel-launch small { color: rgba(255, 255, 255, 0.67); font-size: 0.72rem; }
        .home-panel-launch > i { color: #b9e8cc; font-size: 0.85rem; }
        .home-shortcuts { border-top: 1px solid #dce7ed; padding: 34px 0 100px; }
        .home-section-heading h2 { color: #173247; font-size: 1.45rem; font-weight: 700; letter-spacing: -0.03em; margin: 9px 0 22px; }
        .home-shortcut-grid { display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .home-shortcut { align-items: center; background: #fff; border: 1px solid #dce7ed; border-radius: 14px; box-shadow: 0 7px 18px rgba(37, 65, 84, 0.04); color: #2c4a5e; display: flex; gap: 13px; min-height: 88px; padding: 16px; text-decoration: none; transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease; }
        .home-shortcut:hover { border-color: #a9cbb5; box-shadow: 0 12px 24px rgba(57, 116, 97, 0.11); color: #173247; transform: translateY(-2px); }
        .home-shortcut > i:first-child { align-items: center; background: #eaf3ed; border-radius: 11px; color: #397461; display: flex; flex-shrink: 0; font-size: 1.1rem; height: 40px; justify-content: center; width: 40px; }
        .home-shortcut span { display: flex; flex: 1; flex-direction: column; gap: 3px; min-width: 0; }
        .home-shortcut strong { font-size: 0.88rem; font-weight: 700; }
        .home-shortcut small { color: #718594; font-size: 0.74rem; line-height: 1.35; }
        .home-shortcut-arrow { color: #8fa4b1; font-size: 0.8rem; }

        .home-panel-backdrop { background: rgba(17, 34, 46, 0.38); inset: 0; opacity: 0; pointer-events: none; position: fixed; transition: opacity 220ms ease; z-index: 60; }
        .home-control-panel { background: rgba(247, 251, 249, 0.97); border: 1px solid rgba(255, 255, 255, 0.8); border-radius: 28px; bottom: 22px; box-shadow: -18px 20px 60px rgba(23, 50, 71, 0.22); color: #173247; display: flex; flex-direction: column; max-width: calc(100vw - 32px); opacity: 0; overflow: hidden; position: fixed; right: 22px; top: 22px; transform: translateX(110%) scale(0.97); transition: opacity 220ms ease, transform 260ms cubic-bezier(0.2, 0.8, 0.2, 1); visibility: hidden; width: 560px; z-index: 61; }
        .home-panel-open .home-panel-backdrop { opacity: 1; pointer-events: auto; }
        .home-panel-open .home-control-panel { opacity: 1; transform: translateX(0) scale(1); visibility: visible; }
        .home-control-header { align-items: flex-start; border-bottom: 1px solid rgba(220, 227, 223, 0.8); display: flex; justify-content: space-between; padding: 28px 28px 22px; }
        .home-control-header h2 { font-size: 1.8rem; font-weight: 750; letter-spacing: -0.045em; margin: 7px 0 0; }
        .home-panel-close { align-items: center; background: rgba(225, 234, 229, 0.8); border: 0; border-radius: 50%; color: #49616d; cursor: pointer; display: flex; height: 38px; justify-content: center; width: 38px; }
        .home-panel-close:hover { background: #dce9e1; color: #173247; }
        .home-control-body { flex: 1; min-height: 0; overflow-y: auto; padding: 24px 28px 30px; }
        .home-panel-loading, .home-panel-error { align-items: center; color: #718594; display: flex; flex-direction: column; font-size: 0.82rem; gap: 10px; justify-content: center; min-height: 260px; text-align: center; }
        .home-panel-loading i { animation: home-panel-spin 1s linear infinite; color: #6b8f71; font-size: 1.5rem; }
        .home-panel-error { color: #9b5964; }
        .home-panel-error i { font-size: 1.5rem; }
        .home-control-date { color: #6b8f71; font-size: 0.82rem; font-weight: 750; margin: 0 0 17px; text-transform: capitalize; }
        .home-control-summary { display: grid; gap: 9px; grid-template-columns: repeat(3, 1fr); margin-bottom: 27px; }
        .home-control-summary div { background: rgba(255, 255, 255, 0.75); border: 1px solid #e0eae4; border-radius: 14px; padding: 13px 14px; }
        .home-control-summary strong, .home-control-summary span { display: block; }
        .home-control-summary strong { color: #173247; font-size: 1.45rem; letter-spacing: -0.04em; }
        .home-control-summary span { color: #718594; font-size: 0.7rem; margin-top: 2px; }
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
        .home-obligation-time { color: #397461; font-size: 0.76rem; font-variant-numeric: tabular-nums; font-weight: 800; min-width: 40px; }
        .home-obligation-copy { display: flex; flex: 1; flex-direction: column; gap: 3px; min-width: 0; }
        .home-obligation-copy strong { color: #2d4a5c; font-size: 0.8rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .home-obligation-copy small { color: #81929c; font-size: 0.7rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .home-obligation-link { color: #8ba1ad; font-size: 0.8rem; padding: 6px; }
        .home-obligation-link:hover { color: #397461; }
        .home-control-empty { color: #8999a2; font-size: 0.78rem; padding: 18px 0 8px; }
        @keyframes home-panel-spin { to { transform: rotate(360deg); } }

        .dark .home-page { background: #0d1620; }
        .dark .home-hero h1, .dark .home-section-heading h2 { color: #edf6fb; }
        .dark .home-copy { color: #9bb2c1; }
        .dark .home-shortcuts { border-color: #294252; }
        .dark .home-shortcut { background: #152532; border-color: #294252; color: #d9e7ee; }
        .dark .home-shortcut small { color: #9bb2c1; }
        .dark .home-control-panel { background: rgba(21, 37, 50, 0.98); border-color: #294252; color: #d9e7ee; }
        .dark .home-control-header, .dark .home-control-section { border-color: #294252; }
        .dark .home-control-header h2, .dark .home-control-section-heading h3, .dark .home-control-summary strong, .dark .home-obligation-copy strong { color: #d9e7ee; }
        .dark .home-control-summary div { background: #1b303f; border-color: #294252; }
        .dark .home-control-summary span, .dark .home-obligation-copy small { color: #9bb2c1; }
        .dark .home-obligation { border-color: #294252; }

        @media (max-width: 720px) {
            .home-container { padding-left: 16px; padding-right: 16px; }
            .home-hero { align-items: flex-start; flex-direction: column; gap: 42px; min-height: auto; padding-bottom: 65px; padding-top: 70px; }
            .home-hero-mark { align-self: flex-end; height: 140px; font-size: 3.4rem; margin-right: 8%; width: 140px; }
            .home-shortcut-grid { grid-template-columns: 1fr; }
            .home-control-panel { bottom: 10px; left: 10px; right: 10px; top: 10px; width: auto; }
        }
        @media (max-width: 420px) {
            .home-control-header { padding-left: 20px; padding-right: 20px; }
            .home-control-body { padding-left: 20px; padding-right: 20px; }
            .home-control-summary { gap: 6px; }
            .home-control-summary div { padding: 11px 9px; }
            .home-control-summary strong { font-size: 1.25rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .home-panel-backdrop, .home-control-panel, .home-panel-launch, .home-shortcut { transition: none; }
            .home-panel-loading i { animation: none; }
        }
    </style>

    @push('scripts')
        <script>
            (() => {
                const shell = document.querySelector('.home-page');
                const panel = document.getElementById('personal-control-panel');
                const openButtons = [...document.querySelectorAll('[data-personal-panel-open]')];
                const closeButtons = [...document.querySelectorAll('[data-personal-panel-close]')];
                const loading = panel?.querySelector('[data-panel-loading]');
                const error = panel?.querySelector('[data-panel-error]');
                const content = panel?.querySelector('[data-panel-content]');

                if (!shell || !panel || !openButtons.length || !loading || !error || !content) return;

                 let lastFocus = null;
                 let previousOverflow = '';
                 let loaded = false;
                const focusableSelector = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

                const text = (tag, className, value) => {
                    const element = document.createElement(tag);
                    element.className = className;
                    element.textContent = value;
                    return element;
                };

                const renderEmpty = (container, message) => {
                    container.replaceChildren(text('p', 'home-control-empty', message));
                };

                const renderPanel = (data) => {
                    panel.querySelector('[data-panel-date]').textContent = data.fecha;
                    panel.querySelector('[data-panel-pending]').textContent = data.tareas_pendientes;
                    panel.querySelector('[data-panel-overdue]').textContent = data.tareas_vencidas;
                    panel.querySelector('[data-panel-events-count]').textContent = data.eventos.length;

                    const tasks = panel.querySelector('[data-panel-tasks]');
                    tasks.replaceChildren();
                    if (!data.tareas.length) {
                        renderEmpty(tasks, 'No tenés tareas pendientes.');
                    } else {
                        data.tareas.forEach((task) => {
                            const row = document.createElement('div');
                            row.className = 'home-obligation';
                            row.append(text('span', `home-obligation-dot ${task.prioridad ?? ''}`, ''));
                            const copy = document.createElement('div');
                            copy.className = 'home-obligation-copy';
                            copy.append(text('strong', '', task.titulo));
                            copy.append(text('small', '', `${task.prioridad ? task.prioridad.charAt(0).toUpperCase() + task.prioridad.slice(1) : 'Tarea'}${task.fecha_limite ? ` · vence ${task.fecha_limite}` : ''}${task.legajo ? ` · ${task.legajo}` : ''}`));
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
                    }

                    const events = panel.querySelector('[data-panel-events]');
                    events.replaceChildren();
                    if (!data.eventos.length) {
                        renderEmpty(events, 'No hay eventos agendados para hoy.');
                    } else {
                        data.eventos.forEach((event) => {
                            const row = document.createElement('div');
                            row.className = 'home-obligation';
                            row.append(text('time', 'home-obligation-time', event.hora ?? '--:--'));
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
                    }
                };

                const loadPanel = () => {
                    if (loaded) return;
                    fetch(@json(route('personal-panel')), { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                        .then((response) => response.ok ? response.json() : Promise.reject(new Error('panel')))
                        .then((data) => {
                            renderPanel(data);
                            loaded = true;
                            loading.hidden = true;
                            error.hidden = true;
                            content.hidden = false;
                        })
                        .catch(() => {
                            loading.hidden = true;
                            error.hidden = false;
                        });
                };

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

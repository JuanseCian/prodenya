@extends('layouts.backend')

@section('title', 'Panel administrador')
@section('page-title', 'Panel administrador')
@section('page-description', 'Una vista clara para cuidar la operación diaria de PRODENYA.')

@push('styles')
    <style>
        .admin-dashboard { color: #162b3a; }
        .admin-hero { background: radial-gradient(circle at 86% 12%, rgba(156, 224, 191, 0.28), transparent 25%), linear-gradient(125deg, #123348 0%, #1a5360 58%, #2d735f 100%); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 24px; box-shadow: 0 20px 45px rgba(21, 52, 72, 0.16); color: #fff; overflow: hidden; padding: 32px; position: relative; }
        .admin-hero::after { border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 50%; content: ''; height: 280px; position: absolute; right: -100px; top: -145px; width: 280px; }
        .admin-hero-content { position: relative; z-index: 1; }
        .admin-eyebrow { color: #bcebd0; font-size: 0.69rem; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase; }
        .admin-hero h2 { font-size: clamp(1.65rem, 3vw, 2.25rem); font-weight: 800; letter-spacing: -0.045em; line-height: 1.08; margin: 10px 0 0; max-width: 560px; }
        .admin-hero p { color: rgba(255, 255, 255, 0.68); font-size: 0.9rem; line-height: 1.65; margin: 12px 0 0; max-width: 550px; }
        .admin-date { align-items: center; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.14); border-radius: 12px; color: rgba(255, 255, 255, 0.8); display: inline-flex; font-size: 0.76rem; gap: 8px; margin-top: 22px; padding: 9px 12px; }
        .admin-hero-mark { align-items: center; background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.18); border-radius: 18px; display: flex; font-size: 2rem; height: 74px; justify-content: center; position: absolute; right: 35px; top: 35px; width: 74px; }
        .admin-section-title { color: #162b3a; font-size: 1.08rem; font-weight: 800; letter-spacing: -0.025em; }
        .admin-section-copy { color: #71828a; font-size: 0.78rem; margin-top: 4px; }
        .admin-stat { background: rgba(255, 255, 255, 0.86); border: 1px solid #dce8e3; border-radius: 18px; box-shadow: 0 10px 28px rgba(21, 52, 72, 0.05); min-height: 142px; padding: 18px; position: relative; transition: border-color 180ms ease, transform 180ms ease, box-shadow 180ms ease; }
        .admin-stat:hover { border-color: #b9d4c5; box-shadow: 0 16px 32px rgba(21, 52, 72, 0.1); transform: translateY(-3px); }
        .admin-stat-icon { align-items: center; border-radius: 12px; display: flex; font-size: 1.1rem; height: 38px; justify-content: center; width: 38px; }
        .admin-stat-label { color: #71828a; font-size: 0.76rem; font-weight: 700; margin-top: 17px; }
        .admin-stat-value { color: #162b3a; font-size: 1.65rem; font-weight: 800; letter-spacing: -0.04em; line-height: 1; margin-top: 5px; }
        .admin-stat-purple .admin-stat-icon { background: #e9f4ee; color: #397461; }
        .admin-stat-blue .admin-stat-icon { background: #e6f1f6; color: #39728a; }
        .admin-stat-green .admin-stat-icon { background: #e3f2e9; color: #39875e; }
        .admin-stat-amber .admin-stat-icon { background: #fff3dc; color: #a56e24; }
        .admin-card { background: rgba(255, 255, 255, 0.9); border: 1px solid #dce8e3; border-radius: 19px; box-shadow: 0 10px 28px rgba(21, 52, 72, 0.045); }
        .admin-card-header { align-items: center; border-bottom: 1px solid #e8f0ec; display: flex; justify-content: space-between; padding: 19px 21px; }
        .admin-card-body { padding: 7px 21px 18px; }
        .admin-action { align-items: center; border: 1px solid #e1ece6; border-radius: 13px; color: #365160; display: flex; gap: 12px; padding: 13px; text-decoration: none; transition: background-color 180ms ease, border-color 180ms ease, transform 180ms ease; }
        .admin-action:hover { background: #f0f8f4; border-color: #b9d4c5; color: #286047; transform: translateX(2px); }
        .admin-action-icon { align-items: center; background: #e9f4ee; border-radius: 10px; color: #397461; display: flex; flex-shrink: 0; height: 35px; justify-content: center; width: 35px; }
        .admin-action strong { display: block; font-size: 0.82rem; }
        .admin-action span { color: #8b94a5; display: block; font-size: 0.7rem; margin-top: 3px; }
        .admin-action > i { color: #8cad9b; font-size: 0.78rem; margin-left: auto; }
        .admin-table { width: 100%; }
        .admin-table th { color: #8ba09a; font-size: 0.64rem; font-weight: 800; letter-spacing: 0.07em; padding: 11px 0; text-align: left; text-transform: uppercase; }
        .admin-table td { border-top: 1px solid #edf3f0; color: #5c7079; font-size: 0.78rem; padding: 13px 0; }
        .admin-table td:first-child { color: #294455; font-weight: 750; }
        .admin-table a { color: inherit; text-decoration: none; }
        .admin-table a:hover { color: #286047; }
        .admin-pill { background: #e9f4ee; border-radius: 999px; color: #397461; display: inline-flex; font-size: 0.64rem; font-weight: 800; padding: 5px 8px; }
        .admin-empty { color: #819197; font-size: 0.8rem; padding: 20px 0 8px; text-align: center; }

        @media (max-width: 700px) {
            .admin-hero { padding: 23px; }
            .admin-hero-mark { display: none; }
            .admin-stat { min-height: 126px; }
            .admin-table th:nth-child(3), .admin-table td:nth-child(3) { display: none; }
        }
    </style>
@endpush

@section('content')
    <div class="admin-dashboard space-y-6">
        <section class="admin-hero">
            <div class="admin-hero-content">
                <div class="admin-eyebrow">Centro de control</div>
                <h2>Una lectura simple para tomar mejores decisiones.</h2>
                <p>Supervisá el estado de la plataforma, encontrá accesos rápidos y mantené el trabajo del equipo en movimiento.</p>
                <div class="admin-date">
                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                    {{ now()->format('d/m/Y') }}
                </div>
            </div>
            <div class="admin-hero-mark" aria-hidden="true"><i class="bi bi-shield-check"></i></div>
        </section>

        <section aria-labelledby="admin-stats-title">
            <div class="mb-4">
                <h2 id="admin-stats-title" class="admin-section-title">Resumen operativo</h2>
                <p class="admin-section-copy">Los datos principales del sistema en un solo vistazo.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="admin-stat admin-stat-purple">
                    <div class="admin-stat-icon"><i class="bi bi-people"></i></div>
                    <div class="admin-stat-label">Legajos activos</div>
                    <div class="admin-stat-value">{{ number_format($legajosActivos, 0, ',', '.') }}</div>
                </div>
                <div class="admin-stat admin-stat-blue">
                    <div class="admin-stat-icon"><i class="bi bi-folder2-open"></i></div>
                    <div class="admin-stat-label">Legajos registrados</div>
                    <div class="admin-stat-value">{{ number_format($legajosRegistrados, 0, ',', '.') }}</div>
                </div>
                <div class="admin-stat admin-stat-green">
                    <div class="admin-stat-icon"><i class="bi bi-person-check"></i></div>
                    <div class="admin-stat-label">Usuarios activos</div>
                    <div class="admin-stat-value">{{ number_format($usuariosActivos, 0, ',', '.') }}</div>
                </div>
                <div class="admin-stat admin-stat-amber">
                    <div class="admin-stat-icon"><i class="bi bi-person-exclamation"></i></div>
                    <div class="admin-stat-label">Solicitudes pendientes</div>
                    <div class="admin-stat-value">{{ number_format($usuariosPendientes, 0, ',', '.') }}</div>
                </div>
                <div class="admin-stat admin-stat-amber">
                    <div class="admin-stat-icon"><i class="bi bi-envelope"></i></div>
                    <div class="admin-stat-label">Mensajes sin leer</div>
                    <div class="admin-stat-value">{{ number_format($mensajesNoLeidos, 0, ',', '.') }}</div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
            <section class="admin-card" aria-labelledby="admin-actions-title">
                <div class="admin-card-header">
                    <div>
                        <h2 id="admin-actions-title" class="admin-section-title">Accesos rápidos</h2>
                        <p class="admin-section-copy">Las tareas más frecuentes.</p>
                    </div>
                    <i class="bi bi-lightning-charge text-lg text-amber-500" aria-hidden="true"></i>
                </div>
                <div class="admin-card-body space-y-2">
                    @if (Route::has('legajos.index'))
                        <a href="{{ route('legajos.index') }}" class="admin-action">
                            <span class="admin-action-icon"><i class="bi bi-folder2-open"></i></span>
                            <span><strong>Revisar legajos</strong><span>Accedé al seguimiento de casos</span></span>
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    @endif
                    @if (Route::has('mailbox.index'))
                        <a href="{{ route('mailbox.index') }}" class="admin-action">
                            <span class="admin-action-icon"><i class="bi bi-envelope"></i></span>
                            <span><strong>Ver Mailbox</strong><span>Revisá la bandeja institucional</span></span>
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    @endif
                    @if (Route::has('admin.usuarios.index'))
                        <a href="{{ route('admin.usuarios.index') }}" class="admin-action">
                            <span class="admin-action-icon"><i class="bi bi-person-gear"></i></span>
                            <span><strong>Gestionar usuarios</strong><span>{{ $usuariosPendientes }} solicitudes pendientes</span></span>
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    @endif
                    @if (Route::has('admin.auditoria.index'))
                        <a href="{{ route('admin.auditoria.index') }}" class="admin-action">
                            <i class="bi bi-clipboard2-check" aria-hidden="true"></i>
                            <span><strong>Auditoría</strong><small>Revisar historial de acciones y cambios.</small></span>
                            <i class="bi bi-arrow-right admin-action-arrow" aria-hidden="true"></i>
                        </a>
                    @endif
                </div>
            </section>

            <section class="admin-card" aria-labelledby="admin-recent-title">
                <div class="admin-card-header">
                    <div>
                        <h2 id="admin-recent-title" class="admin-section-title">Actividad reciente</h2>
                        <p class="admin-section-copy">Últimos legajos incorporados al sistema.</p>
                    </div>
                    @if (Route::has('legajos.index'))
                        <a href="{{ route('legajos.index') }}" class="text-xs font-bold text-[#514b9f] no-underline hover:underline">Ver todos</a>
                    @endif
                </div>
                <div class="admin-card-body overflow-x-auto">
                    @if ($legajosRecientes->isNotEmpty())
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Persona</th>
                                    <th>Legajo</th>
                                    <th>Estado</th>
                                    <th>Alta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($legajosRecientes as $persona)
                                    <tr>
                                        <td>
                                            @if (Route::has('legajos.show'))
                                                <a href="{{ route('legajos.show', ['legajo' => $persona->id]) }}">{{ $persona->apellido }}, {{ $persona->nombre }}</a>
                                            @else
                                                {{ $persona->apellido }}, {{ $persona->nombre }}
                                            @endif
                                        </td>
                                        <td>{{ $persona->numero_legajo ?: '—' }}</td>
                                        <td><span class="admin-pill">{{ $persona->estado ?: 'Registrada' }}</span></td>
                                        <td>{{ $persona->creado_en?->format('d/m/Y') ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="admin-empty">Todavía no hay actividad para mostrar.</div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection

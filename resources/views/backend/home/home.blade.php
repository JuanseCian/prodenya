@extends('layouts.backend')

@section('title', 'Panel administrador')
@section('page-title', 'Panel administrador')
@section('page-description', 'Una vista clara para cuidar la operación diaria de PRODENYA.')

@push('styles')
    <style>
        .admin-dashboard { color: #26334a; }
        .admin-hero { background: radial-gradient(circle at 85% 10%, rgba(156, 224, 191, 0.24), transparent 28%), linear-gradient(125deg, #302b68, #514b9f); border-radius: 24px; color: #fff; overflow: hidden; padding: 28px; position: relative; }
        .admin-hero::after { border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 50%; content: ''; height: 280px; position: absolute; right: -100px; top: -145px; width: 280px; }
        .admin-hero-content { position: relative; z-index: 1; }
        .admin-eyebrow { color: #bfead0; font-size: 0.69rem; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase; }
        .admin-hero h2 { font-size: clamp(1.65rem, 3vw, 2.25rem); font-weight: 800; letter-spacing: -0.045em; line-height: 1.08; margin: 10px 0 0; max-width: 560px; }
        .admin-hero p { color: rgba(255, 255, 255, 0.68); font-size: 0.9rem; line-height: 1.65; margin: 12px 0 0; max-width: 550px; }
        .admin-date { align-items: center; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.14); border-radius: 12px; color: rgba(255, 255, 255, 0.8); display: inline-flex; font-size: 0.76rem; gap: 8px; margin-top: 22px; padding: 9px 12px; }
        .admin-hero-mark { align-items: center; background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.18); border-radius: 18px; display: flex; font-size: 2rem; height: 74px; justify-content: center; position: absolute; right: 35px; top: 35px; width: 74px; }
        .admin-section-title { color: #26334a; font-size: 1.08rem; font-weight: 800; letter-spacing: -0.025em; }
        .admin-section-copy { color: #7a8497; font-size: 0.78rem; margin-top: 4px; }
        .admin-stat { background: #fff; border: 1px solid #e7ebf2; border-radius: 18px; box-shadow: 0 8px 24px rgba(41, 52, 77, 0.045); min-height: 142px; padding: 18px; position: relative; transition: transform 180ms ease, box-shadow 180ms ease; }
        .admin-stat:hover { box-shadow: 0 15px 30px rgba(41, 52, 77, 0.09); transform: translateY(-3px); }
        .admin-stat-icon { align-items: center; border-radius: 12px; display: flex; font-size: 1.1rem; height: 38px; justify-content: center; width: 38px; }
        .admin-stat-label { color: #7c879a; font-size: 0.76rem; font-weight: 700; margin-top: 17px; }
        .admin-stat-value { color: #26334a; font-size: 1.65rem; font-weight: 800; letter-spacing: -0.04em; line-height: 1; margin-top: 5px; }
        .admin-stat-purple .admin-stat-icon { background: #efedff; color: #7169d0; }
        .admin-stat-blue .admin-stat-icon { background: #e8f4ff; color: #438fc7; }
        .admin-stat-green .admin-stat-icon { background: #e8f7ef; color: #3b9d6a; }
        .admin-stat-amber .admin-stat-icon { background: #fff5e4; color: #c38a2f; }
        .admin-card { background: #fff; border: 1px solid #e7ebf2; border-radius: 19px; box-shadow: 0 8px 24px rgba(41, 52, 77, 0.04); }
        .admin-card-header { align-items: center; border-bottom: 1px solid #eef0f5; display: flex; justify-content: space-between; padding: 19px 21px; }
        .admin-card-body { padding: 7px 21px 18px; }
        .admin-action { align-items: center; border: 1px solid #e8ebf2; border-radius: 13px; color: #3b4860; display: flex; gap: 12px; padding: 13px; text-decoration: none; transition: background-color 180ms ease, border-color 180ms ease, transform 180ms ease; }
        .admin-action:hover { background: #f8f8ff; border-color: #cbc7f0; color: #514b9f; transform: translateX(2px); }
        .admin-action-icon { align-items: center; background: #f0efff; border-radius: 10px; color: #6962bf; display: flex; flex-shrink: 0; height: 35px; justify-content: center; width: 35px; }
        .admin-action strong { display: block; font-size: 0.82rem; }
        .admin-action span { color: #8b94a5; display: block; font-size: 0.7rem; margin-top: 3px; }
        .admin-action > i { color: #a3aaba; font-size: 0.78rem; margin-left: auto; }
        .admin-table { width: 100%; }
        .admin-table th { color: #9aa2b1; font-size: 0.64rem; font-weight: 800; letter-spacing: 0.07em; padding: 11px 0; text-align: left; text-transform: uppercase; }
        .admin-table td { border-top: 1px solid #f0f2f6; color: #556177; font-size: 0.78rem; padding: 13px 0; }
        .admin-table td:first-child { color: #29364e; font-weight: 750; }
        .admin-table a { color: inherit; text-decoration: none; }
        .admin-table a:hover { color: #514b9f; }
        .admin-pill { background: #edf8f1; border-radius: 999px; color: #39875e; display: inline-flex; font-size: 0.64rem; font-weight: 800; padding: 5px 8px; }
        .admin-empty { color: #8b94a5; font-size: 0.8rem; padding: 20px 0 8px; text-align: center; }

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
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
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

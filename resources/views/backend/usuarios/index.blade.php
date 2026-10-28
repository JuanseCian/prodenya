@extends('layouts.backend')

@php
    $estadoLabels = [
        'pendiente' => 'Pendientes',
        'aprobado' => 'Aprobados',
        'rechazado' => 'Rechazados',
        'suspendido' => 'Suspendidos',
        'archivado' => 'Archivados',
    ];
    $estadoClasses = [
        'pendiente' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'aprobado' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'rechazado' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'suspendido' => 'bg-orange-50 text-orange-700 ring-orange-200',
        'archivado' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];
    $detailUrl = fn (?int $userId = null) => route('admin.usuarios.index', array_filter(array_merge(request()->except(['page', 'usuario']), $userId ? ['usuario' => $userId] : []), fn ($value) => $value !== null && $value !== ''));
@endphp

@section('title', 'Gestión de usuarios')
@section('page-title', 'Gestión de usuarios')
@section('page-description', 'Revisá solicitudes, estados y permisos de acceso.')

@push('styles')
    <style>
        .users-page { color: #162b3a; }
        .users-hero { background: radial-gradient(circle at 90% 0%, rgba(156, 224, 191, 0.23), transparent 28%), linear-gradient(125deg, #123348, #2d735f); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 22px; box-shadow: 0 18px 40px rgba(21, 52, 72, 0.14); color: #fff; overflow: hidden; padding: 28px; position: relative; }
        .users-hero::after { border: 1px solid rgba(255, 255, 255, 0.13); border-radius: 50%; content: ''; height: 250px; position: absolute; right: -90px; top: -135px; width: 250px; }
        .users-hero-content { position: relative; z-index: 1; }
        .users-eyebrow { color: #bfead0; font-size: 0.67rem; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase; }
        .users-hero h2 { font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 800; letter-spacing: -0.045em; margin: 8px 0 0; }
        .users-hero p { color: rgba(255, 255, 255, 0.7); font-size: 0.86rem; line-height: 1.55; margin: 9px 0 0; max-width: 570px; }
        .users-stat { background: rgba(255, 255, 255, 0.9); border: 1px solid #dce8e3; border-radius: 16px; box-shadow: 0 10px 24px rgba(21, 52, 72, 0.05); padding: 15px; }
        .users-stat:hover { border-color: #b9d4c5; background: #f6fbf8; }
        .users-stat strong { color: #162b3a; display: block; font-size: 1.45rem; letter-spacing: -0.04em; }
        .users-stat span { color: #71828a; display: block; font-size: 0.7rem; font-weight: 700; margin-top: 4px; }
        .users-filter { background: rgba(255, 255, 255, 0.9); border: 1px solid #dce8e3; border-radius: 18px; box-shadow: 0 10px 24px rgba(21, 52, 72, 0.035); }
        .users-status { border-radius: 999px; box-shadow: inset 0 0 0 1px; display: inline-flex; font-size: 0.65rem; font-weight: 800; padding: 5px 9px; }
        .users-detail-backdrop { background: rgba(15, 36, 48, 0.48); inset: 0; opacity: 0; pointer-events: none; position: fixed; transition: opacity 180ms ease; z-index: 50; }
        .users-detail { background: #fbfdfc; bottom: 0; box-shadow: -18px 0 50px rgba(21, 52, 72, 0.2); max-width: 520px; overflow-y: auto; position: fixed; right: 0; top: 0; transform: translateX(105%); transition: transform 220ms ease; width: min(100%, 520px); z-index: 51; }
        .users-detail.is-open { transform: translateX(0); }
        .users-detail-backdrop.is-open { opacity: 1; pointer-events: auto; }
        .users-detail-header { align-items: flex-start; border-bottom: 1px solid #e1ece6; display: flex; gap: 14px; justify-content: space-between; padding: 24px; }
        .users-detail-avatar { align-items: center; background: #e3f2e9; border-radius: 15px; color: #397461; display: flex; flex: 0 0 48px; font-size: 1.1rem; font-weight: 800; height: 48px; justify-content: center; }
        .users-detail h2 { color: #162b3a; font-size: 1.15rem; font-weight: 800; margin: 0; }
        .users-detail-email { color: #71828a; font-size: 0.78rem; margin-top: 4px; overflow-wrap: anywhere; }
        .users-detail-close { align-items: center; background: #f5f6fa; border: 0; border-radius: 9px; color: #647087; display: flex; height: 36px; justify-content: center; width: 36px; }
        .users-detail-close:hover { background: #eceef5; color: #26334a; }
        .users-detail-body { padding: 21px 24px 30px; }
        .users-detail-section { border-top: 1px solid #eef0f5; margin-top: 21px; padding-top: 18px; }
        .users-detail-section h3 { color: #26334a; font-size: 0.76rem; font-weight: 800; letter-spacing: 0.08em; margin: 0 0 12px; text-transform: uppercase; }
        .users-detail-grid { display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .users-detail-grid dt { color: #9aa2b1; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; }
        .users-detail-grid dd { color: #3b4860; font-size: 0.8rem; margin: 4px 0 0; overflow-wrap: anywhere; }
        .users-action-box { background: #f3f8f5; border: 1px solid #dce8e3; border-radius: 13px; padding: 13px; }
        .users-action-box + .users-action-box { margin-top: 10px; }
        .users-action-box h4 { color: #3b4860; font-size: 0.8rem; font-weight: 800; margin: 0 0 9px; }
        .users-timeline { display: grid; gap: 12px; }
        .users-timeline-item { border-left: 2px solid #d7e9dd; padding-left: 12px; }
        .users-timeline-item strong { color: #3b4860; display: block; font-size: 0.76rem; }
        .users-timeline-item small { color: #9aa2b1; display: block; font-size: 0.68rem; margin-top: 3px; }
        .users-page .bg-indigo-600 { background-color: #397461 !important; }
        .users-page .bg-indigo-600:hover { background-color: #286047 !important; }
        .users-page .text-indigo-600 { color: #397461 !important; }
        .users-page input:focus, .users-page select:focus, .users-page textarea:focus { border-color: #7db493 !important; box-shadow: 0 0 0 3px rgba(125, 180, 147, 0.18) !important; }

        @media (max-width: 700px) {
            .users-detail { max-width: none; }
            .users-hero { padding: 21px; }
        }
    </style>
@endpush

@section('content')
    <div class="users-page space-y-5">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"><i class="bi bi-check-circle mr-2"></i>{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700"><i class="bi bi-exclamation-circle mr-2"></i>{{ $errors->first() }}</div>
        @endif

        <section class="users-hero">
            <div class="users-hero-content">
                <div class="users-eyebrow">Control de acceso</div>
                <h2>Personas y permisos, en un solo lugar.</h2>
                <p>Revisá solicitudes pendientes, asigná roles y mantené el acceso de PRODENYA bajo control.</p>
            </div>
        </section>

        <section class="grid gap-3 grid-cols-2 lg:grid-cols-5" aria-label="Resumen de usuarios">
            @foreach ($estadoLabels as $estadoKey => $label)
                <a href="{{ route('admin.usuarios.index', ['estado' => $estadoKey]) }}" class="users-stat transition hover:-translate-y-0.5 hover:shadow-md">
                    <strong>{{ number_format($conteos[$estadoKey] ?? 0, 0, ',', '.') }}</strong>
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </section>

        <section class="users-filter p-4 md:p-5">
            <form method="GET" action="{{ route('admin.usuarios.index') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(220px,1.4fr)_180px_180px_150px_150px_150px_130px_auto]">
                <label class="relative block">
                    <span class="sr-only">Buscar usuario</span>
                    <i class="bi bi-search absolute top-1/2 left-3 -translate-y-1/2 text-slate-400"></i>
                    <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Nombre, email o DNI" class="w-full rounded-xl border border-slate-200 py-2.5 pr-3 pl-10 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </label>
                <select name="estado" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-600 outline-none focus:border-indigo-400">
                    <option value="">Todos los estados</option>
                    @foreach ($estadoLabels as $estadoKey => $label)
                        <option value="{{ $estadoKey }}" @selected($estado === $estadoKey)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="rol_id" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-600 outline-none focus:border-indigo-400">
                    <option value="">Todos los roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected((string) $rolId === (string) $role->id)>{{ ucfirst($role->nombre) }}</option>
                    @endforeach
                </select>
                <input type="date" name="desde" value="{{ request('desde') }}" aria-label="Desde" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-600 outline-none focus:border-indigo-400">
                <input type="date" name="hasta" value="{{ request('hasta') }}" aria-label="Hasta" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-600 outline-none focus:border-indigo-400">
                <select name="orden" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-600 outline-none focus:border-indigo-400"><option value="created_at" @selected($orden === 'created_at')>Orden: registro</option><option value="name" @selected($orden === 'name')>Orden: nombre</option><option value="ultimo_acceso" @selected($orden === 'ultimo_acceso')>Orden: acceso</option><option value="estado_cuenta" @selected($orden === 'estado_cuenta')>Orden: estado</option></select>
                <select name="direccion" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-600 outline-none focus:border-indigo-400"><option value="desc" @selected($direccion === 'desc')>Descendente</option><option value="asc" @selected($direccion === 'asc')>Ascendente</option></select>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700">Filtrar</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div><h2 class="text-base font-bold text-slate-800">Listado de usuarios</h2><p class="mt-1 text-xs text-slate-500">{{ $usuarios->total() }} registros encontrados. Seleccioná una fila para abrir el detalle.</p></div>
                @if ($buscar || $estado || $rolId || request('desde') || request('hasta'))
                    <a href="{{ route('admin.usuarios.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">Limpiar filtros</a>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left">
                    <thead class="bg-slate-50"><tr><th class="px-5 py-3 text-xs font-bold tracking-wide text-slate-500 uppercase">Usuario</th><th class="px-5 py-3 text-xs font-bold tracking-wide text-slate-500 uppercase">Rol</th><th class="px-5 py-3 text-xs font-bold tracking-wide text-slate-500 uppercase">Estado</th><th class="px-5 py-3 text-xs font-bold tracking-wide text-slate-500 uppercase">Registro</th><th class="px-5 py-3 text-right text-xs font-bold tracking-wide text-slate-500 uppercase">Detalle</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($usuarios as $usuario)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="px-5 py-4"><a href="{{ $detailUrl($usuario->id) }}" class="flex min-w-56 items-center gap-3 no-underline"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-xs font-bold text-indigo-600">{{ collect(explode(' ', trim($usuario->name)))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('') }}</span><span class="min-w-0"><strong class="block truncate text-sm font-bold text-slate-800">{{ $usuario->name }}</strong><small class="block truncate text-xs text-slate-500">{{ $usuario->email }}</small></span></a></td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ $usuario->rol?->nombre ? ucfirst($usuario->rol->nombre) : 'Sin asignar' }}</td>
                                <td class="px-5 py-4"><span class="users-status ring-1 {{ $estadoClasses[$usuario->estado_cuenta] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">{{ $estadoLabels[$usuario->estado_cuenta] ?? ucfirst($usuario->estado_cuenta) }}</span></td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-500">{{ $usuario->created_at?->format('d/m/Y') ?: '—' }}</td>
                                <td class="px-5 py-4 text-right"><a href="{{ $detailUrl($usuario->id) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600" aria-label="Abrir detalle"><i class="bi bi-arrow-up-right"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-16 text-center"><i class="bi bi-people text-3xl text-slate-300"></i><p class="mt-3 text-sm font-semibold text-slate-500">No hay usuarios para mostrar.</p><p class="mt-1 text-xs text-slate-400">Probá con otros filtros.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($usuarios->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $usuarios->links() }}</div>@endif
        </section>
    </div>

    @if ($seleccionado)
        <div class="users-detail-backdrop is-open" data-user-detail-close></div>
        <aside class="users-detail is-open" role="dialog" aria-modal="true" aria-labelledby="user-detail-title">
            <div class="users-detail-header">
                <div class="flex min-w-0 items-center gap-3"><span class="users-detail-avatar">{{ collect(explode(' ', trim($seleccionado->name)))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('') }}</span><div class="min-w-0"><h2 id="user-detail-title" class="truncate">{{ $seleccionado->name }}</h2><p class="users-detail-email">{{ $seleccionado->email }}</p></div></div>
                <a href="{{ $detailUrl() }}" class="users-detail-close" aria-label="Cerrar detalle"><i class="bi bi-x-lg"></i></a>
            </div>
            <div class="users-detail-body">
                <div class="flex flex-wrap items-center gap-2"><span class="users-status ring-1 {{ $estadoClasses[$seleccionado->estado_cuenta] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">{{ $estadoLabels[$seleccionado->estado_cuenta] ?? ucfirst($seleccionado->estado_cuenta) }}</span><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $seleccionado->rol?->nombre ? ucfirst($seleccionado->rol->nombre) : 'Sin rol' }}</span></div>

                <section class="users-detail-section"><h3>Datos de la cuenta</h3><dl class="users-detail-grid"><div><dt>DNI</dt><dd>{{ $seleccionado->dni ?: '—' }}</dd></div><div><dt>Teléfono</dt><dd>{{ $seleccionado->telefono ?: '—' }}</dd></div><div><dt>Profesión</dt><dd>{{ $seleccionado->profesion ?: '—' }}</dd></div><div><dt>Correo</dt><dd>{{ $seleccionado->hasVerifiedEmail() ? 'Verificado' : 'Sin verificar' }}</dd></div><div><dt>Registro</dt><dd>{{ $seleccionado->created_at?->format('d/m/Y H:i') ?: '—' }}</dd></div><div><dt>Último acceso</dt><dd>{{ $seleccionado->ultimo_acceso?->format('d/m/Y H:i') ?: 'Nunca' }}</dd></div></dl></section>

                @if ($seleccionado->estado_cuenta === 'pendiente')
                    <section class="users-detail-section"><h3>Resolver solicitud</h3><div class="users-action-box"><h4>Aprobar y asignar rol</h4><form method="POST" action="{{ route('admin.usuarios.aprobar', $seleccionado) }}" class="grid gap-3" data-profession-role-form>@csrf<label class="grid gap-1 text-xs font-bold text-slate-600">Rol<select name="rol_id" required class="min-w-0 rounded-lg border border-slate-200 px-3 py-2 text-sm" data-profession-role><option value="">Elegir rol</option>@foreach ($roles as $role)<option value="{{ $role->id }}" data-role-name="{{ $role->nombre }}">{{ ucfirst($role->nombre) }}</option>@endforeach</select></label><label class="grid gap-1 text-xs font-bold text-slate-600" data-profession-field>Profesión<select name="profesion" class="min-w-0 rounded-lg border border-slate-200 px-3 py-2 text-sm" data-profession-select><option value="">Seleccionar profesión</option>@foreach ($profesiones as $profesion)<option value="{{ $profesion }}" @selected($seleccionado->profesion === $profesion)>{{ $profesion }}</option>@endforeach</select><span class="text-[11px] font-normal text-slate-400">Obligatoria para el rol Profesional.</span></label><button class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700">Aprobar</button></form></div><div class="users-action-box"><h4>Rechazar solicitud</h4><form method="POST" action="{{ route('admin.usuarios.rechazar', $seleccionado) }}">@csrf<textarea name="motivo" required rows="3" placeholder="Motivo obligatorio" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></textarea><button class="mt-2 rounded-lg bg-rose-600 px-3 py-2 text-xs font-bold text-white hover:bg-rose-700">Rechazar</button></form></div></section>
                @endif

                @if ($seleccionado->estado_cuenta === 'rechazado')
                    <section class="users-detail-section"><h3>Solicitud</h3><div class="rounded-xl border border-rose-100 bg-rose-50 p-3 text-sm text-rose-700"><strong>Motivo:</strong> {{ $seleccionado->motivo_rechazo ?: 'Sin motivo registrado.' }}</div><form method="POST" action="{{ route('admin.usuarios.reabrir', $seleccionado) }}" class="mt-3">@csrf<button class="w-full rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-100">Devolver a pendientes</button></form></section>
                @endif

                @if ($seleccionado->estado_cuenta === 'aprobado')
                    <section class="users-detail-section"><h3>Permisos</h3><div class="users-action-box"><h4>Cambiar rol</h4><form method="POST" action="{{ route('admin.usuarios.rol', $seleccionado) }}" class="grid gap-3" data-profession-role-form>@csrf @method('PATCH')<label class="grid gap-1 text-xs font-bold text-slate-600">Rol<select name="rol_id" required class="min-w-0 rounded-lg border border-slate-200 px-3 py-2 text-sm" data-profession-role>@foreach ($roles as $role)<option value="{{ $role->id }}" data-role-name="{{ $role->nombre }}" @selected($seleccionado->rol_id === $role->id)>{{ ucfirst($role->nombre) }}</option>@endforeach</select></label><label class="grid gap-1 text-xs font-bold text-slate-600" data-profession-field>Profesión<select name="profesion" class="min-w-0 rounded-lg border border-slate-200 px-3 py-2 text-sm" data-profession-select><option value="">Seleccionar profesión</option>@foreach ($profesiones as $profesion)<option value="{{ $profesion }}" @selected($seleccionado->profesion === $profesion)>{{ $profesion }}</option>@endforeach</select><span class="text-[11px] font-normal text-slate-400">Obligatoria para el rol Profesional.</span></label><button class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white hover:bg-indigo-700">Guardar</button></form></div></section>
                @endif

                @if ($seleccionado->motivo_suspension)
                    <section class="users-detail-section"><h3>Suspensión</h3><div class="rounded-xl border border-orange-100 bg-orange-50 p-3 text-sm text-orange-700">{{ $seleccionado->motivo_suspension }}</div></section>
                @endif

                @if ($seleccionado->estado_cuenta === 'aprobado' && $seleccionado->id !== auth()->id())
                    <section class="users-detail-section"><h3>Acciones de acceso</h3><div class="users-action-box"><h4>Suspender cuenta</h4><form method="POST" action="{{ route('admin.usuarios.suspender', $seleccionado) }}">@csrf<textarea name="motivo" required rows="2" placeholder="Motivo obligatorio" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></textarea><button class="mt-2 rounded-lg bg-orange-600 px-3 py-2 text-xs font-bold text-white hover:bg-orange-700">Suspender</button></form></div><form method="POST" action="{{ route('admin.usuarios.archivar', $seleccionado) }}" onsubmit="return confirm('¿Archivar esta cuenta?');">@csrf<button class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">Archivar cuenta</button></form></section>
                @elseif ($seleccionado->estado_cuenta === 'suspendido')
                    <section class="users-detail-section"><h3>Acciones de acceso</h3><form method="POST" action="{{ route('admin.usuarios.reactivar', $seleccionado) }}">@csrf<button class="w-full rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700">Reactivar cuenta</button></form></section>
                @elseif ($seleccionado->estado_cuenta === 'archivado')
                    <section class="users-detail-section"><h3>Archivo</h3><form method="POST" action="{{ route('admin.usuarios.restaurar', $seleccionado) }}">@csrf<button class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white hover:bg-indigo-700">Restaurar cuenta</button></form></section>
                @endif

                <section class="users-detail-section"><h3>Actividad administrativa</h3>@if ($actividad->isNotEmpty())<div class="users-timeline">@foreach ($actividad as $evento)<div class="users-timeline-item"><strong>{{ ucfirst(str_replace('_', ' ', $evento->evento)) }}</strong><small>{{ $evento->creado_en?->format('d/m/Y H:i') }} · {{ $evento->usuario?->name ?: 'Sistema' }}</small></div>@endforeach</div>@else<p class="text-xs text-slate-400">Todavía no hay acciones administrativas registradas.</p>@endif</section>
            </div>
        </aside>
        <script>
            (() => {
                const backdrop = document.querySelector('[data-user-detail-close]');
                const close = () => { if (backdrop) window.location.href = @json($detailUrl()); };
                backdrop?.addEventListener('click', close);
                document.addEventListener('keydown', (event) => { if (event.key === 'Escape') close(); });

                document.querySelectorAll('[data-profession-role-form]').forEach((form) => {
                    const role = form.querySelector('[data-profession-role]');
                    const field = form.querySelector('[data-profession-field]');
                    const select = form.querySelector('[data-profession-select]');
                    const refresh = () => {
                        const isProfessional = role?.selectedOptions[0]?.dataset.roleName === 'profesional';
                        field?.classList.toggle('hidden', !isProfessional);
                        if (select) {
                            select.required = isProfessional;
                            select.disabled = !isProfessional;
                        }
                    };

                    role?.addEventListener('change', refresh);
                    refresh();
                });
            })();
        </script>
    @endif
@endsection

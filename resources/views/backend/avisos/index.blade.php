@extends('layouts.backend')

@section('title', 'Avisos')
@section('page-title', 'Avisos')
@section('page-description', 'Publicá avisos y notificá a todos los usuarios del sistema.')

@push('styles')
    <style>
        .catalog-page { color: #162b3a; }
        .catalog-page .catalog-hero { background: rgba(255, 255, 255, 0.9); border-color: #dce8e3; box-shadow: 0 10px 28px rgba(21, 52, 72, 0.045); }
        .catalog-page .catalog-kicker { color: #397461; }
        .catalog-page h2 { color: #162b3a; }
        .catalog-page input:focus, .catalog-page select:focus { border-color: #7db493 !important; box-shadow: 0 0 0 3px rgba(125, 180, 147, 0.18) !important; }
        .catalog-page .catalog-primary { background: #397461; }
        .catalog-page .catalog-primary:hover { background: #286047; }
        .catalog-page .catalog-table { border-color: #dce8e3; box-shadow: 0 10px 28px rgba(21, 52, 72, 0.045); }
        .catalog-page .catalog-table thead { background: #f3f8f5; border-bottom: 1px solid #dce8e3; }
        .catalog-page .catalog-table thead th { color: #3b6a57; letter-spacing: 0.09em; }
        .catalog-page .catalog-table tbody tr:nth-child(even) { background: #fafcfb; }
        .catalog-page .catalog-table tbody tr:hover { background: #f1f8f4; }

        .catalog-action-edit { background: rgba(57, 116, 97, 0.06); border-color: rgba(57, 116, 97, 0.22); color: #397461; }
        .catalog-action-edit:hover { background: rgba(57, 116, 97, 0.14); border-color: rgba(57, 116, 97, 0.4); box-shadow: 0 4px 10px rgba(57, 116, 97, 0.14); color: #286047; }
        .catalog-action-delete { background: rgba(185, 58, 77, 0.055); border-color: rgba(185, 58, 77, 0.2); color: #b33a4d; }
        .catalog-action-delete:hover { background: rgba(185, 58, 77, 0.12); border-color: rgba(185, 58, 77, 0.38); box-shadow: 0 4px 10px rgba(185, 58, 77, 0.14); color: #962f40; }
        .catalog-action-restore { background: rgba(57, 116, 97, 0.06); border-color: rgba(57, 116, 97, 0.22); color: #397461; }
        .catalog-action-restore:hover { background: rgba(57, 116, 97, 0.14); border-color: rgba(57, 116, 97, 0.4); box-shadow: 0 4px 10px rgba(57, 116, 97, 0.14); color: #286047; }

        html.dark .catalog-page { color: var(--admin-ink); }
        html.dark .catalog-page .catalog-hero { background: var(--admin-soft); border-color: var(--admin-line); }
        html.dark .catalog-page .catalog-kicker { color: #8bc39a; }
        html.dark .catalog-page h2 { color: var(--admin-ink); }
        html.dark .catalog-page input:focus, html.dark .catalog-page select:focus { border-color: #4a916f !important; box-shadow: 0 0 0 3px rgba(139, 195, 154, 0.18) !important; }
        html.dark .catalog-page .catalog-primary { background: #2f6350; }
        html.dark .catalog-page .catalog-primary:hover { background: #26503f; }
        html.dark .catalog-page .catalog-table { background: var(--admin-soft); border-color: var(--admin-line); }
        html.dark .catalog-page .catalog-table thead { background: #1d2f39; border-color: var(--admin-line); }
        html.dark .catalog-page .catalog-table thead th { color: #8bc39a; }
        html.dark .catalog-page .catalog-table tbody tr:nth-child(even) { background: rgba(255, 255, 255, 0.02); }
        html.dark .catalog-page .catalog-table tbody tr:hover { background: rgba(139, 195, 154, 0.08); }
        html.dark .catalog-action-edit { background: rgba(139, 195, 154, 0.1); border-color: rgba(139, 195, 154, 0.3); color: #8bc39a; }
        html.dark .catalog-action-edit:hover { background: rgba(139, 195, 154, 0.18); border-color: rgba(139, 195, 154, 0.46); color: #b9e8cc; }
        html.dark .catalog-action-delete { background: rgba(213, 128, 112, 0.1); border-color: rgba(213, 128, 112, 0.3); color: #d58070; }
        html.dark .catalog-action-delete:hover { background: rgba(213, 128, 112, 0.18); border-color: rgba(213, 128, 112, 0.46); color: #e99b8e; }
        html.dark .catalog-action-restore { background: rgba(139, 195, 154, 0.1); border-color: rgba(139, 195, 154, 0.3); color: #8bc39a; }
        html.dark .catalog-action-restore:hover { background: rgba(139, 195, 154, 0.18); border-color: rgba(139, 195, 154, 0.46); color: #b9e8cc; }
    </style>
@endpush

@section('content')
    <div class="catalog-page space-y-6">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                <i class="bi bi-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <section class="catalog-hero rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="catalog-kicker text-xs font-bold tracking-[0.15em] text-indigo-500 uppercase">Comunicación</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Avisos</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $avisos->total() }} {{ $avisos->total() === 1 ? 'aviso publicado' : 'avisos publicados' }}.</p>
                </div>
                <a href="{{ route('admin.avisos.create') }}" class="catalog-primary inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700">
                    <i class="bi bi-megaphone"></i>
                    Nuevo aviso
                </a>
            </div>

            <form method="GET" action="{{ route('admin.avisos.index') }}" class="mt-6 flex flex-col gap-3 sm:flex-row">
                <label class="relative block flex-1">
                    <span class="sr-only">Buscar</span>
                    <i class="bi bi-search absolute top-1/2 left-3 -translate-y-1/2 text-slate-400"></i>
                    <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Buscar por título o mensaje..." class="w-full rounded-xl border border-slate-200 py-2.5 pr-3 pl-10 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </label>
                <select name="estado" class="rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    <option value="" @selected($estado === '')>Todos los estados</option>
                    <option value="activo" @selected($estado === 'activo')>Activos</option>
                    <option value="inactivo" @selected($estado === 'inactivo')>Inactivos</option>
                </select>
                <button type="submit" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Buscar</button>
                @if ($buscar !== '' || $estado !== '')
                    <a href="{{ route('admin.avisos.index') }}" class="rounded-xl px-3 py-2.5 text-center text-sm font-bold text-slate-500 transition hover:bg-slate-50">Limpiar</a>
                @endif
            </form>
        </section>

        <section class="catalog-table overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="whitespace-nowrap px-5 py-3 text-xs font-bold tracking-wide text-slate-500 uppercase">Título</th>
                            <th class="whitespace-nowrap px-5 py-3 text-xs font-bold tracking-wide text-slate-500 uppercase">Mensaje</th>
                            <th class="whitespace-nowrap px-5 py-3 text-xs font-bold tracking-wide text-slate-500 uppercase">Publicado por</th>
                            <th class="whitespace-nowrap px-5 py-3 text-xs font-bold tracking-wide text-slate-500 uppercase">Fecha</th>
                            <th class="whitespace-nowrap px-5 py-3 text-xs font-bold tracking-wide text-slate-500 uppercase">Estado</th>
                            <th class="px-5 py-3 text-right text-xs font-bold tracking-wide text-slate-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($avisos as $aviso)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="max-w-xs px-5 py-4 text-sm font-bold text-slate-800">{{ $aviso->titulo }}</td>
                                <td class="max-w-sm px-5 py-4 text-sm text-slate-600">
                                    <span class="line-clamp-2">{{ $aviso->mensaje }}</span>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ $aviso->creadoPor?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ $aviso->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $aviso->activo ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $aviso->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.avisos.edit', $aviso) }}" class="catalog-action-edit inline-flex h-9 w-9 items-center justify-center rounded-lg border transition" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if ($aviso->activo)
                                            <form method="POST" action="{{ route('admin.avisos.desactivar', $aviso) }}" onsubmit="return confirm('¿Desactivar este aviso?');">
                                                @csrf
                                                <button type="submit" class="catalog-action-delete inline-flex h-9 w-9 items-center justify-center rounded-lg border transition" title="Desactivar">
                                                    <i class="bi bi-slash-circle"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.avisos.reactivar', $aviso) }}">
                                                @csrf
                                                <button type="submit" class="catalog-action-restore inline-flex h-9 w-9 items-center justify-center rounded-lg border transition" title="Reactivar">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <i class="bi bi-megaphone text-3xl text-slate-300"></i>
                                    <p class="mt-3 text-sm font-semibold text-slate-500">No hay avisos para mostrar.</p>
                                    <p class="mt-1 text-xs text-slate-400">Podés crear el primero con el botón superior.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($avisos->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $avisos->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection

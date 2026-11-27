@extends('layouts.backend')

@php($editing = $aviso !== null)

@section('title', ($editing ? 'Editar aviso' : 'Nuevo aviso'))
@section('page-title', ($editing ? 'Editar aviso' : 'Nuevo aviso'))
@section('page-description', $editing ? 'Actualizá el contenido del aviso.' : 'Al publicarlo se notificará a todos los usuarios activos del sistema.')

@push('styles')
    <style>
        .catalog-form { color: #162b3a; }
        .catalog-form .catalog-form-card { border-color: #dce8e3; box-shadow: 0 14px 34px rgba(21, 52, 72, 0.06); }
        .catalog-form .catalog-form-head, .catalog-form .catalog-form-actions { background: #f3f8f5; border-color: #e1ece6; }
        .catalog-form .catalog-kicker { color: #397461; }
        .catalog-form h2 { color: #162b3a; }
        .catalog-form input:focus, .catalog-form textarea:focus { border-color: #7db493 !important; box-shadow: 0 0 0 3px rgba(125, 180, 147, 0.18) !important; }
        .catalog-form .catalog-submit { background: #397461; }
        .catalog-form .catalog-submit:hover { background: #286047; }

        html.dark .catalog-form { color: var(--admin-ink); }
        html.dark .catalog-form .catalog-form-card { background: var(--admin-soft); border-color: var(--admin-line); }
        html.dark .catalog-form .catalog-form-head, html.dark .catalog-form .catalog-form-actions { background: #1d2f39; border-color: var(--admin-line); }
        html.dark .catalog-form .catalog-kicker { color: #8bc39a; }
        html.dark .catalog-form h2 { color: var(--admin-ink); }
        html.dark .catalog-form input, html.dark .catalog-form textarea { background: #172832; border-color: var(--admin-line); color: var(--admin-ink); }
        html.dark .catalog-form input:focus, html.dark .catalog-form textarea:focus { border-color: #4a916f !important; box-shadow: 0 0 0 3px rgba(139, 195, 154, 0.18) !important; }
        html.dark .catalog-form .catalog-submit { background: #2f6350; }
        html.dark .catalog-form .catalog-submit:hover { background: #26503f; }
    </style>
@endpush

@section('content')
    <div class="catalog-form mx-auto max-w-3xl">
        <div class="mb-6 flex items-center gap-3 text-sm text-slate-500">
            <a href="{{ route('admin.avisos.index') }}" class="transition hover:text-indigo-600">Avisos</a>
            <i class="bi bi-chevron-right text-xs"></i>
            <span>{{ $editing ? 'Editar' : 'Nuevo' }}</span>
        </div>

        <form method="POST" action="{{ $editing ? route('admin.avisos.update', $aviso) : route('admin.avisos.store') }}" class="catalog-form-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="catalog-form-head border-b border-slate-100 bg-slate-50 px-6 py-5">
                <p class="catalog-kicker text-xs font-bold tracking-[0.15em] text-indigo-500 uppercase">Aviso</p>
                <h2 class="mt-2 text-xl font-bold text-slate-900">{{ $editing ? 'Actualizar aviso' : 'Publicar aviso' }}</h2>
                @unless ($editing)
                    <p class="mt-1 text-sm text-slate-500">Se enviará una notificación a todos los usuarios activos del sistema.</p>
                @endunless
            </div>

            <div class="space-y-5 px-6 py-6">
                <div>
                    <label for="titulo" class="mb-2 block text-sm font-bold text-slate-700">Título <span class="text-rose-500">*</span></label>
                    <input id="titulo" type="text" name="titulo" value="{{ old('titulo', $aviso?->titulo) }}" maxlength="190" required class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    @error('titulo')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="mensaje" class="mb-2 block text-sm font-bold text-slate-700">Mensaje <span class="text-rose-500">*</span></label>
                    <textarea id="mensaje" name="mensaje" rows="5" maxlength="2000" required class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">{{ old('mensaje', $aviso?->mensaje) }}</textarea>
                    @error('mensaje')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="url" class="mb-2 block text-sm font-bold text-slate-700">Enlace (opcional)</label>
                    <input id="url" type="text" name="url" value="{{ old('url', $aviso?->url) }}" maxlength="255" placeholder="https://..." class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    <p class="mt-1.5 text-xs text-slate-400">Si lo completás, al tocar la notificación el usuario será redirigido a esta dirección.</p>
                    @error('url')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="catalog-form-actions flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50 px-6 py-5 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.avisos.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-bold text-slate-500 transition hover:bg-slate-100">Cancelar</a>
                <button type="submit" class="catalog-submit inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700">
                    <i class="bi bi-{{ $editing ? 'check2' : 'megaphone' }}"></i>
                    {{ $editing ? 'Guardar cambios' : 'Publicar y notificar' }}
                </button>
            </div>
        </form>
    </div>
@endsection

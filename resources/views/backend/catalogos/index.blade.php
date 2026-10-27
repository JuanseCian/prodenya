@extends('layouts.backend')

@section('title', $definition['title'])
@section('page-title', $definition['title'])
@section('page-description', 'Administrá los valores disponibles para el sistema.')

@push('styles')
    <style>
        .catalog-page { color: #162b3a; }
        .catalog-page .catalog-hero { background: rgba(255, 255, 255, 0.9); border-color: #dce8e3; box-shadow: 0 10px 28px rgba(21, 52, 72, 0.045); }
        .catalog-page .catalog-kicker { color: #397461; }
        .catalog-page h2 { color: #162b3a; }
        .catalog-page input:focus { border-color: #7db493 !important; box-shadow: 0 0 0 3px rgba(125, 180, 147, 0.18) !important; }
        .catalog-page .catalog-primary { background: #397461; }
        .catalog-page .catalog-primary:hover { background: #286047; }
        .catalog-page .catalog-table { border-color: #dce8e3; box-shadow: 0 10px 28px rgba(21, 52, 72, 0.045); }
        .catalog-page .catalog-table thead { background: #f3f8f5; }
        .catalog-page .catalog-table tbody tr:hover { background: #f6fbf8; }
        .catalog-page .catalog-table a:hover { color: #397461; }
    </style>
@endpush

@section('content')
    <div class="catalog-page space-y-6">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                <i class="bi bi-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if ($errors->has('general'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                <i class="bi bi-exclamation-circle mr-2"></i>{{ $errors->first('general') }}
            </div>
        @endif

        <section class="catalog-hero rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="catalog-kicker text-xs font-bold tracking-[0.15em] text-indigo-500 uppercase">Catálogo administrativo</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $definition['title'] }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $items->total() }} {{ $items->total() === 1 ? 'registro disponible' : 'registros disponibles' }}.</p>
                </div>
                    <a href="{{ route('admin.catalogos.create', ['catalog' => $catalog]) }}" class="catalog-primary inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700">
                    <i class="bi bi-plus-lg"></i>
                    Nuevo {{ strtolower($definition['singular']) }}
                </a>
            </div>

            <form method="GET" action="{{ route('admin.catalogos.index', ['catalog' => $catalog]) }}" class="mt-6 flex flex-col gap-3 sm:flex-row">
                <label class="relative block flex-1">
                    <span class="sr-only">Buscar</span>
                    <i class="bi bi-search absolute top-1/2 left-3 -translate-y-1/2 text-slate-400"></i>
                    <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Buscar en {{ strtolower($definition['title']) }}..." class="w-full rounded-xl border border-slate-200 py-2.5 pr-3 pl-10 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </label>
                <button type="submit" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Buscar</button>
                @if ($buscar !== '')
                    <a href="{{ route('admin.catalogos.index', ['catalog' => $catalog]) }}" class="rounded-xl px-3 py-2.5 text-center text-sm font-bold text-slate-500 transition hover:bg-slate-50">Limpiar</a>
                @endif
            </form>
        </section>

        <section class="catalog-table overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left">
                    <thead class="bg-slate-50">
                        <tr>
                            @foreach ($definition['columns'] as $column)
                                <th class="whitespace-nowrap px-5 py-3 text-xs font-bold tracking-wide text-slate-500 uppercase">{{ $column['label'] }}</th>
                            @endforeach
                            <th class="px-5 py-3 text-right text-xs font-bold tracking-wide text-slate-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($items as $item)
                            <tr class="transition hover:bg-slate-50/70">
                                @foreach ($definition['columns'] as $column)
                                    @php($value = data_get($item, $column['key']))
                                    <td class="max-w-xs px-5 py-4 text-sm text-slate-600">
                                        @if (($column['type'] ?? null) === 'boolean')
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $value ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                                {{ $value ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        @else
                                            <span class="{{ $loop->first ? 'font-bold text-slate-800' : '' }}">{{ $value ?: '—' }}</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.catalogos.edit', ['catalog' => $catalog, 'item' => $item->getKey()]) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.catalogos.destroy', ['catalog' => $catalog, 'item' => $item->getKey()]) }}" onsubmit="return confirm('¿Eliminar este registro?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600" title="Eliminar">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($definition['columns']) + 1 }}" class="px-5 py-14 text-center">
                                    <i class="bi bi-inbox text-3xl text-slate-300"></i>
                                    <p class="mt-3 text-sm font-semibold text-slate-500">No hay registros para mostrar.</p>
                                    <p class="mt-1 text-xs text-slate-400">Podés crear el primero con el botón superior.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $items->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection

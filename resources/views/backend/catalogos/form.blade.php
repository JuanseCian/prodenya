@extends('layouts.backend')

@php($editing = $item !== null)

@section('title', ($editing ? 'Editar ' : 'Nuevo ').$definition['singular'])
@section('page-title', ($editing ? 'Editar ' : 'Nuevo ').$definition['singular'])
@section('page-description', 'Completá los datos del catálogo administrativo.')

@push('styles')
    <style>
        .catalog-form { color: #162b3a; }
        .catalog-form .catalog-form-card { border-color: #dce8e3; box-shadow: 0 14px 34px rgba(21, 52, 72, 0.06); }
        .catalog-form .catalog-form-head, .catalog-form .catalog-form-actions { background: #f3f8f5; border-color: #e1ece6; }
        .catalog-form .catalog-kicker { color: #397461; }
        .catalog-form h2 { color: #162b3a; }
        .catalog-form input:focus, .catalog-form select:focus, .catalog-form textarea:focus { border-color: #7db493 !important; box-shadow: 0 0 0 3px rgba(125, 180, 147, 0.18) !important; }
        .catalog-form .catalog-submit { background: #397461; }
        .catalog-form .catalog-submit:hover { background: #286047; }
    </style>
@endpush

@section('content')
    <div class="catalog-form mx-auto max-w-3xl">
        <div class="mb-6 flex items-center gap-3 text-sm text-slate-500">
            <a href="{{ route('admin.catalogos.index', ['catalog' => $catalog]) }}" class="transition hover:text-indigo-600">{{ $definition['title'] }}</a>
            <i class="bi bi-chevron-right text-xs"></i>
            <span>{{ $editing ? 'Editar' : 'Nuevo' }}</span>
        </div>

        @if ($errors->has('general'))
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                <i class="bi bi-exclamation-circle mr-2"></i>{{ $errors->first('general') }}
            </div>
        @endif

        <form method="POST" action="{{ $editing ? route('admin.catalogos.update', ['catalog' => $catalog, 'item' => $item->getKey()]) : route('admin.catalogos.store', ['catalog' => $catalog]) }}" class="catalog-form-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="catalog-form-head border-b border-slate-100 bg-slate-50 px-6 py-5">
                <p class="catalog-kicker text-xs font-bold tracking-[0.15em] text-indigo-500 uppercase">Datos del catálogo</p>
                <h2 class="mt-2 text-xl font-bold text-slate-900">{{ $editing ? 'Actualizar registro' : 'Crear registro' }}</h2>
            </div>

            <div class="space-y-5 px-6 py-6">
                @foreach ($definition['fields'] as $field)
                    @php($fieldValue = old($field['name'], $editing ? data_get($item, $field['name']) : ($field['type'] === 'checkbox' ? true : '')))
                    @if ($field['type'] === 'checkbox')
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <input type="hidden" name="{{ $field['name'] }}" value="0">
                            <label class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-700">
                                <input type="checkbox" name="{{ $field['name'] }}" value="1" @checked($fieldValue) class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                {{ $field['label'] }}
                            </label>
                        </div>
                    @elseif ($field['type'] === 'select')
                        <div>
                            <label for="{{ $field['name'] }}" class="mb-2 block text-sm font-bold text-slate-700">{{ $field['label'] }} @if ($field['required'] ?? false)<span class="text-rose-500">*</span>@endif</label>
                            <select id="{{ $field['name'] }}" name="{{ $field['name'] }}" @required($field['required'] ?? false) class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                                <option value="">Seleccionar...</option>
                                @foreach (($options[$field['option_key']] ?? []) as $option)
                                    <option value="{{ data_get($option, 'id') }}" @selected((string) $fieldValue === (string) data_get($option, 'id'))>{{ data_get($option, 'nombre') }}</option>
                                @endforeach
                            </select>
                            @error($field['name'])<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    @elseif ($field['type'] === 'textarea')
                        <div>
                            <label for="{{ $field['name'] }}" class="mb-2 block text-sm font-bold text-slate-700">{{ $field['label'] }}</label>
                            <textarea id="{{ $field['name'] }}" name="{{ $field['name'] }}" rows="4" maxlength="{{ $field['maxlength'] ?? '' }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">{{ $fieldValue }}</textarea>
                            @error($field['name'])<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    @else
                        <div>
                            <label for="{{ $field['name'] }}" class="mb-2 block text-sm font-bold text-slate-700">{{ $field['label'] }} @if ($field['required'] ?? false)<span class="text-rose-500">*</span>@endif</label>
                            <input id="{{ $field['name'] }}" type="text" name="{{ $field['name'] }}" value="{{ $fieldValue }}" maxlength="{{ $field['maxlength'] ?? '' }}" @required($field['required'] ?? false) class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                            @error($field['name'])<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="catalog-form-actions flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50 px-6 py-5 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.catalogos.index', ['catalog' => $catalog]) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-bold text-slate-500 transition hover:bg-slate-100">Cancelar</a>
                <button type="submit" class="catalog-submit inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700">
                    <i class="bi bi-check2"></i>
                    {{ $editing ? 'Guardar cambios' : 'Crear registro' }}
                </button>
            </div>
        </form>
    </div>
@endsection

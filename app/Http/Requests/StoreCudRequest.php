<?php

namespace App\Http\Requests;

use App\Models\Legajo;
use Illuminate\Foundation\Http\FormRequest;

class StoreCudRequest extends FormRequest
{
    public function authorize(): bool
    {
        $legajo = $this->route('legajo');

        return ($this->user()?->esAdmin() || $this->user()?->tieneRol('coordinador'))
            && $legajo instanceof Legajo
            && $this->user()->puedeVerSeccionLegajo('persona');
    }

    public function rules(): array
    {
        return [
            'numero_cud' => ['required', 'string', 'max:80'],
            'fecha_vigencia' => ['required', 'date'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:fecha_vigencia'],
            'observaciones' => ['nullable', 'string', 'max:10000'],
            'frente' => ['nullable', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png'],
            'dorso' => ['nullable', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png'],
            'recetas' => ['nullable', 'array', 'max:10'],
            'recetas.*' => ['file', 'max:20480', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }
}

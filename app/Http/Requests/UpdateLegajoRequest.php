<?php

namespace App\Http\Requests;

use App\Models\Problematica;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLegajoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nivel_riesgo' => ['required', 'in:Bajo,Medio,Alto,Critico'],
            'origen_derivacion' => ['required', 'string', 'max:255'],
            'profesional_id' => ['nullable', Rule::exists(User::class, 'id')],
            'problematicas' => ['required', 'array', 'min:1'],
            'problematicas.*' => [Rule::exists(Problematica::class, 'id')],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nivel_riesgo.required' => 'Seleccioná el nivel de riesgo (Bajo, Medio, Alto o Crítico).',
            'nivel_riesgo.in' => 'El nivel de riesgo debe ser Bajo, Medio, Alto o Crítico.',
            'origen_derivacion.required' => 'Indicá la institución o referente que deriva el caso.',
            'origen_derivacion.max' => 'El origen de la derivación no puede superar los 255 caracteres.',
            'profesional_id.exists' => 'El profesional seleccionado no es válido.',
            'problematicas.required' => 'Seleccioná al menos una problemática.',
            'problematicas.min' => 'Seleccioná al menos una problemática.',
            'problematicas.*.exists' => 'Una de las problemáticas seleccionadas no es válida.',
            'observaciones.max' => 'Las observaciones no pueden superar los 2000 caracteres.',
        ];
    }
}

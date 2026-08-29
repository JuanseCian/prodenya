<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLegajoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'persona_id' => ['required', 'exists:personas,id'],
            'motivo_ingreso' => ['required', 'string'],
            'nivel_riesgo' => ['required', 'in:Bajo,Medio,Alto,Critico'],
            'origen_derivacion' => ['required', 'string', 'max:255'],
            'profesional_id' => ['nullable', 'exists:usuarios,id'],
            'tipos_maltrato' => ['required', 'array', 'min:1'],
            'tipos_maltrato.*' => ['exists:tipos_maltrato,id'],
            'observaciones' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'persona_id.required' => 'Elegí una persona para asociar el legajo.',
            'motivo_ingreso.required' => 'Ingresá el motivo de ingreso.',
            'nivel_riesgo.required' => 'Seleccioná el nivel de riesgo.',
            'origen_derivacion.required' => 'Indicá la institución o persona que deriva el caso.',
            'tipos_maltrato.required' => 'Seleccioná al menos un tipo de maltrato.',
            'tipos_maltrato.min' => 'Seleccioná al menos un tipo de maltrato.',
        ];
    }
}

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
            'motivo_ingreso_id' => ['nullable', 'exists:motivos_ingreso,id'],
            'nivel_riesgo' => ['required', 'in:Bajo,Medio,Alto,Critico'],
            'origen_derivacion' => ['required', 'string', 'max:255'],
            'profesional_id' => ['nullable', 'exists:users,id'],
            'problematicas' => ['required', 'array', 'min:1'],
            'problematicas.*' => ['exists:problematicas,id'],
            'observaciones' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'persona_id.required' => 'Elegí una persona para asociar el legajo.',
            'persona_id.exists' => 'La persona seleccionada no es válida.',
            'motivo_ingreso.required' => 'Ingresá el motivo de ingreso.',
            'motivo_ingreso_id.exists' => 'El motivo de ingreso seleccionado no es válido.',
            'nivel_riesgo.required' => 'Seleccioná el nivel de riesgo.',
            'origen_derivacion.required' => 'Indicá la institución o referente que deriva el caso.',
            'problematicas.array' => 'Seleccioná al menos una problemática.',
            'problematicas.required' => 'Seleccioná al menos una problemática.',
            'problematicas.min' => 'Seleccioná al menos una problemática.',
            'problematicas.*.exists' => 'Una de las problemáticas seleccionadas no es válida.',
            'profesional_id.exists' => 'El profesional seleccionado no es válido.',
        ];
    }
}

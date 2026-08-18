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
            'observaciones' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'persona_id.required' => 'Elegí una persona para asociar el legajo.',
            'motivo_ingreso.required' => 'Ingresá el motivo de ingreso.',
        ];
    }
}

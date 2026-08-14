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
            'numero_legajo' => ['required', 'string', 'max:30', 'unique:legajos,numero_legajo'],
            'observaciones' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero_legajo.unique' => 'Ese número de legajo ya está en uso.',
            'persona_id.required' => 'Elegí una persona para asociar el legajo.',
        ];
    }
}

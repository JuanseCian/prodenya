<?php

namespace App\Http\Requests;

use App\Models\Persona;
use App\Models\VinculoFamiliar;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVinculoFamiliarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->tieneRol('admin', 'coordinador', 'profesional', 'administrativo') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'persona_relacionada_id' => [
                'required',
                'integer',
                'different:persona_id',
                Rule::exists(Persona::class, 'id')
                    ->where('activo', true)
                    ->whereNotNull('numero_legajo'),
            ],
            'tipo_vinculo' => ['required', 'string', Rule::in(VinculoFamiliar::TIPOS)],
        ];
    }
}

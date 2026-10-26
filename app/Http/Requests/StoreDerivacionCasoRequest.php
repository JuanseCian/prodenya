<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDerivacionCasoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol(
            'admin',
            'operador',
            'oficios',
            'coordinador',
            'profesional',
            'administrativo',
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'destinatario_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'modo' => ['required', Rule::in(['adicional', 'reemplazo'])],
            'motivo' => ['required', 'string', 'max:120'],
            'detalle' => ['nullable', 'string', 'max:5000'],
            'fecha_limite' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}

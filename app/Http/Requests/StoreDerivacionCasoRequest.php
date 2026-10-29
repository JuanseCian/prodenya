<?php

namespace App\Http\Requests;

use App\Models\CasoResponsable;
use App\Models\DerivacionCaso;
use App\Models\Legajo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $legajo = $this->route('legajo');

            if (! $legajo instanceof Legajo || $this->input('modo') !== DerivacionCaso::MODO_ADICIONAL) {
                return;
            }

            $yaActivo = $legajo->responsablesActivos()
                ->where('usuario_id', $this->input('destinatario_id'))
                ->exists();

            if (! $yaActivo && $legajo->responsablesActivos()->count() >= CasoResponsable::MAX_ACTIVOS) {
                $validator->errors()->add(
                    'destinatario_id',
                    'El legajo ya alcanzó el máximo de '.CasoResponsable::MAX_ACTIVOS.' responsables activos.',
                );
            }
        });
    }
}

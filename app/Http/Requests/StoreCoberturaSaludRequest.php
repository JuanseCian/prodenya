<?php

namespace App\Http\Requests;

use App\Models\CoberturaSalud;
use App\Models\Legajo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCoberturaSaludRequest extends FormRequest
{
    public function authorize(): bool
    {
        $legajo = $this->route('legajo');

        return $legajo instanceof Legajo
            && $this->user()?->puedeVerSeccionLegajo('persona') === true;
    }

    public function rules(): array
    {
        $tipo = (string) $this->input('tipo');
        $requiereEntidad = in_array($tipo, ['obra_social', 'prepaga', 'pami_publica', 'otra'], true);
        $esTitular = $this->boolean('persona_es_titular');

        return [
            'tipo' => ['required', Rule::in(CoberturaSalud::TIPOS)],
            'origen' => ['required', Rule::in(CoberturaSalud::ORIGENES)],
            'programa' => [
                Rule::requiredIf($tipo === 'municipal'),
                'nullable',
                'string',
                'max:190',
            ],
            'entidad' => [
                Rule::requiredIf($requiereEntidad),
                'nullable',
                'string',
                'max:190',
            ],
            'plan' => ['nullable', 'string', 'max:190'],
            'numero_afiliado' => ['nullable', 'string', 'max:100'],
            'persona_es_titular' => ['required', 'boolean'],
            'titular_nombre' => [
                Rule::requiredIf(! $esTitular),
                'nullable',
                'string',
                'max:150',
            ],
            'titular_dni' => ['nullable', 'string', 'max:30'],
            'titular_cuil' => ['nullable', 'string', 'max:20'],
            'parentesco_titular' => [
                Rule::requiredIf(! $esTitular),
                'nullable',
                'string',
                'max:60',
            ],
            'fecha_desde' => ['required', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'observaciones' => ['nullable', 'string', 'max:10000'],
            'credencial' => ['nullable', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png'],
            'constancias' => ['nullable', 'array', 'max:10'],
            'constancias.*' => ['file', 'max:20480', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Auh;
use App\Models\Legajo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAuhArchivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('legajo') instanceof Legajo
            && (bool) $this->user()?->tieneRol(...Auh::ROLES_GESTION)
            && $this->user()->puedeVerSeccionLegajo('auh');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', Rule::in(array_keys(Auh::tipoLabels()))],
            'descripcion' => [
                Rule::requiredIf($this->input('tipo') === Auh::TIPO_OTRA),
                'nullable',
                'string',
                'max:255',
            ],
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:20480'],
            'confirma_firma' => ['exclude_unless:tipo,'.implode(',', array_keys(Auh::PLANILLAS)), 'accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tipo.required' => 'Seleccioná qué documento estás cargando.',
            'tipo.in' => 'El tipo de documento no es válido.',
            'descripcion.required' => 'Indicá qué documentación estás adjuntando.',
            'descripcion.max' => 'La descripción puede tener hasta 255 caracteres.',
            'archivo.required' => 'Seleccioná el archivo.',
            'archivo.mimes' => 'El archivo debe ser PDF, Word, JPG o PNG.',
            'archivo.max' => 'El archivo puede pesar hasta 20 MB.',
            'confirma_firma.accepted' => 'Confirmá que la planilla escaneada está firmada.',
        ];
    }
}

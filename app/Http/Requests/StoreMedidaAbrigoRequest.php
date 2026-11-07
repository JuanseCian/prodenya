<?php

namespace App\Http\Requests;

use App\Models\Legajo;
use App\Models\MedidaAbrigo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedidaAbrigoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $legajo = $this->route('legajo');

        return $this->user()?->tieneRol('admin', 'operador', 'coordinador', 'profesional')
            && $legajo instanceof Legajo
            && $this->user()->puedeVerSeccionLegajo('medidas_abrigo');
    }

    public function rules(): array
    {
        return [
            'ubicacion' => ['required', Rule::in(MedidaAbrigo::UBICACIONES)],
            'duracion_dias' => ['required', 'integer', Rule::in(MedidaAbrigo::DURACIONES)],
            'fecha_inicio' => ['required', 'date', 'after:2000-01-01'],
            'observaciones' => ['nullable', 'string', 'max:10000'],
            'actas' => ['required', 'array', 'min:1', 'max:3'],
            'actas.*.tipo' => ['required', 'string', Rule::in(array_keys(MedidaAbrigo::ACTA_LABELS)), 'distinct'],
            'actas.*.archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ubicacion.required' => 'Seleccioná dónde se realizará la medida.',
            'ubicacion.in' => 'La ubicación seleccionada no es válida.',
            'duracion_dias.required' => 'Seleccioná la duración de la medida.',
            'duracion_dias.in' => 'La medida debe durar 90 o 180 días.',
            'fecha_inicio.required' => 'Ingresá la fecha de inicio.',
            'fecha_inicio.date' => 'La fecha de inicio no es válida.',
            'actas.required' => 'Cargá al menos un acta.',
            'actas.min' => 'Cargá al menos un acta.',
            'actas.max' => 'Podés cargar hasta tres actas.',
            'actas.*.tipo.required' => 'Seleccioná el tipo de cada acta.',
            'actas.*.tipo.in' => 'El tipo de acta seleccionado no es válido.',
            'actas.*.tipo.distinct' => 'No podés cargar dos veces el mismo tipo de acta.',
            'actas.*.archivo.required' => 'Seleccioná el archivo del acta.',
            'actas.*.archivo.mimes' => 'Las actas deben ser PDF, JPG o PNG.',
            'actas.*.archivo.max' => 'Cada acta puede pesar hasta 20 MB.',
        ];
    }
}

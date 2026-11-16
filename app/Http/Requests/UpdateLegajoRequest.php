<?php

namespace App\Http\Requests;

use App\Models\Legajo;
use App\Models\Problematica;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLegajoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $legajo = $this->route('legajo');

        return $this->user()?->tieneRol('admin', 'administrativo')
            && $legajo instanceof Legajo
            && $this->user()->puedeVerSeccionLegajo('seguimiento')
            && $this->user()->puedeVerSeccionLegajo('situacion');
    }

    public function rules(): array
    {
        return [
            'nivel_riesgo' => ['required', 'in:Bajo,Medio,Alto,Critico'],
            'origen_derivacion' => ['required', 'string', 'max:255'],
            'profesional_id' => ['prohibited'],
            'problematicas' => ['required', 'array', 'min:1'],
            'problematicas.*' => [Rule::exists(Problematica::class, 'id')],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nivel_riesgo.required' => 'Seleccioná el nivel de riesgo (Bajo, Medio, Alto o Crítico).',
            'nivel_riesgo.in' => 'El nivel de riesgo debe ser Bajo, Medio, Alto o Crítico.',
            'origen_derivacion.required' => 'Indicá la institución o referente que deriva el caso.',
            'origen_derivacion.max' => 'El origen de la derivación no puede superar los 255 caracteres.',
            'profesional_id.prohibited' => 'El responsable sólo puede modificarse mediante una derivación.',
            'problematicas.required' => 'Seleccioná al menos una problemática.',
            'problematicas.min' => 'Seleccioná al menos una problemática.',
            'problematicas.*.exists' => 'Una de las problemáticas seleccionadas no es válida.',
            'observaciones.max' => 'Las observaciones no pueden superar los 2000 caracteres.',
        ];
    }
}

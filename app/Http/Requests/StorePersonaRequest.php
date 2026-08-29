<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'dni' => ['required', 'string', 'max:15', 'unique:personas,dni'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'genero' => ['required', 'in:M,F,X,NO_ESPECIFICA'],
            'direccion_calle' => ['nullable', 'string', 'max:150'],
            'direccion_numero' => ['nullable', 'string', 'max:20'],
            'direccion_piso_depto' => ['nullable', 'string', 'max:20'],
            'provincia_id' => ['required', 'exists:provincias,id'],
            'localidad_id' => ['required', 'exists:localidades,id'],
            'barrio_id' => ['nullable', 'exists:barrios,id'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'observaciones' => ['nullable', 'string'],

            // Legajo opcional (el número se genera automáticamente en el controller)
            'abrir_legajo' => ['boolean'],
            'legajo_motivo_ingreso' => ['required_if:abrir_legajo,true', 'nullable', 'string'],
            'legajo_nivel_riesgo' => ['required_if:abrir_legajo,true', 'nullable', 'in:Bajo,Medio,Alto,Critico'],
            'legajo_origen_derivacion' => ['required_if:abrir_legajo,true', 'nullable', 'string', 'max:255'],
            'legajo_profesional_id' => ['nullable', 'exists:usuarios,id'],
            'legajo_tipos_maltrato' => ['required_if:abrir_legajo,true', 'nullable', 'array', 'min:1'],
            'legajo_tipos_maltrato.*' => ['exists:tipos_maltrato,id'],
            'legajo_observaciones' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'dni.unique' => 'Ya existe una persona registrada con ese DNI.',
            'legajo_motivo_ingreso.required_if' => 'Ingresá el motivo de ingreso.',
            'legajo_nivel_riesgo.required_if' => 'Seleccioná el nivel de riesgo.',
            'legajo_origen_derivacion.required_if' => 'Indicá la institución o persona que deriva el caso.',
            'legajo_tipos_maltrato.required_if' => 'Seleccioná al menos un tipo de maltrato.',
        ];
    }
}

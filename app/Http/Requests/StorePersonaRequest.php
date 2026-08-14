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

            // Legajo opcional
            'abrir_legajo' => ['boolean'],
            'numero_legajo' => ['required_if:abrir_legajo,true', 'nullable', 'string', 'max:30', 'unique:legajos,numero_legajo'],
            'legajo_observaciones' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'dni.unique' => 'Ya existe una persona registrada con ese DNI.',
            'numero_legajo.required_if' => 'Ingresá un número de legajo o destildá la opción de abrir legajo.',
            'numero_legajo.unique' => 'Ese número de legajo ya está en uso.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Barrio;
use App\Models\Localidad;
use App\Models\Persona;
use App\Models\Provincia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('dni')) {
            $this->merge(['dni' => preg_replace('/\D/', '', (string) $this->input('dni'))]);
        }
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'dni' => [
                'required',
                'regex:/^\d{7,8}$/',
                Rule::unique(Persona::class, 'dni')->ignore($this->route('persona')),
            ],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:today', 'after:1900-01-01'],
            'genero' => ['required', 'in:M,F,X,NO_ESPECIFICA'],
            'telefono' => ['nullable', 'regex:/^[0-9+\-\s()]{6,30}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion_calle' => ['nullable', 'string', 'max:150'],
            'direccion_numero' => ['nullable', 'string', 'max:20'],
            'direccion_piso_depto' => ['nullable', 'string', 'max:20'],
            'provincia_id' => ['required', Rule::exists(Provincia::class, 'id')],
            'localidad_id' => ['required', Rule::exists(Localidad::class, 'id')],
            'barrio_id' => ['nullable', Rule::exists(Barrio::class, 'id')],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Ingresá el nombre.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'apellido.required' => 'Ingresá el apellido.',
            'apellido.max' => 'El apellido no puede superar los 100 caracteres.',
            'dni.required' => 'Ingresá el DNI.',
            'dni.regex' => 'El DNI debe tener 7 u 8 dígitos, solo números (sin puntos ni espacios).',
            'dni.unique' => 'Ya existe otra persona registrada con ese DNI.',
            'fecha_nacimiento.required' => 'Ingresá la fecha de nacimiento.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento no es una fecha válida.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser posterior a hoy.',
            'fecha_nacimiento.after' => 'La fecha de nacimiento no puede ser anterior al año 1900.',
            'genero.in' => 'Elegí una de las opciones de género disponibles.',
            'telefono.regex' => 'El teléfono debe tener entre 6 y 30 caracteres y solo puede contener números, espacios y los signos + - ( ).',
            'email.email' => 'Ingresá un correo electrónico válido (ej.: nombre@dominio.com).',
            'email.max' => 'El correo electrónico no puede superar los 150 caracteres.',
            'direccion_calle.max' => 'La calle no puede superar los 150 caracteres.',
            'direccion_numero.max' => 'El número no puede superar los 20 caracteres.',
            'direccion_piso_depto.max' => 'Piso / Depto no puede superar los 20 caracteres.',
            'provincia_id.required' => 'Seleccioná una provincia.',
            'provincia_id.exists' => 'La provincia seleccionada no es válida.',
            'localidad_id.required' => 'Seleccioná una localidad (primero elegí la provincia).',
            'localidad_id.exists' => 'La localidad seleccionada no es válida.',
            'barrio_id.exists' => 'El barrio seleccionado no es válido.',
            'observaciones.max' => 'Las observaciones no pueden superar los 2000 caracteres.',
        ];
    }
}

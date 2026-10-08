<?php

namespace App\Http\Requests;

use App\Models\Barrio;
use App\Models\Localidad;
use App\Models\Persona;
use App\Models\Problematica;
use App\Models\Provincia;
use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // El DNI se guarda solo con dígitos (acepta que lo escriban con puntos).
        if ($this->has('dni')) {
            $this->merge(['dni' => preg_replace('/\D/', '', (string) $this->input('dni'))]);
        }
    }

    public function rules(): array
    {
        $conLegajo = $this->boolean('abrir_legajo');

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'dni' => ['required', 'regex:/^\d{7,8}$/', Rule::unique(Persona::class, 'dni')],
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

            'abrir_legajo' => ['boolean'],
            'legajo_motivo_ingreso' => $conLegajo ? ['required', 'string', 'max:2000'] : ['nullable'],
            'legajo_problematicas' => $conLegajo ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'legajo_problematicas.*' => [Rule::exists(Problematica::class, 'id')],
            'legajo_nivel_riesgo' => $conLegajo ? ['required', 'in:Bajo,Medio,Alto,Critico'] : ['nullable'],
            'legajo_origen_derivacion' => $conLegajo ? ['required', 'string', 'max:255'] : ['nullable'],
            'legajo_profesional_id' => ['nullable', Rule::exists(Usuario::class, 'id')],
            'legajo_observaciones' => ['nullable', 'string', 'max:2000'],
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
            'dni.unique' => 'Ya existe una persona registrada con ese DNI.',

            'fecha_nacimiento.required' => 'Ingresá la fecha de nacimiento.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento no es una fecha válida.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser posterior a hoy.',
            'fecha_nacimiento.after' => 'La fecha de nacimiento no puede ser anterior al año 1900.',

            'genero.required' => 'Seleccioná una opción de género.',
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

            'legajo_motivo_ingreso.required' => 'Ingresá el motivo de ingreso para abrir el legajo.',
            'legajo_motivo_ingreso.max' => 'El motivo de ingreso no puede superar los 2000 caracteres.',
            'legajo_problematicas.required' => 'Seleccioná al menos una problemática para abrir el legajo.',
            'legajo_problematicas.min' => 'Seleccioná al menos una problemática para abrir el legajo.',
            'legajo_problematicas.*.exists' => 'Una de las problemáticas seleccionadas no es válida.',
            'legajo_nivel_riesgo.required' => 'Seleccioná el nivel de riesgo (Bajo, Medio, Alto o Crítico).',
            'legajo_nivel_riesgo.in' => 'El nivel de riesgo debe ser Bajo, Medio, Alto o Crítico.',
            'legajo_origen_derivacion.required' => 'Indicá la institución o referente que deriva el caso.',
            'legajo_origen_derivacion.max' => 'El origen de la derivación no puede superar los 255 caracteres.',
            'legajo_profesional_id.exists' => 'El profesional seleccionado no es válido.',
            'legajo_observaciones.max' => 'Las observaciones del legajo no pueden superar los 2000 caracteres.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Auh;
use App\Models\Legajo;
use Illuminate\Foundation\Http\FormRequest;

class GenerarAuhPlanillaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('legajo') instanceof Legajo
            && array_key_exists((string) $this->route('tipo'), Auh::PLANILLAS)
            && (bool) $this->user()?->tieneRol(...Auh::ROLES_GESTION)
            && $this->user()->puedeVerSeccionLegajo('auh');
    }

    /**
     * Todos los campos son opcionales: lo que falte se completa a mano en la planilla impresa.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $texto = ['nullable', 'string', 'max:255'];

        return [
            'fecha' => ['nullable', 'date'],
            'ciudad' => $texto,
            'provincia' => $texto,
            'nino_nombre' => $texto,
            'nino_dni' => ['nullable', 'string', 'max:20'],
            'responsable' => ['nullable', 'array'],
            'responsable.nombre' => $texto,
            'responsable.dni' => ['nullable', 'string', 'max:20'],
            'responsable.cuil' => ['nullable', 'string', 'max:20'],
            'responsable.fecha_nacimiento' => ['nullable', 'date'],
            'responsable.nacionalidad' => $texto,
            'responsable.domicilio' => $texto,
            'responsable.barrio' => $texto,
            'responsable.localidad' => $texto,
            'responsable.provincia' => $texto,
            'responsable.telefono' => ['nullable', 'string', 'max:50'],
            'no_conviviente_nombre' => $texto,
            'no_conviviente_dni' => ['nullable', 'string', 'max:20'],
            'no_conviviente_desde' => ['nullable', 'integer', 'between:1900,'.now()->year],
            'referencia_detalle' => ['nullable', 'string', 'max:1000'],
            'grupo_familiar' => ['nullable', 'array', 'max:20'],
            'grupo_familiar.*.nombre' => $texto,
            'grupo_familiar.*.vinculo' => ['nullable', 'string', 'max:100'],
            'grupo_familiar.*.fecha_relacion' => ['nullable', 'date'],
            'grupo_familiar.*.dni' => ['nullable', 'string', 'max:20'],
            'grupo_familiar.*.fecha_nacimiento' => ['nullable', 'date'],
            'motivos' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fecha.date' => 'La fecha no es válida.',
            'responsable.fecha_nacimiento.date' => 'La fecha de nacimiento del responsable no es válida.',
            'no_conviviente_desde.integer' => 'Ingresá un año válido (por ejemplo, 2022).',
            'no_conviviente_desde.between' => 'Ingresá un año válido (por ejemplo, 2022).',
            'grupo_familiar.max' => 'Podés cargar hasta 20 integrantes del grupo familiar.',
            'grupo_familiar.*.fecha_relacion.date' => 'Revisá las fechas de relación del grupo familiar.',
            'grupo_familiar.*.fecha_nacimiento.date' => 'Revisá las fechas de nacimiento del grupo familiar.',
            'motivos.max' => 'Los motivos pueden tener hasta 5000 caracteres.',
        ];
    }
}

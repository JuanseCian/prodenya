<?php

namespace App\Http\Requests;

use App\Models\Persona;
use App\Models\VinculoFamiliar;
use App\Support\Barrios;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Vincula al legajo una persona del sistema (con o sin legajo) o registra un
 * familiar que todavía no está en el sistema.
 */
class StoreVinculoFamiliarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol('admin', 'coordinador', 'profesional', 'administrativo') ?? false;
    }

    /**
     * Registra un familiar nuevo cuando no se eligió a nadie del sistema.
     */
    public function registraFamiliarNuevo(): bool
    {
        return blank($this->input('persona_relacionada_id'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $reglas = [
            'tipo_vinculo' => ['required', 'string', Rule::in(VinculoFamiliar::TIPOS)],
        ];

        if (! $this->registraFamiliarNuevo()) {
            return $reglas + [
                'persona_relacionada_id' => [
                    'required',
                    'integer',
                    Rule::exists(Persona::class, 'id')->where('activo', true),
                ],
            ];
        }

        return $reglas + self::reglasFamiliar('familiar.');
    }

    /**
     * Datos de un familiar registrado sin legajo. También se usan al editarlo.
     *
     * @return array<string, array<mixed>>
     */
    public static function reglasFamiliar(string $prefijo = '', ?int $ignorarPersonaId = null): array
    {
        return [
            "{$prefijo}apellido" => ['required', 'string', 'max:100'],
            "{$prefijo}nombre" => ['required', 'string', 'max:100'],
            "{$prefijo}dni" => ['nullable', 'regex:/^\d{7,8}$/', Rule::unique(Persona::class, 'dni')->ignore($ignorarPersonaId)],
            "{$prefijo}fecha_nacimiento" => ['nullable', 'date', 'before:today'],
            "{$prefijo}genero" => ['nullable', Rule::in(['M', 'F', 'X', 'NO_ESPECIFICA'])],
            "{$prefijo}telefono" => ['nullable', 'string', 'max:50'],
            "{$prefijo}direccion_calle" => ['nullable', 'string', 'max:150'],
            "{$prefijo}direccion_numero" => ['nullable', 'string', 'max:20'],
            "{$prefijo}barrio" => ['nullable', 'string', Barrios::regla()],
            "{$prefijo}observaciones" => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function mensajesFamiliar(string $prefijo = ''): array
    {
        return [
            "{$prefijo}apellido.required" => 'Ingresá el apellido del familiar.',
            "{$prefijo}nombre.required" => 'Ingresá el nombre del familiar.',
            "{$prefijo}dni.regex" => 'El DNI debe tener 7 u 8 números, sin puntos.',
            "{$prefijo}dni.unique" => 'Ya hay una persona registrada con ese DNI: usala desde «Ya está en el sistema».',
            "{$prefijo}fecha_nacimiento.date" => 'La fecha de nacimiento no es válida.',
            "{$prefijo}fecha_nacimiento.before" => 'La fecha de nacimiento tiene que ser anterior a hoy.',
            "{$prefijo}barrio.in" => 'Elegí un barrio de la lista.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tipo_vinculo.required' => 'Elegí el tipo de vínculo.',
            'tipo_vinculo.in' => 'El tipo de vínculo no es válido.',
            'persona_relacionada_id.exists' => 'La persona seleccionada no es válida.',
        ] + self::mensajesFamiliar('familiar.');
    }
}

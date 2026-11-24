<?php

namespace App\Http\Requests;

use App\Models\Acta;
use App\Support\ValidacionActa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreActaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return array_key_exists($this->tipo(), Acta::TIPOS)
            && (bool) $this->user()?->tieneRol(...Acta::ROLES);
    }

    protected function prepareForValidation(): void
    {
        if (array_key_exists($this->tipo(), Acta::TIPOS)) {
            $this->replace(ValidacionActa::prepararFirmas($this->tipo(), $this->all()));
        }
    }

    /**
     * Todos los campos son opcionales: lo que falte se completa a mano en el acta impresa.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'legajo_id' => ['nullable', 'integer', 'exists:legajos,id'],
            'persona_id' => ['nullable', 'integer', 'exists:personas,id'],
        ] + ValidacionActa::reglas($this->tipo());
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['legajo_id.exists' => 'El legajo elegido no existe.'] + ValidacionActa::mensajes($this->tipo());
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [fn (Validator $validator) => ValidacionActa::validarFirmas($validator, $this->tipo(), $this->all())];
    }

    public function tipo(): string
    {
        return (string) $this->route('tipo');
    }

    /**
     * @return array<string, mixed>
     */
    public function datos(): array
    {
        return ValidacionActa::datos($this->validated());
    }

    /**
     * @return array<string, array{nombre: string, png: string}>
     */
    public function firmas(): array
    {
        return ValidacionActa::firmas($this->tipo(), $this->all());
    }
}

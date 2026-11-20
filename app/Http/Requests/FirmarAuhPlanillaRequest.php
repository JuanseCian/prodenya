<?php

namespace App\Http\Requests;

use App\Models\Auh;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class FirmarAuhPlanillaRequest extends GenerarAuhPlanillaRequest
{
    private const PREFIJO_PNG = 'data:image/png;base64,';

    /**
     * La aclaración del responsable o del solicitante a cobrar es su propio
     * nombre en la planilla: se toma de ahí para que no se cargue dos veces.
     */
    protected function prepareForValidation(): void
    {
        $firmas = $this->input('firmas', []);

        if (! is_array($firmas)) {
            return;
        }

        foreach (Auh::FIRMANTES_RESPONSABLE as $rol) {
            if (array_key_exists($rol, $this->firmantes()) && is_array($firmas[$rol] ?? null)) {
                $firmas[$rol]['nombre'] = $this->input('responsable.nombre');
            }
        }

        $this->merge(['firmas' => $firmas]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $reglas = parent::rules();

        foreach (array_keys($this->firmantes()) as $rol) {
            $reglas["firmas.{$rol}.dibujo"] = ['required', 'string', 'starts_with:'.self::PREFIJO_PNG, 'max:2000000'];
            $reglas["firmas.{$rol}.nombre"] = ['required', 'string', 'max:150'];
        }

        return $reglas;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $mensajes = parent::messages();

        foreach ($this->firmantes() as $rol => $label) {
            $mensajes["firmas.{$rol}.dibujo.required"] = "Falta la firma de: {$label}.";
            $mensajes["firmas.{$rol}.dibujo.starts_with"] = "La firma de {$label} no es válida.";
            $mensajes["firmas.{$rol}.dibujo.max"] = "La firma de {$label} es demasiado grande.";
            $mensajes["firmas.{$rol}.nombre.required"] = "Ingresá la aclaración de: {$label}.";
        }

        return $mensajes;
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach ($this->firmantes() as $rol => $label) {
                    $dibujo = $this->input("firmas.{$rol}.dibujo");

                    if (! is_string($dibujo) || $validator->errors()->has("firmas.{$rol}.dibujo")) {
                        continue;
                    }

                    if ($this->imagenPng($dibujo) === null) {
                        $validator->errors()->add("firmas.{$rol}.dibujo", "La firma de {$label} no es válida.");
                    }
                }
            },
        ];
    }

    /**
     * Firmas validadas, decodificadas a PNG binario.
     *
     * @return array<string, array{nombre: string, png: string}>
     */
    public function firmas(): array
    {
        return collect($this->firmantes())
            ->mapWithKeys(fn (string $label, string $rol) => [$rol => [
                'nombre' => (string) $this->input("firmas.{$rol}.nombre"),
                'png' => (string) $this->imagenPng((string) $this->input("firmas.{$rol}.dibujo")),
            ]])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function firmantes(): array
    {
        return Auh::FIRMANTES[(string) $this->route('tipo')] ?? [];
    }

    private function imagenPng(string $dibujo): ?string
    {
        $contenido = base64_decode(Str::after($dibujo, self::PREFIJO_PNG), true);

        if ($contenido === false || ! str_starts_with($contenido, "\x89PNG\r\n\x1a\n")) {
            return null;
        }

        $dimensiones = @getimagesizefromstring($contenido);

        return $dimensiones !== false && $dimensiones[2] === IMAGETYPE_PNG ? $contenido : null;
    }
}

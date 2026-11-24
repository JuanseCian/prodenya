<?php

namespace App\Support;

use App\Http\Requests\GenerarAuhPlanillaRequest;
use App\Models\Acta;
use App\Models\Auh;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;

/**
 * Reglas y firmas de un acta, compartidas por Acta rápida y por las
 * intervenciones que generan un acta. El prefijo permite validarlas
 * anidadas (por ejemplo «acta.relato»).
 */
class ValidacionActa
{
    /**
     * @return array<string, mixed>
     */
    public static function reglas(string $tipo, string $prefijo = ''): array
    {
        $texto = ['nullable', 'string', 'max:255'];

        $reglas = Acta::esAuh($tipo)
            ? (new GenerarAuhPlanillaRequest)->rules()
            : [
                'fecha' => ['nullable', 'date'],
                'hora' => ['nullable', 'date_format:H:i'],
                'operador' => $texto,
                'compareciente_nombre' => $texto,
                'compareciente_dni' => ['nullable', 'string', 'max:20'],
                'compareciente_domicilio' => $texto,
                'compareciente_barrio' => ['nullable', 'string', Barrios::regla()],
                'compareciente_telefono' => ['nullable', 'string', 'max:50'],
                'vinculo' => ['nullable', 'string', 'max:100'],
                'nino_nombre' => $texto,
                'nino_dni' => ['nullable', 'string', 'max:20'],
                'nino_fecha_nacimiento' => ['nullable', 'date'],
                'nino_escuela' => ['nullable', 'string', Escuelas::regla()],
                'relato' => ['nullable', 'string', 'max:10000'],
                'estrategias' => ['nullable', 'string', 'max:10000'],
                'citacion_dia' => ['nullable', 'date'],
                'citacion_horario' => ['nullable', 'string', 'max:100'],
            ];

        foreach (array_keys(Acta::firmantesDe($tipo)) as $rol) {
            $reglas["firmas.{$rol}.dibujo"] = ['nullable', 'string', 'starts_with:'.FirmaPng::PREFIJO, 'max:2000000'];
            $reglas["firmas.{$rol}.nombre"] = ['nullable', 'string', 'max:150'];
        }

        return self::conPrefijo($reglas, $prefijo);
    }

    /**
     * @return array<string, string>
     */
    public static function mensajes(string $tipo, string $prefijo = ''): array
    {
        $mensajes = [
            'fecha.date' => 'La fecha no es válida.',
            'hora.date_format' => 'La hora no es válida.',
            'nino_fecha_nacimiento.date' => 'La fecha de nacimiento no es válida.',
            'citacion_dia.date' => 'El día de la citación no es válido.',
            'compareciente_barrio.in' => 'Elegí un barrio de la lista.',
            'nino_escuela.in' => 'Elegí una escuela de la lista.',
        ];

        if (Acta::esAuh($tipo)) {
            $mensajes += (new GenerarAuhPlanillaRequest)->messages();
        }

        foreach (Acta::firmantesDe($tipo) as $rol => $label) {
            $mensajes["firmas.{$rol}.dibujo.starts_with"] = "La firma de {$label} no es válida.";
            $mensajes["firmas.{$rol}.dibujo.max"] = "La firma de {$label} es demasiado grande.";
        }

        return self::conPrefijo($mensajes, $prefijo);
    }

    /**
     * La aclaración del responsable o solicitante de AUH es su nombre en la planilla.
     *
     * @param  array<string, mixed>  $acta
     * @return array<string, mixed>
     */
    public static function prepararFirmas(string $tipo, array $acta): array
    {
        if (! Acta::esAuh($tipo) || ! is_array($acta['firmas'] ?? null)) {
            return $acta;
        }

        foreach (Auh::FIRMANTES_RESPONSABLE as $rol) {
            if (is_array($acta['firmas'][$rol] ?? null)) {
                $acta['firmas'][$rol]['nombre'] = $acta['responsable']['nombre'] ?? null;
            }
        }

        return $acta;
    }

    /**
     * Una firma dibujada tiene que ser un PNG real y llevar aclaración.
     *
     * @param  array<string, mixed>  $acta
     */
    public static function validarFirmas(Validator $validator, string $tipo, array $acta, string $prefijo = ''): void
    {
        foreach (Acta::firmantesDe($tipo) as $rol => $label) {
            $dibujo = $acta['firmas'][$rol]['dibujo'] ?? null;
            $campo = "{$prefijo}firmas.{$rol}";

            if (blank($dibujo) || $validator->errors()->has("{$campo}.dibujo")) {
                continue;
            }

            if (! is_string($dibujo) || FirmaPng::decodificar($dibujo) === null) {
                $validator->errors()->add("{$campo}.dibujo", "La firma de {$label} no es válida.");
            }

            if (blank($acta['firmas'][$rol]['nombre'] ?? null)) {
                $validator->errors()->add("{$campo}.nombre", "Ingresá la aclaración de: {$label}.");
            }
        }
    }

    /**
     * Datos del acta validados, sin firmas ni vínculos.
     *
     * @param  array<string, mixed>  $validado
     * @return array<string, mixed>
     */
    public static function datos(array $validado): array
    {
        return Arr::except($validado, ['firmas', 'legajo_id', 'persona_id']);
    }

    /**
     * Firmas dibujadas, decodificadas a PNG.
     *
     * @param  array<string, mixed>  $acta
     * @return array<string, array{nombre: string, png: string}>
     */
    public static function firmas(string $tipo, array $acta): array
    {
        return collect(Acta::firmantesDe($tipo))
            ->keys()
            ->filter(fn (string $rol) => filled($acta['firmas'][$rol]['dibujo'] ?? null))
            ->mapWithKeys(fn (string $rol) => [$rol => [
                'nombre' => (string) ($acta['firmas'][$rol]['nombre'] ?? ''),
                'png' => (string) FirmaPng::decodificar((string) $acta['firmas'][$rol]['dibujo']),
            ]])
            ->all();
    }

    /**
     * @template T
     *
     * @param  array<string, T>  $valores
     * @return array<string, T>
     */
    private static function conPrefijo(array $valores, string $prefijo): array
    {
        if ($prefijo === '') {
            return $valores;
        }

        return collect($valores)->mapWithKeys(fn ($valor, string $clave) => [$prefijo.$clave => $valor])->all();
    }
}

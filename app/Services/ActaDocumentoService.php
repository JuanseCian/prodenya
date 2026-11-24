<?php

namespace App\Services;

use App\Models\Acta;
use App\Support\PlantillaWord;
use Carbon\CarbonImmutable;

/**
 * Genera el Word de un acta rápida a partir de su modelo (resources/plantillas/actas).
 * Los campos vacíos quedan punteados para completarse a mano.
 */
class ActaDocumentoService
{
    private const PUNTEADO = '……………………………';

    /** @var array<int, string> */
    private const MESES = [
        1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
    ];

    /**
     * Bloques de líneas en blanco del modelo que se quitan cuando el texto se escribió.
     *
     * @var array<string, string>
     */
    private const ESPACIOS = ['espacio_relato' => 'relato', 'espacio_estrategias' => 'estrategias'];

    public function __construct(private AuhPlanillaService $planillasAuh) {}

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<string, array{nombre: string, png: string}>  $firmas
     */
    public function generar(string $tipo, array $datos, array $firmas = [], ?string $legajoNumero = null): string
    {
        $config = Acta::TIPOS[$tipo];

        if (isset($config['auh'])) {
            return $this->planillasAuh->generar($config['auh'], $datos, $firmas);
        }

        $plantilla = new PlantillaWord(resource_path("plantillas/actas/{$config['plantilla']}.docx"));
        $temporales = [];

        try {
            foreach (self::ESPACIOS as $bloque => $campo) {
                $plantilla->resolverBloque($bloque, conservar: blank($datos[$campo] ?? null));
            }

            foreach ($this->valores($datos, $legajoNumero) as $macro => $valor) {
                $plantilla->setValue($macro, $this->escapar($valor, in_array($macro, ['relato', 'estrategias'], true) ? '' : self::PUNTEADO));
            }

            foreach (array_keys($config['firmantes'] ?? []) as $rol) {
                $firma = $firmas[$rol] ?? null;
                $plantilla->setValue("aclaracion_{$rol}", $this->escapar($firma['nombre'] ?? null));

                if (! $firma) {
                    $plantilla->setValue("firma_{$rol}", self::PUNTEADO);

                    continue;
                }

                $imagen = tempnam(sys_get_temp_dir(), 'acta-firma-');
                file_put_contents($imagen, $firma['png']);
                $temporales[] = $imagen;
                $plantilla->setImageValue("firma_{$rol}", ['path' => $imagen, 'width' => 170, 'height' => 60, 'ratio' => true]);
            }

            $ruta = tempnam(sys_get_temp_dir(), 'acta-');
            $plantilla->saveAs($ruta);
        } finally {
            foreach ($temporales as $temporal) {
                @unlink($temporal);
            }
        }

        return $ruta;
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, string|null>
     */
    private function valores(array $datos, ?string $legajoNumero): array
    {
        $fecha = filled($datos['fecha'] ?? null) ? CarbonImmutable::parse($datos['fecha']) : null;
        $citacion = filled($datos['citacion_dia'] ?? null) ? CarbonImmutable::parse($datos['citacion_dia']) : null;
        $nacimiento = filled($datos['nino_fecha_nacimiento'] ?? null) ? CarbonImmutable::parse($datos['nino_fecha_nacimiento']) : null;

        return [
            'legajo_numero' => $legajoNumero,
            'fecha' => $fecha?->format('d/m/Y'),
            'fecha_larga' => $fecha ? "{$fecha->day} de ".self::MESES[$fecha->month]." de {$fecha->year}" : null,
            'dia' => $fecha ? (string) $fecha->day : null,
            'mes' => $fecha ? self::MESES[$fecha->month] : null,
            'anio' => $fecha ? (string) $fecha->year : null,
            'hora' => $datos['hora'] ?? null,
            'operador' => $datos['operador'] ?? null,
            'compareciente_nombre' => $datos['compareciente_nombre'] ?? null,
            'compareciente_dni' => $datos['compareciente_dni'] ?? null,
            'compareciente_domicilio' => $datos['compareciente_domicilio'] ?? null,
            'compareciente_barrio' => $datos['compareciente_barrio'] ?? null,
            'compareciente_telefono' => $datos['compareciente_telefono'] ?? null,
            'vinculo' => $datos['vinculo'] ?? null,
            'nino_nombre' => $datos['nino_nombre'] ?? null,
            'nino_dni' => $datos['nino_dni'] ?? null,
            'nino_fecha_nacimiento' => $nacimiento?->format('d/m/Y'),
            'nino_escuela' => $datos['nino_escuela'] ?? null,
            'relato' => $datos['relato'] ?? null,
            'estrategias' => $datos['estrategias'] ?? null,
            'citacion_dia' => $citacion ? $citacion->locale('es')->isoFormat('dddd D [de] MMMM') : null,
            'citacion_horario' => $datos['citacion_horario'] ?? null,
        ];
    }

    /**
     * PHPWord no escapa los valores por defecto; los saltos de línea los convierte él.
     */
    private function escapar(?string $valor, string $siVacio = self::PUNTEADO): string
    {
        $valor = trim((string) $valor);

        return htmlspecialchars($valor === '' ? $siVacio : $valor, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}

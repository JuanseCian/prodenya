<?php

namespace App\Services;

use App\Models\Auh;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\VinculoFamiliar;
use App\Support\Barrios;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use PhpOffice\PhpWord\TemplateProcessor;

/**
 * Completa las planillas modelo de AUH (resources/plantillas/auh) con los
 * datos del legajo. Los campos vacíos quedan punteados para completarse a mano.
 */
class AuhPlanillaService
{
    private const PUNTEADO = '……………………………';

    /** @var array<int, string> */
    private const MESES = [
        1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
    ];

    /**
     * Datos con los que se abre el formulario: lo último guardado o, si no
     * hay nada, lo que se puede deducir del legajo.
     *
     * @return array{datos: array<string, mixed>, personas: array<int, array<string, string>>, barrios: array<int, string>}
     */
    public function datosIniciales(?Legajo $legajo, ?Auh $auh): array
    {
        $legajo?->loadMissing('persona.localidad.provincia', 'persona.barrio');
        $nino = $legajo?->persona;
        $personas = $this->personasVinculadas($nino);

        $sugeridos = [
            'fecha' => today()->toDateString(),
            'ciudad' => $nino?->localidad?->nombre ?? '',
            'provincia' => $nino?->localidad?->provincia?->nombre ?? '',
            'nino_nombre' => $nino ? $this->nombreCompleto($nino) : '',
            'nino_dni' => (string) ($nino?->dni ?? ''),
            'responsable' => $this->responsableVacio(),
            'no_conviviente_nombre' => '',
            'no_conviviente_dni' => '',
            'no_conviviente_desde' => '',
            'referencia_detalle' => '',
            'grupo_familiar' => $personas->map(fn (array $persona) => [
                'nombre' => $persona['nombre'],
                'vinculo' => $persona['vinculo'],
                'fecha_relacion' => '',
                'dni' => $persona['dni'],
                'fecha_nacimiento' => $persona['fecha_nacimiento'],
            ])->values()->all(),
            'motivos' => '',
        ];

        $guardados = $this->nulosAVacios($auh?->datos_planillas ?? []);
        $datos = array_replace($sugeridos, $guardados, ['fecha' => $sugeridos['fecha']]);
        $datos['responsable'] = array_replace($this->responsableVacio(), $datos['responsable']);
        $datos['grupo_familiar'] = array_map(
            fn (array $miembro) => array_replace(
                array_fill_keys(['nombre', 'vinculo', 'fecha_relacion', 'dni', 'fecha_nacimiento'], ''),
                $miembro,
            ),
            array_values($datos['grupo_familiar']),
        );

        return [
            'datos' => $datos,
            'personas' => $personas->values()->all(),
            'barrios' => Barrios::nombres(),
        ];
    }

    /**
     * Genera la planilla en un archivo temporal y devuelve su ruta. Sin firmas,
     * los espacios de firma quedan en blanco para firmar en papel.
     *
     * @param  array<string, mixed>  $datos
     * @param  array<string, array{nombre: string, png: string}>  $firmas
     */
    public function generar(string $tipo, array $datos, array $firmas = []): string
    {
        $plantilla = new TemplateProcessor(resource_path("plantillas/auh/{$tipo}.docx"));
        $temporales = [];

        try {
            if ($tipo === 'informe_relacion') {
                $this->completarGrupoFamiliar($plantilla, $datos['grupo_familiar'] ?? []);
            }

            foreach ($this->valores($datos) as $macro => $valor) {
                $plantilla->setValue($macro, $this->escapar($valor));
            }

            foreach (array_keys(Auh::FIRMANTES[$tipo] ?? []) as $rol) {
                $firma = $firmas[$rol] ?? null;
                $plantilla->setValue("aclaracion_{$rol}", $this->escapar($firma['nombre'] ?? null, ''));

                if (! $firma) {
                    $plantilla->setValue("firma_{$rol}", '');

                    continue;
                }

                $imagen = tempnam(sys_get_temp_dir(), 'auh-firma-');
                file_put_contents($imagen, $firma['png']);
                $temporales[] = $imagen;
                $plantilla->setImageValue("firma_{$rol}", ['path' => $imagen, 'width' => 170, 'height' => 60, 'ratio' => true]);
            }

            $ruta = tempnam(sys_get_temp_dir(), 'auh-planilla-');
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
     * @return array<string, string>
     */
    private function valores(array $datos): array
    {
        $fecha = filled($datos['fecha'] ?? null) ? CarbonImmutable::parse($datos['fecha']) : null;
        $responsable = array_replace($this->responsableVacio(), $datos['responsable'] ?? []);

        return [
            'fecha_larga' => $fecha ? "{$fecha->day} de ".self::MESES[$fecha->month]." de {$fecha->year}" : self::PUNTEADO,
            'dia' => $fecha ? (string) $fecha->day : '',
            'mes' => $fecha ? self::MESES[$fecha->month] : '',
            'anio' => $fecha ? (string) $fecha->year : '',
            'ciudad' => $datos['ciudad'] ?? '',
            'provincia' => $datos['provincia'] ?? '',
            'nino_nombre' => $datos['nino_nombre'] ?? '',
            'referencia_detalle' => $this->referenciaDetalle($datos),
            'responsable_nombre' => $responsable['nombre'],
            'responsable_dni' => $responsable['dni'],
            'no_conviviente_nombre' => $datos['no_conviviente_nombre'] ?? '',
            'no_conviviente_dni' => $datos['no_conviviente_dni'] ?? '',
            'no_conviviente_desde' => $datos['no_conviviente_desde'] ?? '',
            'sol_nombre' => $responsable['nombre'],
            'sol_dni' => $responsable['dni'],
            'sol_cuil' => $responsable['cuil'],
            'sol_fecha_nacimiento' => $this->fechaCorta($responsable['fecha_nacimiento']),
            'sol_nacionalidad' => $responsable['nacionalidad'],
            'sol_domicilio' => $responsable['domicilio'],
            'sol_barrio' => $responsable['barrio'],
            'sol_localidad' => $responsable['localidad'],
            'sol_provincia' => $responsable['provincia'],
            'sol_telefono' => $responsable['telefono'],
            'motivos' => $datos['motivos'] ?? '',
        ];
    }

    /**
     * La tabla del informe deja siempre al menos cinco filas para completar a mano.
     *
     * @param  array<int, array<string, string|null>>  $miembros
     */
    private function completarGrupoFamiliar(TemplateProcessor $plantilla, array $miembros): void
    {
        $miembros = array_values(array_filter(
            $miembros,
            fn (array $miembro): bool => collect($miembro)->filter(fn ($valor) => filled($valor))->isNotEmpty(),
        ));
        $filas = max(5, count($miembros));

        $plantilla->cloneRow('gf_nombre', $filas);

        for ($fila = 1; $fila <= $filas; $fila++) {
            $miembro = $miembros[$fila - 1] ?? [];

            foreach (['nombre', 'vinculo', 'fecha_relacion', 'dni', 'fecha_nacimiento'] as $campo) {
                $valor = (string) ($miembro[$campo] ?? '');

                if (in_array($campo, ['fecha_relacion', 'fecha_nacimiento'], true)) {
                    $valor = $this->fechaCorta($valor);
                }

                $plantilla->setValue("gf_{$campo}#{$fila}", $this->escapar($valor, ''));
            }
        }
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function referenciaDetalle(array $datos): string
    {
        $partes = array_filter([
            filled($datos['nino_dni'] ?? null) ? "DNI Niño/a: {$datos['nino_dni']}." : null,
            $datos['referencia_detalle'] ?? null,
        ], fn ($parte) => filled($parte));

        return implode(' ', $partes);
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function personasVinculadas(?Persona $nino): Collection
    {
        if (! $nino) {
            return collect();
        }

        return VinculoFamiliar::query()
            ->with(['persona.localidad.provincia', 'persona.barrio', 'personaRelacionada.localidad.provincia', 'personaRelacionada.barrio'])
            ->whereNull('desvinculado_en')
            ->where(fn ($query) => $query->where('persona_id', $nino->id)->orWhere('persona_relacionada_id', $nino->id))
            ->oldest('id')
            ->get()
            ->map(function (VinculoFamiliar $vinculo) use ($nino): ?array {
                $persona = $vinculo->persona_id === $nino->id ? $vinculo->personaRelacionada : $vinculo->persona;

                return $persona ? $this->datosPersona($persona) + ['vinculo' => (string) $vinculo->tipo_vinculo] : null;
            })
            ->filter()
            ->unique('id')
            ->values();
    }

    /**
     * @return array<string, string>
     */
    private function datosPersona(Persona $persona): array
    {
        $domicilio = trim(implode(' ', array_filter([
            $persona->direccion_calle,
            $persona->direccion_numero,
            $persona->direccion_piso_depto,
        ])));

        return [
            'id' => (string) $persona->id,
            'nombre' => $this->nombreCompleto($persona),
            'dni' => (string) ($persona->dni ?? ''),
            'cuil' => '',
            'fecha_nacimiento' => $persona->fecha_nacimiento?->toDateString() ?? '',
            'nacionalidad' => '',
            'domicilio' => $domicilio,
            'barrio' => $persona->barrio?->nombre ?? '',
            'localidad' => $persona->localidad?->nombre ?? '',
            'provincia' => $persona->localidad?->provincia?->nombre ?? '',
            'telefono' => (string) ($persona->telefono ?? ''),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function responsableVacio(): array
    {
        return array_fill_keys(
            ['nombre', 'dni', 'cuil', 'fecha_nacimiento', 'nacionalidad', 'domicilio', 'barrio', 'localidad', 'provincia', 'telefono'],
            '',
        );
    }

    /**
     * Los vacíos llegan como null (ConvertEmptyStringsToNull); el formulario los espera como texto.
     *
     * @param  array<mixed>  $datos
     * @return array<mixed>
     */
    private function nulosAVacios(array $datos): array
    {
        return array_map(
            fn ($valor) => is_array($valor) ? $this->nulosAVacios($valor) : (string) ($valor ?? ''),
            $datos,
        );
    }

    private function nombreCompleto(Persona $persona): string
    {
        return trim("{$persona->apellido}, {$persona->nombre}", ', ');
    }

    private function fechaCorta(?string $fecha): string
    {
        if (blank($fecha)) {
            return '';
        }

        try {
            return CarbonImmutable::parse($fecha)->format('d/m/Y');
        } catch (\Throwable) {
            return $fecha;
        }
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

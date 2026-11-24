<?php

namespace App\Services;

use App\Models\Acta;
use App\Models\AuditoriaEvento;
use App\Models\Intervencion;
use App\Models\Legajo;
use App\Models\TipoIntervencion;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Registro de actas: genera el Word, lo guarda y, cuando el acta pertenece a un
 * legajo, la deja asentada como intervención. Lo usan Acta rápida y el alta de
 * intervenciones.
 */
class ActaService
{
    private const MIME_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    /** Roles de firmante que pertenecen al Servicio (no a la familia). */
    private const FIRMANTES_DEL_SERVICIO = ['equipo', 'autoridad', 'mds', 'centro', 'profesional'];

    public function __construct(
        private ActaDocumentoService $documentos,
        private AuhPlanillaService $planillasAuh,
    ) {}

    /**
     * Datos con los que se abre la hoja del acta.
     *
     * @return array<string, mixed>
     */
    public function datosIniciales(string $tipo, ?Legajo $legajo, User $usuario): array
    {
        if (Acta::esAuh($tipo)) {
            return $this->planillasAuh->datosIniciales($legajo, $legajo?->auhActual()->first())['datos'];
        }

        $legajo?->loadMissing('persona');
        $persona = $legajo?->persona;

        return [
            'fecha' => today()->toDateString(),
            'hora' => now()->format('H:i'),
            'operador' => $usuario->name,
            'compareciente_nombre' => '',
            'compareciente_dni' => '',
            'compareciente_domicilio' => '',
            'compareciente_barrio' => '',
            'compareciente_telefono' => '',
            'vinculo' => '',
            'nino_nombre' => $persona ? "{$persona->apellido}, {$persona->nombre}" : '',
            'nino_dni' => (string) ($persona?->dni ?? ''),
            'nino_fecha_nacimiento' => $persona?->fecha_nacimiento?->toDateString() ?? '',
            'nino_escuela' => '',
            'relato' => '',
            'estrategias' => '',
            'citacion_dia' => '',
            'citacion_horario' => '',
        ];
    }

    /**
     * Configuración de cada tipo de acta para las hojas del frontend.
     *
     * @return array<string, array{label: string, esAuh: bool, tipoAuh: string|null, firmantes: array<string, string>, datos: array<string, mixed>}>
     */
    public function configuracionParaHojas(?Legajo $legajo, User $usuario): array
    {
        $datosAuh = null;

        // Las planillas de AUH se cargan desde la sección AUH del legajo, no como intervención.
        return collect(Acta::TIPOS)->reject(fn (array $config) => isset($config['auh']))->map(function (array $config, string $tipo) use ($legajo, $usuario, &$datosAuh) {
            $esAuh = isset($config['auh']);
            $datosAuh ??= $esAuh ? $this->datosIniciales($tipo, $legajo, $usuario) : null;

            return [
                'label' => $config['label'],
                'esAuh' => $esAuh,
                'tipoAuh' => $config['auh'] ?? null,
                'firmantes' => Acta::firmantesDe($tipo),
                'datos' => $esAuh ? $datosAuh : $this->datosIniciales($tipo, $legajo, $usuario),
            ];
        })->all();
    }

    /**
     * Genera y guarda el acta. Si va a un legajo, queda asentada como intervención:
     * la indicada o, si no se indica, una nueva.
     *
     * @param  array<string, mixed>  $datos
     * @param  array<string, array{nombre: string, png: string}>  $firmas
     * @param  array<int, string>  $rutas  Archivos escritos, para limpiarlos si algo falla.
     */
    public function registrar(
        string $tipo,
        array $datos,
        array $firmas,
        User $usuario,
        ?Legajo $legajo = null,
        ?int $personaId = null,
        ?Intervencion $intervencion = null,
        array &$rutas = [],
    ): Acta {
        $firmantes = Acta::firmantesDe($tipo);
        $temporal = $this->documentos->generar($tipo, $datos, $firmas, $legajo?->numero_legajo);

        try {
            $ruta = 'actas/'.now()->format('Y/m').'/'.Str::uuid().'.docx';

            if (! Storage::disk('local')->put($ruta, (string) file_get_contents($temporal))) {
                throw new \RuntimeException('No se pudo guardar el acta.');
            }

            $rutas[] = $ruta;
        } finally {
            @unlink($temporal);
        }

        $acta = Acta::create([
            'tipo' => $tipo,
            'legajo_id' => $legajo?->id,
            'intervencion_id' => $intervencion?->id,
            'persona_id' => $personaId,
            'titulo' => $this->titulo($tipo, $datos),
            'fecha' => $datos['fecha'] ?? today()->toDateString(),
            'datos' => $datos,
            'firmado' => $firmantes !== [] && count($firmas) === count($firmantes),
            'firmas' => collect($firmas)->map(fn (array $firma, string $rol) => [
                'rol' => $rol,
                'rol_label' => $firmantes[$rol],
                'nombre' => $firma['nombre'],
                'firmado_en' => now()->toIso8601String(),
            ])->values()->all(),
            'nombre_archivo' => Str::slug(Acta::TIPOS[$tipo]['label']).'-'.now()->format('Y-m-d').'.docx',
            'ruta_archivo' => $ruta,
            'mime_type' => self::MIME_DOCX,
            'tamanio_bytes' => (int) Storage::disk('local')->size($ruta),
            'creado_por' => $usuario->id,
            'vinculado_por' => $legajo ? $usuario->id : null,
            'vinculado_en' => $legajo ? now() : null,
        ]);

        if ($legajo) {
            $this->asentarEnLegajo($acta, $legajo, $usuario, $intervencion, $rutas);
        }

        AuditoriaEvento::registrar('acta.creada', $acta, null, $acta->only(['tipo', 'legajo_id', 'intervencion_id', 'firmado']));

        return $acta;
    }

    /**
     * Vincula un acta hecha sin legajo: queda asentada como intervención.
     *
     * @param  array<int, string>  $rutas
     */
    public function vincular(Acta $acta, Legajo $legajo, User $usuario, array &$rutas = []): void
    {
        $acta->update([
            'legajo_id' => $legajo->id,
            'vinculado_por' => $usuario->id,
            'vinculado_en' => now(),
        ]);

        $this->asentarEnLegajo($acta, $legajo, $usuario, null, $rutas);

        AuditoriaEvento::registrar('acta.vinculada', $acta, null, ['legajo_id' => $legajo->id, 'intervencion_id' => $acta->intervencion_id]);
    }

    /**
     * Texto del detalle de la intervención: el relato del acta o, si no lo tiene,
     * una frase con lo esencial.
     *
     * @param  array<string, mixed>  $datos
     */
    public function resumen(string $tipo, array $datos): string
    {
        $relato = trim((string) ($datos['relato'] ?? ''));
        $estrategias = trim((string) ($datos['estrategias'] ?? ''));

        if ($relato !== '') {
            return $estrategias !== '' ? "{$relato}\n\nEstrategias a seguir: {$estrategias}" : $relato;
        }

        $nombre = $this->titulo($tipo, $datos);
        $fecha = fn (?string $valor) => filled($valor) ? CarbonImmutable::parse($valor)->format('d/m/Y') : null;

        $texto = match ($tipo) {
            'citacion' => collect([
                'Citación para presentarse',
                $fecha($datos['citacion_dia'] ?? null) ? 'el '.$fecha($datos['citacion_dia']) : null,
                filled($datos['citacion_horario'] ?? null) ? "en el horario de {$datos['citacion_horario']}" : null,
            ])->filter()->implode(' ').'.',
            'constancia' => "Constancia escolar de asistencia a entrevista de {$nombre}".
                (filled($datos['compareciente_dni'] ?? null) ? " (DNI {$datos['compareciente_dni']})" : '').'.',
            'auh_nota_elevacion' => "Nota de elevación de AUH: cambio de titularidad a {$nombre}.",
            'auh_declaracion_jurada' => "Declaración jurada de AUH de {$nombre}.",
            'auh_informe_relacion' => trim("Informe de recoversion de relación (AUH) de {$nombre}. ".trim((string) ($datos['motivos'] ?? ''))),
            default => Acta::etiquetaDe($tipo)." con {$nombre}.",
        };

        return Str::limit($texto, 10000, '');
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function titulo(string $tipo, array $datos): string
    {
        $principal = Acta::esAuh($tipo)
            ? ($datos['responsable']['nombre'] ?? null)
            : ($datos['compareciente_nombre'] ?? null);
        $nombre = filled($principal) ? $principal : ($datos['nino_nombre'] ?? null);

        return Str::limit(filled($nombre) ? (string) $nombre : 'Sin nombre', 255, '');
    }

    /**
     * Nombre de quien firmó por el Servicio, para la firma de la intervención.
     *
     * @param  array<int, array{rol: string, nombre: string}>|null  $firmas
     */
    public function firmanteDelServicio(?array $firmas): ?string
    {
        return collect($firmas ?? [])->first(fn (array $firma) => in_array($firma['rol'], self::FIRMANTES_DEL_SERVICIO, true))['nombre'] ?? null;
    }

    /**
     * @param  array<int, string>  $rutas
     */
    private function asentarEnLegajo(Acta $acta, Legajo $legajo, User $usuario, ?Intervencion $intervencion, array &$rutas): void
    {
        // Las planillas de AUH van a la sección AUH del legajo, sin intervención.
        if (! $intervencion && ! $acta->intervencion_id && ! Acta::esAuh($acta->tipo)) {
            $firmante = $this->firmanteDelServicio($acta->firmas);
            $intervencion = Intervencion::create([
                'legajo_id' => $legajo->id,
                'tipo_intervencion_id' => TipoIntervencion::firstOrCreate(
                    ['nombre' => Acta::TIPOS[$acta->tipo]['intervencion']],
                    ['activo' => true],
                )->id,
                'profesional_id' => $acta->creado_por ?? $usuario->id,
                'fecha_intervencion' => $acta->fecha?->toDateString() ?? today()->toDateString(),
                'hora_intervencion' => $acta->datos['hora'] ?? null,
                'detalle' => $this->resumen($acta->tipo, $acta->datos ?? []),
                'firma_nombre' => $firmante,
                'firma_aclaracion' => $firmante,
                'firmada_en' => $firmante ? now() : null,
                'creado_por' => $usuario->id,
                'actualizado_por' => $usuario->id,
            ]);
            $acta->update(['intervencion_id' => $intervencion->id]);
        }

        $this->sumarAAuhDelLegajo($acta, $legajo, $usuario->id, $rutas);
    }

    /**
     * Una planilla de AUH firmada pasa a ser la vigente en la sección AUH del legajo.
     *
     * @param  array<int, string>  $rutas
     */
    private function sumarAAuhDelLegajo(Acta $acta, Legajo $legajo, int $usuarioId, array &$rutas): void
    {
        $tipoAuh = Acta::TIPOS[$acta->tipo]['auh'] ?? null;

        if (! $tipoAuh || ! $acta->firmado) {
            return;
        }

        $auh = $legajo->auhActual()->first() ?? $legajo->auhs()->create([
            'estado' => 'activo',
            'activo' => true,
            'creado_por' => $usuarioId,
            'actualizado_por' => $usuarioId,
        ]);

        $auh->archivos()->where('tipo', $tipoAuh)->where('activo', true)->update(['activo' => false, 'reemplazado_en' => now()]);

        $ruta = "legajos/{$legajo->id}/auh/{$auh->id}/".Str::uuid().'.docx';
        Storage::disk('local')->copy($acta->ruta_archivo, $ruta);
        $rutas[] = $ruta;

        $auh->archivos()->create([
            'tipo' => $tipoAuh,
            'nombre_archivo' => $acta->nombre_archivo,
            'ruta_archivo' => $ruta,
            'mime_type' => $acta->mime_type,
            'tamanio_bytes' => $acta->tamanio_bytes,
            'firmado' => true,
            'firmas' => $acta->firmas,
            'activo' => true,
            'subido_por' => $usuarioId,
        ]);
    }
}

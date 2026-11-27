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

    /** Campos de las actas que no son de AUH (los que completa la hoja). */
    private const CAMPOS_ACTA = [
        'fecha', 'hora', 'operador',
        'compareciente_nombre', 'compareciente_dni', 'compareciente_domicilio', 'compareciente_barrio', 'compareciente_telefono', 'vinculo',
        'nino_nombre', 'nino_dni', 'nino_fecha_nacimiento', 'nino_escuela',
        'relato', 'estrategias', 'citacion_dia', 'citacion_horario',
    ];

    public function __construct(
        private ActaDocumentoService $documentos,
        private AuhPlanillaService $planillasAuh,
        private PuntajeService $puntaje,
    ) {}

    /**
     * Responsable del acta: quien la carga. Un administrativo carga actas hechas por
     * otra persona, así que en ese caso no hay responsable fijo y se indica a mano.
     */
    public function responsableFijo(?User $autor): ?string
    {
        if (! $autor || $autor->tieneRol('administrativo')) {
            return null;
        }

        return filled($autor->name) ? $autor->name : null;
    }

    /**
     * Fija al responsable en el acta: es el operador/a y quien firma por el Servicio.
     *
     * @param  array<string, mixed>  $datos
     * @param  array<string, array{nombre: string, png: string}>  $firmas
     */
    public function aplicarResponsable(string $tipo, array &$datos, array &$firmas, ?User $autor): void
    {
        $responsable = $this->responsableFijo($autor);

        if ($responsable === null || Acta::esAuh($tipo)) {
            return;
        }

        $datos['operador'] = $responsable;

        foreach (array_keys(Acta::firmantesDe($tipo)) as $rol) {
            if (in_array($rol, self::FIRMANTES_DEL_SERVICIO, true) && isset($firmas[$rol])) {
                $firmas[$rol]['nombre'] = $responsable;
            }
        }
    }

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
            'operador' => $this->responsableFijo($usuario) ?? '',
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
        ?User $responsable = null,
    ): Acta {
        // Responsable: el profesional de la intervención si se indica; si no, quien carga el acta.
        $this->aplicarResponsable($tipo, $datos, $firmas, $responsable ?? $usuario);
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

        $this->puntaje->registrarActa($acta, $usuario);

        AuditoriaEvento::registrar('acta.creada', $acta, null, $acta->only(['tipo', 'legajo_id', 'intervencion_id', 'firmado']));

        return $acta;
    }

    /**
     * Hoja de un acta ya guardada, para mostrarla igual que el Word (ver, previsualizar o editar).
     *
     * Solo se puede editar mientras no tenga firmas: las firmas dibujadas viven únicamente
     * dentro del Word, así que regenerarlo las perdería.
     *
     * @return array{id: int, tipo: string, etiqueta: string, es_auh: bool, fecha: string|null, creado_por: string|null, datos: array<string, mixed>, firmantes: array<string, string>, firmas: array<string, string>, firmados: array<int, string>, firmas_imagenes: array<string, string>, firmado: bool, requiere_firma: bool, editable: bool, responsable_fijo: string|null, legajo_numero: string|null, descarga_url: string, descarga_pdf_url: string}
     */
    public function hoja(Acta $acta): array
    {
        $acta->loadMissing(['legajo', 'creadoPor']);
        $firmas = collect($acta->firmas ?? []);
        $datos = $acta->datos ?? [];

        if (! Acta::esAuh($acta->tipo)) {
            $datos = [...array_fill_keys(self::CAMPOS_ACTA, ''), ...array_map(fn ($valor) => $valor ?? '', $datos)];
        }

        return [
            'id' => $acta->id,
            'tipo' => $acta->tipo,
            'etiqueta' => $acta->etiqueta(),
            'es_auh' => Acta::esAuh($acta->tipo),
            'fecha' => $acta->fecha?->toDateString(),
            'creado_por' => $acta->creadoPor?->name,
            'datos' => $datos,
            'firmantes' => Acta::firmantesDe($acta->tipo),
            'firmas' => $firmas->mapWithKeys(fn (array $firma) => [$firma['rol'] => $firma['nombre']])->all(),
            'firmados' => $firmas->pluck('rol')->values()->all(),
            'firmas_imagenes' => $this->imagenesDeFirmas($acta),
            'firmado' => (bool) $acta->firmado,
            'requiere_firma' => Acta::firmantesDe($acta->tipo) !== [],
            'editable' => ! Acta::esAuh($acta->tipo) && $firmas->isEmpty(),
            'responsable_fijo' => $this->responsableFijo($acta->intervencion?->profesional ?? $acta->creadoPor),
            'legajo_numero' => $acta->legajo?->numero_legajo,
            'descarga_url' => route('actas.descargar', $acta),
            'descarga_pdf_url' => route('actas.descargar', ['acta' => $acta, 'formato' => 'pdf']),
        ];
    }

    /**
     * Firmas dibujadas del acta, como data URI por rol, leídas del propio Word.
     *
     * Las firmas se insertan en el documento en el orden de los firmantes del tipo, y
     * PhpWord numera cada imagen nueva (image_{n}_document.png) en ese mismo orden. Si la
     * cantidad no coincide con quienes firmaron, no se arriesga una asignación incorrecta.
     *
     * @return array<string, string>
     */
    public function imagenesDeFirmas(Acta $acta): array
    {
        $firmados = collect($acta->firmas ?? [])->pluck('rol');
        $disco = Storage::disk('local');

        if ($firmados->isEmpty() || Acta::esAuh($acta->tipo) || ! $acta->ruta_archivo || ! $disco->exists($acta->ruta_archivo)) {
            return [];
        }

        $zip = new \ZipArchive;

        if ($zip->open($disco->path($acta->ruta_archivo)) !== true) {
            return [];
        }

        try {
            $imagenes = [];

            for ($indice = 0; $indice < $zip->numFiles; $indice++) {
                $nombre = (string) $zip->getNameIndex($indice);

                if (preg_match('/^word\/media\/image_(?:rId)?(\d+)_document\.(png|jpe?g)$/i', $nombre, $coincidencia)) {
                    $imagenes[(int) $coincidencia[1]] = [$nombre, strtolower($coincidencia[2]) === 'png' ? 'png' : 'jpeg'];
                }
            }

            ksort($imagenes);
            $roles = collect(array_keys(Acta::firmantesDe($acta->tipo)))->filter(fn (string $rol) => $firmados->contains($rol))->values();

            if (count($imagenes) !== $roles->count()) {
                return [];
            }

            return collect(array_values($imagenes))
                ->mapWithKeys(fn (array $imagen, int $posicion) => [
                    $roles[$posicion] => "data:image/{$imagen[1]};base64,".base64_encode((string) $zip->getFromName($imagen[0])),
                ])
                ->all();
        } finally {
            $zip->close();
        }
    }

    /**
     * Corrige un acta sin firmas: regenera el Word con los datos nuevos (y las firmas que
     * se agreguen ahora) y reemplaza el archivo guardado.
     *
     * @param  array<string, mixed>  $datos
     * @param  array<string, array{nombre: string, png: string}>  $firmas
     * @param  array<int, string>  $rutas  Archivos escritos, para limpiarlos si algo falla.
     * @param  array<int, string>  $obsoletas  Archivos a borrar cuando la transacción termine bien.
     */
    public function actualizar(Acta $acta, array $datos, array $firmas, User $usuario, array &$rutas = [], array &$obsoletas = [], ?User $responsable = null): Acta
    {
        $acta->loadMissing(['legajo', 'creadoPor']);
        // Responsable: el profesional indicado; si no, quien cargó el acta (aunque la corrija otra persona).
        $this->aplicarResponsable($acta->tipo, $datos, $firmas, $responsable ?? $acta->creadoPor ?? $usuario);
        $firmantes = Acta::firmantesDe($acta->tipo);
        $temporal = $this->documentos->generar($acta->tipo, $datos, $firmas, $acta->legajo?->numero_legajo);

        try {
            $ruta = 'actas/'.now()->format('Y/m').'/'.Str::uuid().'.docx';

            if (! Storage::disk('local')->put($ruta, (string) file_get_contents($temporal))) {
                throw new \RuntimeException('No se pudo guardar el acta.');
            }

            $rutas[] = $ruta;
        } finally {
            @unlink($temporal);
        }

        $obsoletas[] = $acta->ruta_archivo;
        $obsoletas[] = ConversorPdfService::rutaPdf($acta->ruta_archivo);
        $anterior = $acta->only(['titulo', 'fecha', 'firmado']);

        $acta->update([
            'titulo' => $this->titulo($acta->tipo, $datos),
            'fecha' => $datos['fecha'] ?? $acta->fecha?->toDateString() ?? today()->toDateString(),
            'datos' => $datos,
            'firmado' => $firmantes !== [] && count($firmas) === count($firmantes),
            'firmas' => collect($firmas)->map(fn (array $firma, string $rol) => [
                'rol' => $rol,
                'rol_label' => $firmantes[$rol],
                'nombre' => $firma['nombre'],
                'firmado_en' => now()->toIso8601String(),
            ])->values()->all(),
            'ruta_archivo' => $ruta,
            'tamanio_bytes' => (int) Storage::disk('local')->size($ruta),
        ]);

        AuditoriaEvento::registrar('acta.actualizada', $acta, $anterior, $acta->only(['titulo', 'fecha', 'firmado']));

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

        $this->puntaje->registrarActaVinculada($acta, $usuario);

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

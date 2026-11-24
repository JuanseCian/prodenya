<?php

namespace App\Models;

use Database\Factories\ActaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acta extends Model
{
    /** @use HasFactory<ActaFactory> */
    use HasFactory;

    protected $table = 'actas';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /**
     * Roles que pueden hacer, descargar y vincular actas.
     *
     * @var array<int, string>
     */
    public const ROLES = ['admin', 'operador', 'coordinador', 'profesional', 'administrativo'];

    /**
     * Tipos de acta disponibles. Las de AUH reutilizan las planillas de esa sección.
     *
     * @var array<string, array{label: string, grupo: string, descripcion: string, intervencion: string, plantilla?: string, auh?: string, firmantes?: array<string, string>}>
     */
    public const TIPOS = [
        'inicial' => [
            'label' => 'Acta inicial',
            'grupo' => 'Actas',
            'descripcion' => 'Comparecencia en sede: datos, relato y estrategias.',
            'plantilla' => 'inicial',
            'intervencion' => 'Acta inicial',
            'firmantes' => ['compareciente' => 'Compareciente', 'equipo' => 'Equipo técnico'],
        ],
        'domiciliaria' => [
            'label' => 'Acta de visita domiciliaria',
            'grupo' => 'Actas',
            'descripcion' => 'Visita al domicilio y manifestación.',
            'plantilla' => 'domiciliaria',
            'intervencion' => 'Acta de visita domiciliaria',
            'firmantes' => ['entrevistado' => 'Entrevistado/a', 'autoridad' => 'Autoridad del Servicio'],
        ],
        'telefonica' => [
            'label' => 'Acta telefónica',
            'grupo' => 'Actas',
            'descripcion' => 'Comunicación telefónica y manifestación.',
            'plantilla' => 'telefonica',
            'intervencion' => 'Acta telefónica',
        ],
        'citacion' => [
            'label' => 'Citación',
            'grupo' => 'Notas',
            'descripcion' => 'Citación a presentarse en el Servicio.',
            'plantilla' => 'citacion',
            'intervencion' => 'Citación',
        ],
        'constancia' => [
            'label' => 'Constancia escolar',
            'grupo' => 'Notas',
            'descripcion' => 'Constancia de asistencia a entrevista.',
            'plantilla' => 'constancia',
            'intervencion' => 'Constancia escolar',
        ],
        'auh_nota_elevacion' => [
            'label' => 'Nota de elevación',
            'grupo' => 'AUH',
            'descripcion' => 'Cambio de titularidad ante ANSES.',
            'auh' => 'nota_elevacion',
            'intervencion' => 'AUH · Nota de elevación',
        ],
        'auh_declaracion_jurada' => [
            'label' => 'Declaración jurada',
            'grupo' => 'AUH',
            'descripcion' => 'No convivencia con el núcleo familiar.',
            'auh' => 'declaracion_jurada',
            'intervencion' => 'AUH · Declaración jurada',
        ],
        'auh_informe_relacion' => [
            'label' => 'Informe de recoversion de relación',
            'grupo' => 'AUH',
            'descripcion' => 'Datos del solicitante y grupo familiar.',
            'auh' => 'informe_relacion',
            'intervencion' => 'AUH · Informe de recoversion de relación',
        ],
    ];

    protected $fillable = [
        'tipo',
        'legajo_id',
        'intervencion_id',
        'persona_id',
        'titulo',
        'fecha',
        'datos',
        'firmado',
        'firmas',
        'nombre_archivo',
        'ruta_archivo',
        'mime_type',
        'tamanio_bytes',
        'creado_por',
        'vinculado_por',
        'vinculado_en',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'datos' => 'array',
            'firmado' => 'boolean',
            'firmas' => 'array',
            'tamanio_bytes' => 'integer',
            'vinculado_en' => 'datetime',
        ];
    }

    /**
     * Quién firma cada tipo de acta.
     *
     * @return array<string, string>
     */
    public static function firmantesDe(string $tipo): array
    {
        $config = self::TIPOS[$tipo];

        return isset($config['auh']) ? Auh::FIRMANTES[$config['auh']] : ($config['firmantes'] ?? []);
    }

    /**
     * Tipo de acta que genera un tipo de intervención, si genera alguno.
     */
    public static function tipoDesdeIntervencion(?string $nombreTipo): ?string
    {
        foreach (self::TIPOS as $tipo => $config) {
            if ($config['intervencion'] === $nombreTipo) {
                return $tipo;
            }
        }

        return null;
    }

    public static function esAuh(string $tipo): bool
    {
        return isset(self::TIPOS[$tipo]['auh']);
    }

    public static function etiquetaDe(string $tipo): string
    {
        $config = self::TIPOS[$tipo] ?? null;

        if (! $config) {
            return $tipo;
        }

        return isset($config['auh']) ? "AUH · {$config['label']}" : $config['label'];
    }

    public function etiqueta(): string
    {
        return self::etiquetaDe($this->tipo);
    }

    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Legajo::class, 'legajo_id');
    }

    public function intervencion(): BelongsTo
    {
        return $this->belongsTo(Intervencion::class, 'intervencion_id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}

<?php

namespace App\Models;

use Database\Factories\AuhFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auh extends Model
{
    /** @use HasFactory<AuhFactory> */
    use HasFactory;

    /**
     * Planillas que se confeccionan para el trámite de AUH.
     *
     * @var array<string, string>
     */
    public const PLANILLAS = [
        'nota_elevacion' => 'Nota de elevación',
        'declaracion_jurada' => 'Declaración jurada',
        'informe_relacion' => 'Informe de recoversion de relación',
    ];

    /**
     * Quién debe firmar cada planilla, según el modelo.
     *
     * @var array<string, array<string, string>>
     */
    public const FIRMANTES = [
        'nota_elevacion' => ['solicitante' => 'Solicitante a cobrar', 'centro' => 'Centro de referencia'],
        'declaracion_jurada' => ['mds' => 'Firma por MDS', 'responsable' => 'Adulto responsable'],
        'informe_relacion' => ['profesional' => 'Profesional interviniente'],
    ];

    /**
     * Documentación respaldatoria obligatoria del trámite.
     *
     * @var array<string, string>
     */
    public const DOCUMENTACION = [
        'documentacion_menor' => 'Documentación del niño/a',
        'documentacion_responsable' => 'Documentación del responsable',
        'certificado_nacimiento' => 'Certificado de nacimiento',
    ];

    /**
     * Firmantes cuya aclaración es el adulto responsable / titular de la planilla.
     *
     * @var array<int, string>
     */
    public const FIRMANTES_RESPONSABLE = ['responsable', 'solicitante'];

    /**
     * Roles que pueden cargar o quitar documentación de AUH.
     *
     * @var array<int, string>
     */
    public const ROLES_GESTION = ['admin', 'operador', 'coordinador', 'profesional', 'administrativo'];

    /**
     * Tipo que admite varios archivos, cada uno con su descripción.
     */
    public const TIPO_OTRA = 'otra';

    protected $table = 'auh';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    public const ESTADO_ACTIVO = 'activo';

    public const ESTADO_SUSPENDIDO = 'suspendido';

    public const ESTADO_DADO_DE_BAJA = 'dado_de_baja';

    protected $fillable = [
        'legajo_id',
        'titular_persona_id',
        'estado',
        'motivo_suspension',
        'fecha_inicio',
        'fecha_baja',
        'monto',
        'numero_asignacion',
        'observaciones',
        'datos_planillas',
        'activo',
        'foto_nombre_archivo',
        'foto_ruta_archivo',
        'foto_mime_type',
        'foto_tamanio_bytes',
        'foto_subido_por',
        'foto_subida_en',
        'creado_por',
        'actualizado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_baja' => 'date',
            'monto' => 'decimal:2',
            'datos_planillas' => 'array',
            'activo' => 'boolean',
            'foto_subida_en' => 'datetime',
        ];
    }

    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Legajo::class, 'legajo_id');
    }

    public function titular(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'titular_persona_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    public function fotoSubidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'foto_subido_por');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(AuhArchivo::class, 'auh_id');
    }

    public function archivosActivos(): HasMany
    {
        return $this->archivos()->where('activo', true)->latest('id');
    }

    /**
     * @return array<string, string>
     */
    public static function tipoLabels(): array
    {
        return self::PLANILLAS + self::DOCUMENTACION + [self::TIPO_OTRA => 'Otra documentación'];
    }

    /**
     * @return array<int, string>
     */
    public static function tiposObligatorios(): array
    {
        return array_keys(self::PLANILLAS + self::DOCUMENTACION);
    }

    public function tieneFoto(): bool
    {
        return ! is_null($this->foto_ruta_archivo);
    }
}

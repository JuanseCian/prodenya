<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auh extends Model
{
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

    public function tieneFoto(): bool
    {
        return ! is_null($this->foto_ruta_archivo);
    }
}

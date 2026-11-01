<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CudCertificado extends Model
{
    protected $table = 'cud_certificados';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'legajo_id',
        'numero_cud',
        'fecha_vigencia',
        'fecha_vencimiento',
        'observaciones',
        'activo',
        'creado_por',
        'actualizado_por',
        'reemplazado_en',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vigencia' => 'date',
            'fecha_vencimiento' => 'date',
            'activo' => 'boolean',
            'reemplazado_en' => 'datetime',
        ];
    }

    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Legajo::class, 'legajo_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(CudArchivo::class, 'cud_certificado_id');
    }

    public function archivosActivos(): HasMany
    {
        return $this->archivos()->where('activo', true);
    }

    public function estado(): string
    {
        if ($this->fecha_vencimiento?->isBefore(today())) {
            return 'Vencido';
        }

        if ($this->fecha_vencimiento?->isBefore(today()->addDays(31))) {
            return 'Próximo a vencer';
        }

        return 'Vigente';
    }
}

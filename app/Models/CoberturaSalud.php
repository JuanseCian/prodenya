<?php

namespace App\Models;

use Database\Factories\CoberturaSaludFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoberturaSalud extends Model
{
    /** @use HasFactory<CoberturaSaludFactory> */
    use HasFactory;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    public const TIPOS = [
        'municipal',
        'obra_social',
        'prepaga',
        'pami_publica',
        'otra',
        'sin_cobertura',
    ];

    public const ORIGENES = [
        'municipal',
        'relacion_dependencia',
        'monotributo',
        'jubilacion_pension',
        'familiar_a_cargo',
        'voluntaria_particular',
        'otro',
        'no_aplica',
    ];

    protected $fillable = [
        'persona_id',
        'tipo',
        'origen',
        'programa',
        'entidad',
        'plan',
        'numero_afiliado',
        'persona_es_titular',
        'titular_nombre',
        'titular_dni',
        'titular_cuil',
        'parentesco_titular',
        'fecha_desde',
        'fecha_hasta',
        'observaciones',
        'activo',
        'creado_por',
        'actualizado_por',
        'reemplazado_en',
    ];

    protected function casts(): array
    {
        return [
            'persona_es_titular' => 'boolean',
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
            'activo' => 'boolean',
            'reemplazado_en' => 'datetime',
        ];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(CoberturaSaludArchivo::class, 'cobertura_salud_id');
    }

    public function archivosActivos(): HasMany
    {
        return $this->archivos()->where('activo', true);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    public function estado(): string
    {
        if ($this->tipo === 'sin_cobertura') {
            return 'Sin cobertura';
        }

        if ($this->fecha_desde?->isFuture()) {
            return 'Programada';
        }

        if ($this->fecha_hasta?->isBefore(today())) {
            return 'Finalizada';
        }

        return $this->activo ? 'Vigente' : 'Histórica';
    }
}

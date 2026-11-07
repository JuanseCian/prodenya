<?php

namespace App\Models;

use Database\Factories\MedidaAbrigoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedidaAbrigo extends Model
{
    public const UBICACIONES = ['familia_ampliada', 'institucion'];

    public const DURACIONES = [90, 180];

    /** @var array<string, string> */
    public const UBICACION_LABELS = [
        'familia_ampliada' => 'Familia ampliada',
        'institucion' => 'Institución',
    ];

    /** @var array<string, string> */
    public const ACTA_LABELS = [
        'per' => 'PER',
        'solicitud_vacante' => 'Solicitud de vacante',
        'informe' => 'Informe',
    ];

    /** @use HasFactory<MedidaAbrigoFactory> */
    use HasFactory;

    protected $table = 'medida_abrigos';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'legajo_id',
        'intervencion_id',
        'ubicacion',
        'duracion_dias',
        'fecha_inicio',
        'fecha_fin',
        'observaciones',
        'creado_por',
        'actualizado_por',
    ];

    protected function casts(): array
    {
        return [
            'duracion_dias' => 'integer',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Legajo::class, 'legajo_id');
    }

    public function intervencion(): BelongsTo
    {
        return $this->belongsTo(Intervencion::class, 'intervencion_id');
    }

    public function actas(): HasMany
    {
        return $this->hasMany(MedidaAbrigoActa::class, 'medida_abrigo_id')->latest('id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function estado(): string
    {
        return $this->fecha_fin?->isBefore(today()) ? 'Vencida' : 'Vigente';
    }

    public function diasRestantes(): ?int
    {
        if (! $this->fecha_fin) {
            return null;
        }

        return max(0, today()->diffInDays($this->fecha_fin, false));
    }
}

<?php

namespace App\Models;

use Database\Factories\PuntajeMovimientoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PuntajeMovimiento extends Model
{
    /** @use HasFactory<PuntajeMovimientoFactory> */
    use HasFactory;

    protected $table = 'puntaje_movimientos';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'usuario_id',
        'puntos',
        'evento',
        'clave',
        'descripcion',
        'origen_type',
        'origen_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'puntos' => 'integer',
            'metadata' => 'array',
            'creado_en' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function origen(): MorphTo
    {
        return $this->morphTo('origen', 'origen_type', 'origen_id');
    }
}

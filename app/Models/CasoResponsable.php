<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CasoResponsable extends Model
{
    protected $table = 'caso_responsables';

    protected $fillable = [
        'legajo_id',
        'usuario_id',
        'asignado_por',
        'retirado_por',
        'es_principal',
        'activo',
        'asignado_en',
        'retirado_en',
    ];

    protected function casts(): array
    {
        return [
            'es_principal' => 'boolean',
            'activo' => 'boolean',
            'asignado_en' => 'datetime',
            'retirado_en' => 'datetime',
        ];
    }

    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Legajo::class, 'legajo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por');
    }

    public function retiradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'retirado_por');
    }
}

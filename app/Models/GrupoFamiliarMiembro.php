<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrupoFamiliarMiembro extends Model
{
    protected $table = 'grupo_familiar_miembros';

    protected $fillable = [
        'grupo_familiar_id',
        'persona_id',
        'creado_por',
        'desvinculado_en',
        'desvinculado_por',
    ];

    protected function casts(): array
    {
        return ['desvinculado_en' => 'datetime'];
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoFamiliar::class, 'grupo_familiar_id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function desvinculadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'desvinculado_por');
    }
}

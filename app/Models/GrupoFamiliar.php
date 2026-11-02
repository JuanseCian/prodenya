<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoFamiliar extends Model
{
    protected $table = 'grupos_familiares';

    protected $fillable = ['nombre', 'observaciones', 'creado_por'];

    public function miembros(): HasMany
    {
        return $this->hasMany(GrupoFamiliarMiembro::class)->whereNull('desvinculado_en');
    }

    public function vinculos(): HasMany
    {
        return $this->hasMany(VinculoFamiliar::class)->whereNull('desvinculado_en');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}

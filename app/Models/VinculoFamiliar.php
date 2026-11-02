<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VinculoFamiliar extends Model
{
    public const TIPOS = [
        'hermano/a',
        'primo/a',
        'tío/a',
        'sobrino/a',
        'madre',
        'padre',
        'hijo/a',
        'abuelo/a',
        'nieto/a',
        'tutor/a',
        'referente afectivo',
        'pareja',
        'otro familiar',
    ];

    protected $table = 'vinculos_familiares';

    protected $fillable = [
        'grupo_familiar_id',
        'persona_id',
        'persona_relacionada_id',
        'tipo_vinculo',
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

    public function personaRelacionada(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_relacionada_id');
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

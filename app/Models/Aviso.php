<?php

namespace App\Models;

use Database\Factories\AvisoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aviso extends Model
{
    /** @use HasFactory<AvisoFactory> */
    use HasFactory;

    protected $attributes = [
        'activo' => true,
    ];

    protected $fillable = [
        'titulo',
        'mensaje',
        'url',
        'creado_por',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}

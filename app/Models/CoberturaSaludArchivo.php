<?php

namespace App\Models;

use Database\Factories\CoberturaSaludArchivoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoberturaSaludArchivo extends Model
{
    /** @use HasFactory<CoberturaSaludArchivoFactory> */
    use HasFactory;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'cobertura_salud_id',
        'tipo',
        'nombre_archivo',
        'ruta_archivo',
        'mime_type',
        'tamanio_bytes',
        'activo',
        'subido_por',
        'reemplazado_en',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'reemplazado_en' => 'datetime',
        ];
    }

    public function cobertura(): BelongsTo
    {
        return $this->belongsTo(CoberturaSalud::class, 'cobertura_salud_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}

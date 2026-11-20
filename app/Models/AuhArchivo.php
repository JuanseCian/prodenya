<?php

namespace App\Models;

use Database\Factories\AuhArchivoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuhArchivo extends Model
{
    /** @use HasFactory<AuhArchivoFactory> */
    use HasFactory;

    protected $table = 'auh_archivos';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'auh_id',
        'tipo',
        'descripcion',
        'nombre_archivo',
        'ruta_archivo',
        'mime_type',
        'tamanio_bytes',
        'firmado',
        'firmas',
        'activo',
        'subido_por',
        'reemplazado_en',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'tamanio_bytes' => 'integer',
            'firmado' => 'boolean',
            'firmas' => 'array',
            'reemplazado_en' => 'datetime',
        ];
    }

    public function auh(): BelongsTo
    {
        return $this->belongsTo(Auh::class, 'auh_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}

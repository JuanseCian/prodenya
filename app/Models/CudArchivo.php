<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CudArchivo extends Model
{
    protected $table = 'cud_archivos';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'cud_certificado_id',
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

    public function certificado(): BelongsTo
    {
        return $this->belongsTo(CudCertificado::class, 'cud_certificado_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}

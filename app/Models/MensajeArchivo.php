<?php

namespace App\Models;

use Database\Factories\MensajeArchivoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensajeArchivo extends Model
{
    /** @use HasFactory<MensajeArchivoFactory> */
    use HasFactory;

    protected $table = 'mensaje_archivos';

    protected $fillable = [
        'mensaje_id',
        'nombre_archivo',
        'ruta_archivo',
        'mime_type',
        'tamanio_bytes',
    ];

    protected function casts(): array
    {
        return [
            'tamanio_bytes' => 'integer',
        ];
    }

    public function mensaje(): BelongsTo
    {
        return $this->belongsTo(Mensaje::class, 'mensaje_id');
    }
}

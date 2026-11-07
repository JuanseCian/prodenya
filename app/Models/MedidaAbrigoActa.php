<?php

namespace App\Models;

use Database\Factories\MedidaAbrigoActaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedidaAbrigoActa extends Model
{
    /** @use HasFactory<MedidaAbrigoActaFactory> */
    use HasFactory;

    protected $table = 'medida_abrigo_actas';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'medida_abrigo_id',
        'tipo',
        'nombre_archivo',
        'ruta_archivo',
        'mime_type',
        'tamanio_bytes',
        'subido_por',
    ];

    public function medida(): BelongsTo
    {
        return $this->belongsTo(MedidaAbrigo::class, 'medida_abrigo_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}

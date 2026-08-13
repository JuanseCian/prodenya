<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaDescarga extends Model
{
    protected $table = 'auditoria_descargas';

    const CREATED_AT = 'fecha_descarga';
    const UPDATED_AT = null;

    protected $fillable = [
        'documento_id',
        'usuario_id',
        'ip_origen',
        'user_agent',
    ];

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'documento_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}

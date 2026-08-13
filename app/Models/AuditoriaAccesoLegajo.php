<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaAccesoLegajo extends Model
{
    protected $table = 'auditoria_accesos_legajo';

    const CREATED_AT = 'fecha_acceso';
    const UPDATED_AT = null;

    protected $fillable = [
        'legajo_id',
        'usuario_id',
        'tipo_accion',
        'ip_origen',
        'user_agent',
    ];

    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Legajo::class, 'legajo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}

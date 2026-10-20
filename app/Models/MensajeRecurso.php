<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensajeRecurso extends Model
{
    use Auditable;

    protected $table = 'mensaje_recursos';

    protected $fillable = [
        'mensaje_id',
        'tipo_recurso',
        'recurso_id',
    ];

    public function mensaje(): BelongsTo
    {
        return $this->belongsTo(Mensaje::class, 'mensaje_id');
    }
}

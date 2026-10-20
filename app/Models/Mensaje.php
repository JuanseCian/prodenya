<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mensaje extends Model
{
    use Auditable;

    protected $table = 'mensajes';

    protected $fillable = [
        'conversacion_id',
        'usuario_id',
        'cuerpo',
    ];

    public function conversacion(): BelongsTo
    {
        return $this->belongsTo(Conversacion::class, 'conversacion_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function recursos(): HasMany
    {
        return $this->hasMany(MensajeRecurso::class, 'mensaje_id');
    }
}

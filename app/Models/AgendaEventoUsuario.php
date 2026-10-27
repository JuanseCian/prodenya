<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AgendaEventoUsuario extends Pivot
{
    protected $table = 'agenda_evento_usuario';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'agenda_evento_id',
        'usuario_id',
    ];

    public function agendaEvento(): BelongsTo
    {
        return $this->belongsTo(AgendaEvento::class, 'agenda_evento_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}

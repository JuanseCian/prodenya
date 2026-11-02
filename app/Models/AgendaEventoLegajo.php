<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AgendaEventoLegajo extends Pivot
{
    protected $table = 'agenda_evento_legajo';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'agenda_evento_id',
        'legajo_id',
    ];

    public function agendaEvento(): BelongsTo
    {
        return $this->belongsTo(AgendaEvento::class, 'agenda_evento_id');
    }

    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Legajo::class, 'legajo_id');
    }
}

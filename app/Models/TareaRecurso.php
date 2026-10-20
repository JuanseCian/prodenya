<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TareaRecurso extends Model
{
    use Auditable;

    protected $table = 'tarea_recursos';

    protected $fillable = [
        'tarea_id',
        'tipo_recurso',
        'recurso_id',
    ];

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(Tarea::class, 'tarea_id');
    }
}

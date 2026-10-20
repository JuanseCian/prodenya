<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarea extends Model
{
    use Auditable;

    protected $table = 'tareas';

    protected $fillable = [
        'tipo',
        'titulo',
        'descripcion',
        'prioridad',
        'estado',
        'fecha_limite',
        'asignado_a',
        'creado_por',
        'legajo_id',
        'intervencion_id',
        'mail_message_id',
        'completada_en',
    ];

    protected function casts(): array
    {
        return [
            'fecha_limite' => 'date',
            'completada_en' => 'datetime',
        ];
    }

    public function asignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Legajo::class, 'legajo_id');
    }

    public function intervencion(): BelongsTo
    {
        return $this->belongsTo(Intervencion::class, 'intervencion_id');
    }

    public function mail(): BelongsTo
    {
        return $this->belongsTo(MailMessage::class, 'mail_message_id');
    }

    public function recursos(): HasMany
    {
        return $this->hasMany(TareaRecurso::class, 'tarea_id');
    }
}

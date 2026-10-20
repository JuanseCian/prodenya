<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AgendaEvento extends Model
{
    use Auditable;

    protected $table = 'agenda_eventos';

    protected $fillable = [
        'titulo',
        'tipo',
        'visibilidad',
        'fecha_inicio',
        'fecha_fin',
        'lugar',
        'enlace',
        'participantes_externos',
        'notas',
        'estado',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'participantes_externos' => 'array',
        ];
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function legajos(): BelongsToMany
    {
        return $this->belongsToMany(Legajo::class, 'agenda_evento_legajo', 'agenda_evento_id', 'legajo_id');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'agenda_evento_usuario', 'agenda_evento_id', 'usuario_id');
    }
}

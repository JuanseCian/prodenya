<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversacion extends Model
{
    use Auditable;

    protected $table = 'conversaciones';

    protected $fillable = [
        'tipo',
        'titulo',
        'creado_por',
        'ultimo_mensaje_en',
    ];

    protected function casts(): array
    {
        return [
            'ultimo_mensaje_en' => 'datetime',
        ];
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'conversacion_usuario',
            'conversacion_id',
            'usuario_id',
        )->withPivot('ultimo_leido_en')->withTimestamps();
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(Mensaje::class, 'conversacion_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}

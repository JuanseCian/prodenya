<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Intervencion extends Model
{
    protected $table = 'intervenciones';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'legajo_id',
        'tipo_intervencion_id',
        'profesional_id',
        'fecha_intervencion',
        'detalle',
        'creado_por',
        'actualizado_por',
    ];

    protected function casts(): array
    {
        return ['fecha_intervencion' => 'date'];
    }

    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Legajo::class, 'legajo_id');
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoIntervencion::class, 'tipo_intervencion_id');
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profesional_id');
    }
}

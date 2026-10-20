<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Intervencion extends Model
{
    use Auditable;

    protected $table = 'intervenciones';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'legajo_id',
        'tipo_intervencion_id',
        'profesional_id',
        'fecha_intervencion',
        'hora_intervencion',
        'detalle',
        'firma_nombre',
        'firma_aclaracion',
        'firmada_en',
        'firma_archivo_path',
        'firma_entrevistado_nombre',
        'firma_entrevistado_aclaracion',
        'firma_entrevistado_archivo_path',
        'firma_entrevistado_en',
        'creado_por',
        'actualizado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_intervencion' => 'date',
            'firmada_en' => 'datetime',
            'firma_entrevistado_en' => 'datetime',
        ];
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

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }
}

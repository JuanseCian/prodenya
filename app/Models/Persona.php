<?php

namespace App\Models;

use App\Models\Concerns\RegistraAutor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Persona extends Model
{
    use RegistraAutor;

    protected $table = 'personas';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'apellido',
        'dni',
        'fecha_nacimiento',
        'genero',
        'direccion_calle',
        'direccion_numero',
        'direccion_piso_depto',
        'barrio_id',
        'localidad_id',
        'telefono',
        'email',
        'observaciones',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function localidad(): BelongsTo
    {
        return $this->belongsTo(Localidad::class, 'localidad_id');
    }

    public function barrio(): BelongsTo
    {
        return $this->belongsTo(Barrio::class, 'barrio_id');
    }

    public function cuds(): HasMany
    {
        return $this->hasMany(Cud::class, 'persona_id');
    }

    public function cudVigente(): HasMany
    {
        return $this->hasMany(Cud::class, 'persona_id')
            ->where('fecha_vencimiento', '>=', now());
    }

    public function legajos(): HasMany
    {
        return $this->hasMany(Legajo::class, 'persona_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'persona_id');
    }

    public function getEdadAttribute(): int
    {
        return $this->fecha_nacimiento->age;
    }
}

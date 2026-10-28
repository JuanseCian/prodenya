<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\RegistraAutor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Persona extends Model
{
    use Auditable, RegistraAutor;

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
        'latitud',
        'longitud',
        'geocodificacion_precision',
        'geocodificacion_consulta_hash',
        'geocodificado_en',
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
            'latitud' => 'float',
            'longitud' => 'float',
            'geocodificado_en' => 'datetime',
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

    public function gruposFamiliares(): BelongsToMany
    {
        return $this->belongsToMany(
            GrupoFamiliar::class,
            'grupo_familiar_miembros',
            'persona_id',
            'grupo_familiar_id',
        )->wherePivotNull('desvinculado_en');
    }

    public function vinculosFamiliares(): HasMany
    {
        return $this->hasMany(VinculoFamiliar::class, 'persona_id')
            ->whereNull('desvinculado_en');
    }

    public function getEdadAttribute(): int
    {
        return $this->fecha_nacimiento->age;
    }
}

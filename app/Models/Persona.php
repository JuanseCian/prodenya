<?php

namespace App\Models;

use App\Models\Concerns\RegistraAutor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'numero_legajo',
        'motivo_ingreso',
        'nivel_riesgo',
        'origen_derivacion',
        'profesional_id',
        'fecha_apertura',
        'estado',
        'observaciones_legajo',
        'motivo_ingreso_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'activo' => 'boolean',
            'fecha_apertura' => 'date',
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

    public function problematicas(): BelongsToMany
    {
        return $this->belongsToMany(
            Problematica::class,
            'persona_problematica',
            'persona_id',
            'problematica_id',
        );
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profesional_id');
    }

    public function motivoIngreso(): BelongsTo
    {
        return $this->belongsTo(MotivoIngreso::class, 'motivo_ingreso_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'persona_id');
    }

    public function getEdadAttribute(): int
    {
        return $this->fecha_nacimiento->age;
    }

    public static function generarNumeroLegajo(self $persona): string
    {
        $soloLetras = static fn (string $texto): string => (string) str($texto)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z]/', '');
        $base = sprintf(
            '%s-%s-%s',
            str_pad(substr($soloLetras($persona->apellido), 0, 3), 3, 'X'),
            str_pad(substr($soloLetras($persona->nombre), 0, 3), 3, 'X'),
            str_pad(substr(preg_replace('/\D/', '', (string) $persona->dni), -3), 3, '0', STR_PAD_LEFT),
        );

        $numero = $base;
        $sufijo = 1;

        while (static::where('numero_legajo', $numero)->exists()) {
            $numero = "{$base}-".++$sufijo;
        }

        return $numero;
    }
}

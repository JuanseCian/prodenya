<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Legajo extends Model
{
    protected $table = 'legajos';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

  protected $fillable = [
    'persona_id',
    'numero_legajo',
    'motivo_ingreso',
    'nivel_riesgo',
    'origen_derivacion',
    'profesional_id',
    'fecha_apertura',
    'estado',
    'observaciones',
    'creado_por',
];

    protected function casts(): array
    {
        return [
            'fecha_apertura' => 'date',
        ];
    }

    public function problematicas(): BelongsToMany
{
    return $this->belongsToMany(
        Problematica::class,
        'legajo_problematica',   // ajustalo al nombre real de tu tabla pivote
        'legajo_id',
        'problematica_id',
    );
}
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'legajo_id');
    }

    public function accesos(): HasMany
    {
        return $this->hasMany(AuditoriaAccesoLegajo::class, 'legajo_id');
    }

    /**
     * Genera el número de legajo con el formato:
     * 3 primeras letras del apellido - 3 primeras letras del nombre - últimos 3 dígitos del DNI
     * Ej: García, Juan, DNI 30111222 -> GAR-JUA-222
     *
     * Si el código ya existe (colisión), agrega un sufijo numérico incremental.
     */
    public static function generarNumero(Persona $persona): string
    {
        $apellido = static::soloLetras($persona->apellido);
        $nombre = static::soloLetras($persona->nombre);
        $dni = preg_replace('/\D/', '', $persona->dni);

        $base = sprintf(
            '%s-%s-%s',
            str_pad(substr($apellido, 0, 3), 3, 'X'),
            str_pad(substr($nombre, 0, 3), 3, 'X'),
            str_pad(substr($dni, -3), 3, '0', STR_PAD_LEFT),
        );

        $numero = $base;
        $sufijo = 1;

        while (static::where('numero_legajo', $numero)->exists()) {
            $sufijo++;
            $numero = "{$base}-{$sufijo}";
        }

        return $numero;
    }

    private static function soloLetras(string $texto): string
    {
        return (string) Str::of($texto)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z]/', '');
    }

    /**
     * Registra un acceso (vista o descarga) al legajo. Usar esto
     * en el controller cada vez que un usuario abre o descarga el legajo.
     */
    public function registrarAcceso(Usuario $usuario, string $tipoAccion): AuditoriaAccesoLegajo
    {
        return $this->accesos()->create([
            'usuario_id' => $usuario->id,
            'tipo_accion' => $tipoAccion,
            'ip_origen' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

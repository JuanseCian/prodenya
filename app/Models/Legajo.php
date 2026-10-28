<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\RegistraAutor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Legajo extends Model
{
    use Auditable, RegistraAutor;

    protected $table = 'legajos';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'persona_id',
        'numero_legajo',
        'nivel_riesgo',
        'origen_derivacion',
        'profesional_id',
        'fecha_apertura',
        'fecha_cierre',
        'estado',
        'observaciones',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_apertura' => 'date',
            'fecha_cierre' => 'date',
            'retencion_hasta' => 'date',
            'archivado_legal_en' => 'datetime',
            'eliminacion_autorizada_en' => 'datetime',
        ];
    }

    /**
     * Conserva el legajo como mínimo hasta diez años después de cumplir 18.
     */
    public function fechaMinimaRetencion(): ?Carbon
    {
        return $this->persona?->fecha_nacimiento?->copy()->addYears(28);
    }

    public function puedeEliminarse(): bool
    {
        $retencion = $this->retencion_hasta ?? $this->fechaMinimaRetencion();

        return $this->eliminacion_autorizada_en !== null
            && $retencion !== null
            && $retencion->isPast();
    }

    public function problematicas(): BelongsToMany
    {
        return $this->belongsToMany(
            Problematica::class,
            'legajo_problematica',
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
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profesional_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'legajo_id');
    }

    public function intervenciones(): HasMany
    {
        return $this->hasMany(Intervencion::class, 'legajo_id')
            ->orderByDesc('fecha_intervencion')
            ->orderByDesc('hora_intervencion')
            ->orderByDesc('id');
    }

    public function accesos(): HasMany
    {
        return $this->hasMany(AuditoriaAccesoLegajo::class, 'legajo_id');
    }

    public function derivaciones(): HasMany
    {
        return $this->hasMany(DerivacionCaso::class, 'legajo_id')->latest();
    }

    public function responsables(): HasMany
    {
        return $this->hasMany(CasoResponsable::class, 'legajo_id');
    }

    public function responsablesActivos(): HasMany
    {
        return $this->responsables()->where('activo', true);
    }

    /**
     * Crea el legajo generando un número único. Si dos legajos se crean a la
     * vez y chocan en el índice UNIQUE de numero_legajo, reintenta con el
     * siguiente sufijo libre.
     *
     * $atributos son los campos del legajo, sin persona_id ni numero_legajo.
     */
    public static function crearConNumeroUnico(Persona $persona, array $atributos): static
    {
        $intentos = 0;

        while (true) {
            try {
                return DB::transaction(fn () => static::create($atributos + [
                    'persona_id' => $persona->id,
                    'numero_legajo' => static::generarNumero($persona),
                ]));
            } catch (UniqueConstraintViolationException $e) {
                if (++$intentos >= 5) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Formato: 3 primeras letras del apellido - 3 primeras del nombre -
     * últimos 3 dígitos del DNI. Ej: García, Juan, DNI 30111222 -> GAR-JUA-222
     *
     * Si el código ya existe, agrega un sufijo numérico incremental.
     */
    public static function generarNumero(Persona $persona): string
    {
        $apellido = static::soloLetras($persona->apellido);
        $nombre = static::soloLetras($persona->nombre);
        $dni = preg_replace('/\D/', '', (string) $persona->dni);

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
     * Registra un acceso (vista o descarga) al legajo.
     */
    public function registrarAcceso(User $usuario, string $tipoAccion): AuditoriaAccesoLegajo
    {
        return $this->accesos()->create([
            'usuario_id' => $usuario->id,
            'tipo_accion' => $tipoAccion,
            'ip_origen' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 255),
        ]);
    }
}

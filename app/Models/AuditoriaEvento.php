<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaEvento extends Model
{
    private const MAX_LONGITUD_VALOR = 2000;

    protected $table = 'auditoria_eventos';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'usuario_id',
        'evento',
        'auditable_type',
        'auditable_id',
        'datos_antes',
        'datos_despues',
        'ip_origen',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'datos_antes' => 'array',
            'datos_despues' => 'array',
            'creado_en' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public static function registrar(
        string $evento,
        ?Model $modelo = null,
        ?array $antes = null,
        ?array $despues = null,
    ): self {
        return static::create([
            'usuario_id' => auth()->id(),
            'evento' => $evento,
            'auditable_type' => $modelo?->getMorphClass(),
            'auditable_id' => $modelo?->getKey(),
            'datos_antes' => self::limitarPayload($antes),
            'datos_despues' => self::limitarPayload($despues),
            'ip_origen' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 500),
        ]);
    }

    /**
     * Evita que columnas de texto libre (observaciones, detalle, body_html, etc.)
     * hagan crecer sin límite el tamaño de cada fila de auditoría.
     *
     * @param  array<string, mixed>|null  $datos
     * @return array<string, mixed>|null
     */
    private static function limitarPayload(?array $datos): ?array
    {
        if ($datos === null) {
            return null;
        }

        return array_map(
            fn ($valor) => is_string($valor) && mb_strlen($valor) > self::MAX_LONGITUD_VALOR
                ? mb_substr($valor, 0, self::MAX_LONGITUD_VALOR).'… (truncado)'
                : $valor,
            $datos,
        );
    }
}

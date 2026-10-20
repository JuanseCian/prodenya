<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaEvento extends Model
{
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
            'datos_antes' => $antes,
            'datos_despues' => $despues,
            'ip_origen' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 500),
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Legajo extends Model
{
    protected $table = 'legajos';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'persona_id',
        'numero_legajo',
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

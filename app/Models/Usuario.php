<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre_usuario',
        'email',
        'password_hash',
        'rol_id',
        'activo',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'ultimo_acceso' => 'datetime',
        ];
    }

    /**
     * Laravel busca por defecto la columna "password" para autenticar.
     * Como en nuestro esquema se llama "password_hash", se lo indicamos así.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function legajosCreados(): HasMany
    {
        return $this->hasMany(Legajo::class, 'creado_por');
    }

    public function documentosSubidos(): HasMany
    {
        return $this->hasMany(Documento::class, 'subido_por');
    }

    public function accesosLegajo(): HasMany
    {
        return $this->hasMany(AuditoriaAccesoLegajo::class, 'usuario_id');
    }

    public function descargas(): HasMany
    {
        return $this->hasMany(AuditoriaDescarga::class, 'usuario_id');
    }

    public function esAdmin(): bool
    {
        return $this->rol?->nombre === 'admin';
    }
}

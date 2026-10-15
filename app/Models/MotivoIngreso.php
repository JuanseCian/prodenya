<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MotivoIngreso extends Model
{
    protected $table = 'motivos_ingreso';

    protected $fillable = ['nombre', 'descripcion', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class, 'motivo_ingreso_id');
    }
}

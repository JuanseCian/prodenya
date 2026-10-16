<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoIntervencion extends Model
{
    protected $table = 'tipos_intervencion';

    public $timestamps = false;

    protected $fillable = ['nombre', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}

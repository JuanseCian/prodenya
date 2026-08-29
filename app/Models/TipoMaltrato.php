<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoMaltrato extends Model
{
    protected $table = 'tipos_maltrato';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function legajos(): BelongsToMany
    {
        return $this->belongsToMany(Legajo::class, 'legajo_tipo_maltrato');
    }
}

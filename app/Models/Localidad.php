<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Localidad extends Model
{
    use SoftDeletes;

    protected $table = 'localidades';
    public $timestamps = false;

    protected $fillable = ['nombre', 'provincia_id'];

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class, 'provincia_id');
    }

    public function barrios(): HasMany
    {
        return $this->hasMany(Barrio::class, 'localidad_id');
    }

    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class, 'localidad_id');
    }
}

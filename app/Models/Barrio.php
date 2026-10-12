<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barrio extends Model
{
    use SoftDeletes;

    protected $table = 'barrios';
    public $timestamps = false;

    protected $fillable = ['nombre', 'localidad_id'];

    public function localidad(): BelongsTo
    {
        return $this->belongsTo(Localidad::class, 'localidad_id');
    }

    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class, 'barrio_id');
    }
}

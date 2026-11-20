<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Problematica extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Problemática genérica que exige especificar cuál es.
     */
    public const NOMBRE_OTROS = 'Otros';

    public static function idOtros(): ?int
    {
        return static::query()->where('nombre', self::NOMBRE_OTROS)->value('id');
    }

    /**
     * Nombre para mostrar en un legajo, con la especificación de «Otros» si la hay.
     */
    public function etiquetaEnLegajo(): string
    {
        $detalle = $this->pivot?->detalle;

        return filled($detalle) ? "{$this->nombre}: {$detalle}" : $this->nombre;
    }
}

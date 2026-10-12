<?php

namespace App\Models\Concerns;

/**
 * Completa automáticamente creado_por / actualizado_por con el usuario
 * autenticado. Usar en los models cuya tabla tenga esas columnas.
 */
trait RegistraAutor
{
    protected static function bootRegistraAutor(): void
    {
        static::creating(function ($modelo) {
            if ($modelo->creado_por === null && auth()->check()) {
                $modelo->creado_por = auth()->id();
            }
        });

        static::updating(function ($modelo) {
            if (auth()->check()) {
                $modelo->actualizado_por = auth()->id();
            }
        });
    }
}

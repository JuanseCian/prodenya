<?php

namespace App\Models\Concerns;

use App\Models\AuditoriaEvento;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($modelo) {
            AuditoriaEvento::registrar('creado', $modelo, null, $modelo->getAttributes());
        });

        static::updated(function ($modelo) {
            AuditoriaEvento::registrar('editado', $modelo, $modelo->getOriginal(), $modelo->getAttributes());
        });

        static::deleted(function ($modelo) {
            AuditoriaEvento::registrar('eliminado', $modelo, $modelo->getOriginal(), null);
        });
    }
}

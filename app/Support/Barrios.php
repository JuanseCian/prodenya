<?php

namespace App\Support;

use App\Models\Barrio;
use App\Models\Localidad;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

/**
 * Barrios que se ofrecen en las actas (las actas son de San Nicolás de los Arroyos).
 */
class Barrios
{
    public const LOCALIDAD = 'San Nicolás de los Arroyos';

    /**
     * @return array<int, string>
     */
    public static function nombres(): array
    {
        $localidadId = Localidad::query()->where('nombre', self::LOCALIDAD)->value('id');

        return Barrio::query()
            ->when($localidadId, fn ($query) => $query->where('localidad_id', $localidadId))
            ->orderBy('nombre')
            ->pluck('nombre')
            ->unique()
            ->values()
            ->all();
    }

    public static function idPorNombre(?string $nombre): ?int
    {
        if (blank($nombre)) {
            return null;
        }

        $localidadId = Localidad::query()->where('nombre', self::LOCALIDAD)->value('id');

        return Barrio::query()
            ->when($localidadId, fn ($query) => $query->where('localidad_id', $localidadId))
            ->where('nombre', $nombre)
            ->value('id');
    }

    /**
     * Solo se aceptan barrios cargados en el sistema.
     */
    public static function regla(): In
    {
        return Rule::in(self::nombres());
    }
}

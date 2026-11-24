<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Firmas dibujadas en pantalla: llegan como data URL PNG.
 */
class FirmaPng
{
    public const PREFIJO = 'data:image/png;base64,';

    /**
     * Devuelve el PNG binario, o null si el dato no es una imagen PNG válida.
     */
    public static function decodificar(string $dibujo): ?string
    {
        if (! str_starts_with($dibujo, self::PREFIJO)) {
            return null;
        }

        $contenido = base64_decode(Str::after($dibujo, self::PREFIJO), true);

        if ($contenido === false || ! str_starts_with($contenido, "\x89PNG\r\n\x1a\n")) {
            return null;
        }

        $dimensiones = @getimagesizefromstring($contenido);

        return $dimensiones !== false && $dimensiones[2] === IMAGETYPE_PNG ? $contenido : null;
    }
}

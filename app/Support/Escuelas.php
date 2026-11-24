<?php

namespace App\Support;

use App\Models\Escuela;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

class Escuelas
{
    /**
     * Todas las escuelas se ofrecen en actas, incluso las inactivas, para no
     * romper la selección de registros históricos.
     *
     * @return array<int, string>
     */
    public static function nombres(): array
    {
        return Escuela::query()
            ->orderBy('nombre')
            ->pluck('nombre')
            ->all();
    }

    public static function regla(): In
    {
        return Rule::in(self::nombres());
    }
}

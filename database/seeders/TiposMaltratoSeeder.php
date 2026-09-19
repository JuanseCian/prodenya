<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposMaltratoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Datos tomados del dump prodenya.sql (tabla `tipos_maltrato`).
     * insertOrIgnore para poder correr el seeder de nuevo sin
     * romper por claves primarias duplicadas.
     */
    public function run(): void
    {
        DB::table('tipos_maltrato')->insertOrIgnore([
            ['id' => 4, 'nombre' => 'Abuso sexual'],
            ['id' => 5, 'nombre' => 'Económico / Patrimonial'],
            ['id' => 1, 'nombre' => 'Físico'],
            ['id' => 3, 'nombre' => 'Negligencia / Abandono'],
            ['id' => 6, 'nombre' => 'Otro'],
            ['id' => 2, 'nombre' => 'Psicológico / Emocional'],
        ]);
    }
}

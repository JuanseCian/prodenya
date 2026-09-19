<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposDocumentoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Datos tomados del dump prodenya.sql (tabla `tipos_documento`).
     * insertOrIgnore para poder correr el seeder de nuevo sin
     * romper por claves primarias duplicadas.
     */
    public function run(): void
    {
        DB::table('tipos_documento')->insertOrIgnore([
            ['id' => 3, 'nombre' => 'Certificado médico'],
            ['id' => 1, 'nombre' => 'CUD'],
            ['id' => 2, 'nombre' => 'DNI'],
            ['id' => 4, 'nombre' => 'Otro'],
        ]);
    }
}

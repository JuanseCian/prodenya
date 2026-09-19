<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Datos tomados del dump prodenya.sql (tabla `roles`).
     * insertOrIgnore para poder correr el seeder de nuevo sin
     * romper por claves primarias duplicadas.
     */
    public function run(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total al sistema', 'creado_en' => '2026-08-14 00:08:47'],
            ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición de personas y documentos', 'creado_en' => '2026-08-14 00:08:47'],
            ['id' => 3, 'nombre' => 'consulta', 'descripcion' => 'Solo lectura y descarga de documentos', 'creado_en' => '2026-08-14 00:08:47'],
        ]);
    }
}

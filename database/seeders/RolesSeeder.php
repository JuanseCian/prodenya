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
    [
        'id' => 1,
        'nombre' => 'admin',
        'descripcion' => 'Acceso total al sistema',
        'creado_en' => '2026-08-14 00:08:47',
    ],
    [
        'id' => 2,
        'nombre' => 'operador',
        'descripcion' => 'Carga y edición de personas y documentos',
        'creado_en' => '2026-08-14 00:08:47',
    ],
    [
        'id' => 3,
        'nombre' => 'consulta',
        'descripcion' => 'Solo lectura y descarga de documentos',
        'creado_en' => '2026-08-14 00:08:47',
    ],
    [
        'id' => 4,
        'nombre' => 'oficios',
        'descripcion' => 'Clasificación, vinculación y derivación de comunicaciones.',
        'creado_en' => '2026-10-20 00:00:00',
    ],
    [
        'id' => 5,
        'nombre' => 'coordinador',
        'descripcion' => 'Supervisión, agenda, tareas y avisos.',
        'creado_en' => '2026-10-20 00:00:00',
    ],
    [
        'id' => 6,
        'nombre' => 'profesional',
        'descripcion' => 'Intervenciones, seguimiento y firmas profesionales.',
        'creado_en' => '2026-10-20 00:00:00',
    ],
    [
        'id' => 7,
        'nombre' => 'administrativo',
        'descripcion' => 'Carga y organización de información operativa.',
        'creado_en' => '2026-10-20 00:00:00',
    ],
]);
    }
}

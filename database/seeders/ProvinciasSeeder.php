<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Datos tomados del dump prodenya.sql (tabla `provincias`).
     * insertOrIgnore para poder correr el seeder de nuevo sin
     * romper por claves primarias duplicadas.
     */
    public function run(): void
    {
        DB::table('provincias')->insertOrIgnore([
            ['id' => 1, 'nombre' => 'Buenos Aires', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 2, 'nombre' => 'Catamarca', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 3, 'nombre' => 'Chaco', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 4, 'nombre' => 'Chubut', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 5, 'nombre' => 'Córdoba', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 6, 'nombre' => 'Corrientes', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 7, 'nombre' => 'Entre Ríos', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 8, 'nombre' => 'Formosa', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 9, 'nombre' => 'Jujuy', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 10, 'nombre' => 'La Pampa', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 11, 'nombre' => 'La Rioja', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 12, 'nombre' => 'Mendoza', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 13, 'nombre' => 'Misiones', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 14, 'nombre' => 'Neuquén', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 15, 'nombre' => 'Río Negro', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 16, 'nombre' => 'Salta', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 17, 'nombre' => 'San Juan', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 18, 'nombre' => 'San Luis', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 19, 'nombre' => 'Santa Cruz', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 20, 'nombre' => 'Santa Fe', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 21, 'nombre' => 'Santiago del Estero', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 22, 'nombre' => 'Tierra del Fuego', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
            ['id' => 23, 'nombre' => 'Tucumán', 'updated_at' => null, 'updated_by' => null, 'deleted_by' => null, 'deleted_at' => null, 'created_at' => '2024-10-23 14:25:50'],
        ]);
    }
}

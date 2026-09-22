<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProblematicaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('problematica')->insert([
            [
                'id' => 1,
                'nombre' => 'Ausencia del adulto responsable',
                'created_at' => '2024-10-24 04:59:55',
                'updated_by' => null,
                'deleted_by' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'nombre' => 'Maltrato',
                'created_at' => '2024-10-24 04:59:55',
                'updated_by' => null,
                'deleted_by' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'nombre' => 'Negligencia',
                'created_at' => '2024-10-24 04:59:55',
                'updated_by' => null,
                'deleted_by' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'nombre' => 'COVID 19',
                'created_at' => '2024-10-24 04:59:55',
                'updated_by' => null,
                'deleted_by' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 6,
                'nombre' => 'Abuso sexual',
                'created_at' => '2024-10-24 04:59:55',
                'updated_by' => null,
                'deleted_by' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 7,
                'nombre' => 'Explotacion',
                'created_at' => '2024-10-24 04:59:55',
                'updated_by' => null,
                'deleted_by' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 8,
                'nombre' => 'Falta de acceso a servicios',
                'created_at' => '2024-10-24 04:59:55',
                'updated_by' => null,
                'deleted_by' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 9,
                'nombre' => 'Discriminacion/Violencia institucional',
                'created_at' => '2024-10-24 04:59:55',
                'updated_by' => null,
                'deleted_by' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 10,
                'nombre' => 'Identidad',
                'created_at' => '2024-10-24 04:59:55',
                'updated_by' => null,
                'deleted_by' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 11,
                'nombre' => 'Responsabilidad penal juvenil',
                'created_at' => '2024-10-24 04:59:55',
                'updated_by' => null,
                'deleted_by' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 12,
                'nombre' => 'Consulta juridicas',
                'created_at' => '2024-10-24 04:59:55',
                'updated_by' => null,
                'deleted_by' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 13,
                'nombre' => 'Salud',
                'created_at' => '2024-10-24 04:59:55',
                'updated_by' => null,
                'deleted_by' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 15,
                'nombre' => 'Medidas de abrigo',
                'created_at' => '2024-10-24 04:59:55',
                'updated_by' => null,
                'deleted_by' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 16,
                'nombre' => 'Otros',
                'created_at' => '2024-10-24 04:59:55',
                'updated_by' => null,
                'deleted_by' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ]);
    }
}
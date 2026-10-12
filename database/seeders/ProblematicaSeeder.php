<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProblematicaSeeder extends Seeder
{
    /**
     * Carga las problemáticas en la tabla `problematicas` (antes `problematica`).
     * Se conservan los ids originales porque los legajos existentes los referencian.
     * insertOrIgnore: se puede ejecutar más de una vez sin errores por ids duplicados.
     */
    public function run(): void
    {
        $problematicas = [
            1 => 'Ausencia del adulto responsable',
            3 => 'Maltrato',
            4 => 'Negligencia',
            5 => 'COVID 19',
            6 => 'Abuso sexual',
            7 => 'Explotacion',
            8 => 'Falta de acceso a servicios',
            9 => 'Discriminacion/Violencia institucional',
            10 => 'Identidad',
            11 => 'Responsabilidad penal juvenil',
            12 => 'Consulta juridicas',
            13 => 'Salud',
            15 => 'Medidas de abrigo',
            16 => 'Otros',
        ];

        $filas = [];

        foreach ($problematicas as $id => $nombre) {
            $filas[] = [
                'id' => $id,
                'nombre' => $nombre,
                'created_at' => '2024-10-24 04:59:55',
                'updated_by' => null,
                'deleted_by' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ];
        }

        DB::table('problematicas')->insertOrIgnore($filas);
    }
}

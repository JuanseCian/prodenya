<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposIntervencionSeeder extends Seeder
{
    /**
     * IMPORTANTE: estos tipos son un punto de partida. Reemplazalos por los
     * que usás en el sistema de género (tabla tipos_intervencion de ese proyecto).
     */
    public function run(): void
    {
        $tipos = [
            'Denuncia',
            'Oficio',
            'Acta',
            'Mail',
            'Intervención en calle',
            'Intervención domiciliaria',
            'Intervención en sede',
            'Sin intervención',
            'Entrevista',
            'Visita domiciliaria',
            'Llamado telefónico',
            'Seguimiento',
            'Acompañamiento',
            'Articulación institucional',
            'Derivación',
            'Otra',
        ];

        DB::table('tipos_intervencion')->insertOrIgnore(
            array_map(fn (string $nombre) => ['nombre' => $nombre, 'activo' => true], $tipos)
        );
    }
}

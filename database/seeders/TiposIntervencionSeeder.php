<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposIntervencionSeeder extends Seeder
{
    /**
     * Tipos que se ofrecen al registrar una intervención.
     *
     * @var array<int, string>
     */
    public const VISIBLES = [
        'Acta inicial',
        'Acta de visita domiciliaria',
        'Acta telefónica',
        'Citación',
        'Constancia escolar',
        'Recepción de oficio',
        'Respuesta de oficio',
        'Mail',
    ];

    /**
     * Tipos de registros anteriores u otros módulos: existen, pero no se ofrecen.
     *
     * @var array<int, string>
     */
    private const OCULTOS = [
        'Denuncia',
        'Oficio',
        'Acta',
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
        'Medida de abrigo',
        'Otra',
        'AUH · Nota de elevación',
        'AUH · Declaración jurada',
        'AUH · Informe de recoversion de relación',
    ];

    public function run(): void
    {
        DB::table('tipos_intervencion')->insertOrIgnore([
            ...array_map(fn (string $nombre) => ['nombre' => $nombre, 'activo' => true], self::VISIBLES),
            ...array_map(fn (string $nombre) => ['nombre' => $nombre, 'activo' => false], self::OCULTOS),
        ]);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Únicos tipos que se ofrecen al registrar una intervención. Los demás se
     * ocultan: las intervenciones existentes conservan su tipo.
     *
     * @var array<int, string>
     */
    private const VISIBLES = [
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
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('tipos_intervencion')->where('nombre', 'Recepcion de oficio')->update(['nombre' => 'Recepción de oficio']);

        DB::table('tipos_intervencion')->insertOrIgnore(
            array_map(fn (string $nombre) => ['nombre' => $nombre, 'activo' => true], self::VISIBLES),
        );

        DB::table('tipos_intervencion')->whereIn('nombre', self::VISIBLES)->update(['activo' => true]);
        DB::table('tipos_intervencion')->whereNotIn('nombre', self::VISIBLES)->update(['activo' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('tipos_intervencion')->update(['activo' => true]);
        DB::table('tipos_intervencion')->where('nombre', 'Recepción de oficio')->update(['nombre' => 'Recepcion de oficio']);
    }
};

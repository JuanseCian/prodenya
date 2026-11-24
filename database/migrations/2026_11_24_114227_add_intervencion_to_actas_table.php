<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tipos de intervención que generan un acta o planilla al registrarse.
     *
     * @var array<int, string>
     */
    private const TIPOS = [
        'Acta inicial',
        'Acta de visita domiciliaria',
        'Acta telefónica',
        'Citación',
        'Constancia escolar',
        'AUH · Nota de elevación',
        'AUH · Declaración jurada',
        'AUH · Informe de recoversion de relación',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('actas', function (Blueprint $table) {
            // Intervención del legajo que registra el acta.
            $table->unsignedInteger('intervencion_id')->nullable()->after('legajo_id');
            $table->foreign('intervencion_id')->references('id')->on('intervenciones')->nullOnDelete();
            $table->unique('intervencion_id');
        });

        DB::table('tipos_intervencion')->insertOrIgnore(
            array_map(fn (string $nombre) => ['nombre' => $nombre, 'activo' => true], self::TIPOS),
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actas', function (Blueprint $table) {
            $table->dropForeign(['intervencion_id']);
            $table->dropUnique(['intervencion_id']);
            $table->dropColumn('intervencion_id');
        });
    }
};

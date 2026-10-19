<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columnas = [
            'hora_intervencion' => fn (Blueprint $table) => $table->time('hora_intervencion')->nullable()->after('fecha_intervencion'),
            'firma_nombre' => fn (Blueprint $table) => $table->string('firma_nombre', 150)->nullable()->after('detalle'),
            'firma_aclaracion' => fn (Blueprint $table) => $table->string('firma_aclaracion', 150)->nullable()->after('firma_nombre'),
            'firmada_en' => fn (Blueprint $table) => $table->timestamp('firmada_en')->nullable()->after('firma_aclaracion'),
        ];

        foreach ($columnas as $nombre => $definicion) {
            if (! Schema::hasColumn('intervenciones', $nombre)) {
                Schema::table('intervenciones', $definicion);
            }
        }

        $tipos = [
            'Denuncia',
            'Oficio',
            'Acta',
            'Mail',
            'Intervención en calle',
            'Intervención domiciliaria',
            'Intervención en sede',
            'Sin intervención',
        ];

        foreach ($tipos as $nombre) {
            DB::table('tipos_intervencion')->insertOrIgnore([
                'nombre' => $nombre,
                'activo' => true,
            ]);
        }
    }

    public function down(): void
    {
        foreach (['firmada_en', 'firma_aclaracion', 'firma_nombre', 'hora_intervencion'] as $columna) {
            if (Schema::hasColumn('intervenciones', $columna)) {
                Schema::table('intervenciones', fn (Blueprint $table) => $table->dropColumn($columna));
            }
        }
    }
};

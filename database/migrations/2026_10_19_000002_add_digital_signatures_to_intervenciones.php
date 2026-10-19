<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columnas = [
            'firma_archivo_path' => fn (Blueprint $table) => $table->string('firma_archivo_path')->nullable()->after('firmada_en'),
            'firma_entrevistado_nombre' => fn (Blueprint $table) => $table->string('firma_entrevistado_nombre', 150)->nullable()->after('firma_archivo_path'),
            'firma_entrevistado_aclaracion' => fn (Blueprint $table) => $table->string('firma_entrevistado_aclaracion', 150)->nullable()->after('firma_entrevistado_nombre'),
            'firma_entrevistado_archivo_path' => fn (Blueprint $table) => $table->string('firma_entrevistado_archivo_path')->nullable()->after('firma_entrevistado_aclaracion'),
            'firma_entrevistado_en' => fn (Blueprint $table) => $table->timestamp('firma_entrevistado_en')->nullable()->after('firma_entrevistado_archivo_path'),
        ];

        foreach ($columnas as $nombre => $definicion) {
            if (! Schema::hasColumn('intervenciones', $nombre)) {
                Schema::table('intervenciones', $definicion);
            }
        }
    }

    public function down(): void
    {
        foreach (array_keys([
            'firma_archivo_path' => true,
            'firma_entrevistado_nombre' => true,
            'firma_entrevistado_aclaracion' => true,
            'firma_entrevistado_archivo_path' => true,
            'firma_entrevistado_en' => true,
        ]) as $columna) {
            if (Schema::hasColumn('intervenciones', $columna)) {
                Schema::table('intervenciones', fn (Blueprint $table) => $table->dropColumn($columna));
            }
        }
    }
};

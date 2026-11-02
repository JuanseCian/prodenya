<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->decimal('latitud', 10, 7)->nullable()->after('direccion_piso_depto');
            $table->decimal('longitud', 10, 7)->nullable()->after('latitud');
            $table->string('geocodificacion_precision', 40)->nullable()->after('longitud');
            $table->string('geocodificacion_consulta_hash', 64)->nullable()->after('geocodificacion_precision');
            $table->timestamp('geocodificado_en')->nullable()->after('geocodificacion_consulta_hash');
            $table->index(['latitud', 'longitud'], 'idx_persona_geolocalizacion');
        });

        Schema::table('barrios', function (Blueprint $table) {
            $table->decimal('latitud', 10, 7)->nullable()->after('nombre');
            $table->decimal('longitud', 10, 7)->nullable()->after('latitud');
            $table->string('geocodificacion_precision', 40)->nullable()->after('longitud');
            $table->string('geocodificacion_consulta_hash', 64)->nullable()->after('geocodificacion_precision');
            $table->timestamp('geocodificado_en')->nullable()->after('geocodificacion_consulta_hash');
            $table->index(['latitud', 'longitud'], 'idx_barrio_geolocalizacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropIndex('idx_persona_geolocalizacion');
            $table->dropColumn([
                'latitud',
                'longitud',
                'geocodificacion_precision',
                'geocodificacion_consulta_hash',
                'geocodificado_en',
            ]);
        });

        Schema::table('barrios', function (Blueprint $table) {
            $table->dropIndex('idx_barrio_geolocalizacion');
            $table->dropColumn([
                'latitud',
                'longitud',
                'geocodificacion_precision',
                'geocodificacion_consulta_hash',
                'geocodificado_en',
            ]);
        });
    }
};

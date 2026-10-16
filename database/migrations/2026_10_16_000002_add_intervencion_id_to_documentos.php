<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los archivos adjuntos de una intervención se guardan en `documentos`
 * (así heredan la auditoría de descargas que ya existe).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->unsignedInteger('intervencion_id')->nullable()->after('legajo_id');

            $table->index('intervencion_id', 'idx_documento_intervencion');
            $table->foreign('intervencion_id')->references('id')->on('intervenciones')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['intervencion_id']);
            $table->dropIndex('idx_documento_intervencion');
            $table->dropColumn('intervencion_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('persona_id')->nullable();
            $table->unsignedInteger('legajo_id')->nullable();
            $table->unsignedInteger('tipo_documento_id');
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 500);
            $table->unsignedBigInteger('tamanio_bytes')->nullable();
            $table->unsignedInteger('subido_por');
            $table->timestamp('fecha_subida')->useCurrent();
            $table->boolean('activo')->default(true);

            $table->foreign('persona_id')->references('id')->on('personas')
                ->onUpdate('cascade')->onDelete('set null');
            $table->foreign('legajo_id')->references('id')->on('legajos')
                ->onUpdate('cascade')->onDelete('set null');
            $table->foreign('tipo_documento_id')->references('id')->on('tipos_documento')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('subido_por')->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->index('persona_id', 'idx_documento_persona');
            $table->index('legajo_id', 'idx_documento_legajo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};

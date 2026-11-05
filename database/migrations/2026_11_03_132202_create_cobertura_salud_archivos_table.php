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
        Schema::create('cobertura_salud_archivos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cobertura_salud_id');
            $table->string('tipo', 30);
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 500);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('tamanio_bytes');
            $table->boolean('activo')->default(true);
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reemplazado_en')->nullable();
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('cobertura_salud_id')->references('id')->on('cobertura_saluds')->cascadeOnDelete();
            $table->index(['cobertura_salud_id', 'tipo', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobertura_salud_archivos');
    }
};

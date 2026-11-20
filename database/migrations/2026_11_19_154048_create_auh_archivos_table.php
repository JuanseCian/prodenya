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
        Schema::create('auh_archivos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('auh_id');
            $table->string('tipo', 50);
            $table->string('descripcion', 255)->nullable();
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 500);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('tamanio_bytes')->default(0);
            $table->boolean('activo')->default(true);
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('reemplazado_en')->nullable();

            $table->foreign('auh_id')->references('id')->on('auh')->cascadeOnDelete();
            $table->index(['auh_id', 'tipo', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auh_archivos');
    }
};

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
        Schema::create('medida_abrigo_actas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('medida_abrigo_id');
            $table->string('tipo', 30);
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 500);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('tamanio_bytes');
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('medida_abrigo_id')->references('id')->on('medida_abrigos')->cascadeOnDelete();
            $table->index(['medida_abrigo_id', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medida_abrigo_actas');
    }
};

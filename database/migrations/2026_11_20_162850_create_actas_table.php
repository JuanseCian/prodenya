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
        Schema::create('actas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo', 40);
            // Las actas rápidas pueden hacerse sin legajo y vincularse después.
            $table->unsignedInteger('legajo_id')->nullable();
            $table->unsignedInteger('persona_id')->nullable();
            $table->string('titulo', 255);
            $table->date('fecha')->nullable();
            $table->json('datos');
            $table->boolean('firmado')->default(false);
            $table->json('firmas')->nullable();
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 500);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('tamanio_bytes')->default(0);
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('vinculado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('vinculado_en')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('legajo_id')->references('id')->on('legajos')->nullOnDelete();
            $table->foreign('persona_id')->references('id')->on('personas')->nullOnDelete();
            $table->index(['legajo_id', 'creado_en']);
            $table->index('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actas');
    }
};

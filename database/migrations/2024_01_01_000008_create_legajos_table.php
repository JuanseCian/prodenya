<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legajos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('persona_id');
            $table->string('numero_legajo', 30)->unique();
            $table->date('fecha_apertura')->useCurrent();
            $table->enum('estado', ['Abierto', 'Cerrado', 'Archivado'])->default('Abierto');
            $table->text('observaciones')->nullable();
            $table->unsignedInteger('creado_por');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('persona_id')->references('id')->on('personas')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('creado_por')->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->index('persona_id', 'idx_legajo_persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legajos');
    }
};

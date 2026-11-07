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
        Schema::create('medida_abrigos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('legajo_id');
            $table->unsignedInteger('intervencion_id')->nullable();
            $table->string('ubicacion', 30);
            $table->unsignedSmallInteger('duracion_dias');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('actualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('legajo_id')->references('id')->on('legajos')->cascadeOnDelete();
            $table->foreign('intervencion_id')->references('id')->on('intervenciones')->nullOnDelete();
            $table->index(['legajo_id', 'fecha_inicio']);
            $table->index(['legajo_id', 'fecha_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medida_abrigos');
    }
};

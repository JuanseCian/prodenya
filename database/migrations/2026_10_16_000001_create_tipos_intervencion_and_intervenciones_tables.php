<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_intervencion', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 80)->unique();
            $table->boolean('activo')->default(true);
        });

        Schema::create('intervenciones', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('legajo_id');
            $table->unsignedInteger('tipo_intervencion_id');
            $table->unsignedBigInteger('profesional_id');
            $table->date('fecha_intervencion');
            $table->text('detalle');
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable()->useCurrentOnUpdate();

            // Historial de un legajo ordenado por fecha (también sirve a la FK de legajo_id)
            $table->index(['legajo_id', 'fecha_intervencion'], 'idx_intervencion_legajo_fecha');

            $table->foreign('legajo_id')->references('id')->on('legajos')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('tipo_intervencion_id')->references('id')->on('tipos_intervencion')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('profesional_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('creado_por')->references('id')->on('users')->nullOnDelete();
            $table->foreign('actualizado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervenciones');
        Schema::dropIfExists('tipos_intervencion');
    }
};

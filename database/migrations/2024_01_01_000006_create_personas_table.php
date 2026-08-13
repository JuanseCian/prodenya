<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('dni', 15)->unique();
            $table->date('fecha_nacimiento');
            $table->enum('genero', ['M', 'F', 'X', 'NO_ESPECIFICA'])->default('NO_ESPECIFICA');
            $table->string('direccion_calle', 150)->nullable();
            $table->string('direccion_numero', 20)->nullable();
            $table->string('direccion_piso_depto', 20)->nullable();
            $table->unsignedInteger('barrio_id')->nullable();
            $table->unsignedInteger('localidad_id');
            $table->string('telefono', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('barrio_id')->references('id')->on('barrios')
                ->onUpdate('cascade')->onDelete('set null');
            $table->foreign('localidad_id')->references('id')->on('localidades')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->index('dni', 'idx_persona_dni');
            $table->index(['apellido', 'nombre'], 'idx_persona_apellido_nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};

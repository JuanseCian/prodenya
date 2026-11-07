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
        Schema::create('auh', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('legajo_id');
            $table->unsignedInteger('titular_persona_id')->nullable();
            $table->string('estado', 20)->default('activo');
            $table->text('motivo_suspension')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->decimal('monto', 10, 2)->nullable();
            $table->string('numero_asignacion', 50)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);

            // Sección: foto de la constancia/certificación (JPG) de la AUH.
            $table->string('foto_nombre_archivo', 255)->nullable();
            $table->string('foto_ruta_archivo', 500)->nullable();
            $table->string('foto_mime_type', 120)->nullable();
            $table->unsignedBigInteger('foto_tamanio_bytes')->nullable();
            $table->foreignId('foto_subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('foto_subida_en')->nullable();

            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('actualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('legajo_id')->references('id')->on('legajos')->cascadeOnDelete();
            $table->foreign('titular_persona_id')->references('id')->on('personas')->nullOnDelete();

            $table->index(['legajo_id', 'activo']);
            $table->index('titular_persona_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auh');
    }
};

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
        Schema::create('cobertura_saluds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('persona_id');
            $table->string('tipo', 40);
            $table->string('origen', 40);
            $table->string('programa', 190)->nullable();
            $table->string('entidad', 190)->nullable();
            $table->string('plan', 190)->nullable();
            $table->string('numero_afiliado', 100)->nullable();
            $table->boolean('persona_es_titular')->default(true);
            $table->string('titular_nombre', 150)->nullable();
            $table->string('titular_dni', 30)->nullable();
            $table->string('titular_cuil', 20)->nullable();
            $table->string('parentesco_titular', 60)->nullable();
            $table->date('fecha_desde');
            $table->date('fecha_hasta')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('actualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reemplazado_en')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('persona_id')->references('id')->on('personas')->cascadeOnDelete();
            $table->index(['persona_id', 'activo']);
            $table->index(['persona_id', 'fecha_desde']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobertura_saluds');
    }
};

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
        Schema::create('puntaje_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->integer('puntos');
            $table->string('evento', 80);
            $table->string('clave', 191)->unique();
            $table->string('descripcion', 255);
            $table->nullableMorphs('origen');
            $table->json('metadata')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->index(['usuario_id', 'creado_en']);
            $table->index(['evento', 'creado_en']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntaje_movimientos');
    }
};

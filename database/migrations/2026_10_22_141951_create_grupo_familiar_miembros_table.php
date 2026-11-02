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
        Schema::create('grupo_familiar_miembros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_familiar_id')->constrained('grupos_familiares')->cascadeOnDelete();
            $table->unsignedInteger('persona_id');
            $table->foreign('persona_id')->references('id')->on('personas')->restrictOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('desvinculado_en')->nullable();
            $table->foreignId('desvinculado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['grupo_familiar_id', 'desvinculado_en']);
            $table->index(['persona_id', 'desvinculado_en']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_familiar_miembros');
    }
};

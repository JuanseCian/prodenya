<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cud', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('persona_id');
            $table->string('numero_cud', 50)->unique();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->text('observaciones')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('persona_id')->references('id')->on('personas')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->index('persona_id', 'idx_cud_persona');
            $table->index('fecha_vencimiento', 'idx_cud_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cud');
    }
};

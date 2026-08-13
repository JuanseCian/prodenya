<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_accesos_legajo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('legajo_id');
            $table->unsignedInteger('usuario_id');
            $table->enum('tipo_accion', ['Visualizacion', 'Descarga']);
            $table->timestamp('fecha_acceso')->useCurrent();
            $table->string('ip_origen', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->foreign('legajo_id')->references('id')->on('legajos')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->index('legajo_id', 'idx_auditlegajo_legajo');
            $table->index('usuario_id', 'idx_auditlegajo_usuario');
            $table->index('fecha_acceso', 'idx_auditlegajo_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_accesos_legajo');
    }
};

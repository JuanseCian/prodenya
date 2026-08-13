<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_descargas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('documento_id');
            $table->unsignedInteger('usuario_id');
            $table->timestamp('fecha_descarga')->useCurrent();
            $table->string('ip_origen', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->foreign('documento_id')->references('id')->on('documentos')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->index('documento_id', 'idx_auditoria_documento');
            $table->index('usuario_id', 'idx_auditoria_usuario');
            $table->index('fecha_descarga', 'idx_auditoria_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_descargas');
    }
};

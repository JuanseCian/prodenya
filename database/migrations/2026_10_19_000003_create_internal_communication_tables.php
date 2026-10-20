<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversaciones', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20)->default('directa');
            $table->string('titulo')->nullable();
            $table->foreignId('creado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamp('ultimo_mensaje_en')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('conversacion_usuario', function (Blueprint $table) {
            $table->foreignId('conversacion_id')->constrained('conversaciones')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('ultimo_leido_en')->nullable();
            $table->timestamps();
            $table->primary(['conversacion_id', 'usuario_id']);
        });

        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversacion_id')->constrained('conversaciones')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->text('cuerpo')->nullable();
            $table->timestamps();
            $table->index(['conversacion_id', 'created_at']);
        });

        Schema::create('mensaje_recursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mensaje_id')->constrained('mensajes')->cascadeOnDelete();
            $table->string('tipo_recurso', 30);
            $table->unsignedBigInteger('recurso_id');
            $table->timestamps();
            $table->unique(['mensaje_id', 'tipo_recurso', 'recurso_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensaje_recursos');
        Schema::dropIfExists('mensajes');
        Schema::dropIfExists('conversacion_usuario');
        Schema::dropIfExists('conversaciones');
    }
};

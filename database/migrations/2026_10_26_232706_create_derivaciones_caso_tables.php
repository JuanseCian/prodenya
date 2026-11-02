<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('derivaciones_caso')) {
            Schema::create('derivaciones_caso', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('legajo_id');
                $table->foreignId('solicitado_por')->constrained('users')->cascadeOnDelete();
                $table->foreignId('destinatario_id')->constrained('users')->cascadeOnDelete();
                $table->string('modo', 20);
                $table->string('estado', 20)->default('pendiente');
                $table->string('motivo', 120);
                $table->text('detalle')->nullable();
                $table->json('responsables_reemplazados')->nullable();
                $table->uuid('grupo_derivacion_id')->nullable()->index();
                $table->timestamp('fecha_limite')->nullable();
                $table->timestamp('aceptada_en')->nullable();
                $table->timestamp('rechazada_en')->nullable();
                $table->text('motivo_rechazo')->nullable();
                $table->timestamp('cancelada_en')->nullable();
                $table->foreignId('mail_message_id')->nullable()->constrained('mail_messages')->nullOnDelete();
                $table->timestamps();

                $table->foreign('legajo_id')->references('id')->on('legajos')->cascadeOnDelete();
                $table->index(['legajo_id', 'created_at']);
                $table->index(['solicitado_por', 'created_at']);
                $table->index(['destinatario_id', 'estado']);
            });
        } elseif (! Schema::hasColumn('derivaciones_caso', 'mail_message_id')) {
            Schema::table('derivaciones_caso', function (Blueprint $table) {
                $table->foreignId('mail_message_id')->nullable()->constrained('mail_messages')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('caso_responsables')) {
            Schema::create('caso_responsables', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('legajo_id');
                $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('asignado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('retirado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('es_principal')->default(false);
                $table->boolean('activo')->default(true);
                $table->timestamp('asignado_en')->useCurrent();
                $table->timestamp('retirado_en')->nullable();
                $table->timestamps();

                $table->foreign('legajo_id')->references('id')->on('legajos')->cascadeOnDelete();
                $table->index(['legajo_id', 'activo']);
                $table->index(['usuario_id', 'activo']);
            });
        }

        if (! Schema::hasTable('derivacion_caso_conversacion')) {
            Schema::create('derivacion_caso_conversacion', function (Blueprint $table) {
                $table->foreignId('derivacion_caso_id')->constrained('derivaciones_caso')->cascadeOnDelete();
                $table->foreignId('conversacion_id')->constrained('conversaciones')->cascadeOnDelete();
                $table->timestamps();
                $table->primary(['derivacion_caso_id', 'conversacion_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('derivacion_caso_conversacion');
        Schema::dropIfExists('caso_responsables');
        Schema::dropIfExists('derivaciones_caso');
    }
};

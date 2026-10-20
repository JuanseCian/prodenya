<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Existing installations get the roles during this migration. Fresh test
        // databases and fresh installs receive them through RolesSeeder, keeping
        // the historic role IDs deterministic for fixtures.
        if (DB::table('roles')->exists()) {
            foreach ([
                ['nombre' => 'oficios', 'descripcion' => 'Clasificación, vinculación y derivación de comunicaciones.'],
                ['nombre' => 'coordinador', 'descripcion' => 'Supervisión, agenda, tareas y avisos.'],
                ['nombre' => 'profesional', 'descripcion' => 'Intervenciones, seguimiento y firmas profesionales.'],
                ['nombre' => 'administrativo', 'descripcion' => 'Carga y organización de información operativa.'],
            ] as $rol) {
                DB::table('roles')->insertOrIgnore($rol + ['creado_en' => now()]);
            }
        }

        if (! Schema::hasColumn('users', 'profesion')) {
            Schema::table('users', fn (Blueprint $table) => $table->string('profesion', 100)->nullable()->after('name'));
        }

        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 40)->default('derivacion');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('prioridad', 20)->default('normal');
            $table->string('estado', 30)->default('pendiente');
            $table->date('fecha_limite')->nullable()->index();
            $table->foreignId('asignado_a')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('creado_por')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('legajo_id')->nullable();
            $table->unsignedInteger('intervencion_id')->nullable();
            $table->foreignId('mail_message_id')->nullable()->constrained('mail_messages')->nullOnDelete();
            $table->timestamp('completada_en')->nullable();
            $table->timestamps();
            $table->index(['asignado_a', 'estado']);
            $table->foreign('legajo_id')->references('id')->on('legajos')->nullOnDelete();
            $table->foreign('intervencion_id')->references('id')->on('intervenciones')->nullOnDelete();
        });

        Schema::create('tarea_recursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_id')->constrained('tareas')->cascadeOnDelete();
            $table->string('tipo_recurso', 30);
            $table->unsignedBigInteger('recurso_id');
            $table->timestamps();
            $table->unique(['tarea_id', 'tipo_recurso', 'recurso_id']);
        });

        Schema::create('agenda_eventos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('tipo', 40)->default('reunion');
            $table->dateTime('fecha_inicio')->index();
            $table->dateTime('fecha_fin')->nullable();
            $table->string('lugar')->nullable();
            $table->string('enlace')->nullable();
            $table->json('participantes_externos')->nullable();
            $table->text('notas')->nullable();
            $table->string('estado', 20)->default('programado');
            $table->foreignId('creado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('agenda_evento_legajo', function (Blueprint $table) {
            $table->foreignId('agenda_evento_id')->constrained('agenda_eventos')->cascadeOnDelete();
            $table->unsignedInteger('legajo_id');
            $table->primary(['agenda_evento_id', 'legajo_id']);
            $table->foreign('legajo_id')->references('id')->on('legajos')->cascadeOnDelete();
        });

        Schema::create('agenda_evento_usuario', function (Blueprint $table) {
            $table->foreignId('agenda_evento_id')->constrained('agenda_eventos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['agenda_evento_id', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_evento_usuario');
        Schema::dropIfExists('agenda_evento_legajo');
        Schema::dropIfExists('agenda_eventos');
        Schema::dropIfExists('tarea_recursos');
        Schema::dropIfExists('tareas');

        if (Schema::hasColumn('users', 'profesion')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('profesion'));
        }
    }
};

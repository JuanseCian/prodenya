<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - personas.dni admite NULL (casos sin DNI). El índice único sigue valiendo:
 *   MySQL permite varios NULL en una columna única.
 * - Se elimina un índice redundante y se ajustan los índices a las consultas reales
 *   (listados, "legajos abiertos de una persona", auditoría).
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- personas ---
        DB::statement('ALTER TABLE `personas` MODIFY `dni` VARCHAR(15) NULL');

        Schema::table('personas', function (Blueprint $table) {
            $table->index(['activo', 'apellido', 'nombre'], 'idx_persona_activo_apellido_nombre');
        });

        Schema::table('personas', function (Blueprint $table) {
            // idx_persona_dni duplicaba a personas_dni_unique
            $table->dropIndex('idx_persona_dni');
            // lo cubre el índice nuevo (activo, apellido, nombre)
            $table->dropIndex('idx_persona_apellido_nombre');
        });

        // --- legajos ---
        // El índice nuevo se crea antes de borrar el viejo: la clave foránea
        // legajos_persona_id_foreign necesita siempre un índice que empiece por persona_id.
        Schema::table('legajos', function (Blueprint $table) {
            $table->index(['persona_id', 'estado'], 'idx_legajo_persona_estado');
            $table->index(['estado', 'creado_en'], 'idx_legajo_estado_creado');
        });

        Schema::table('legajos', fn (Blueprint $table) => $table->dropIndex('idx_legajo_persona'));

        // --- auditoría (las tablas que más crecen) ---
        Schema::table('auditoria_accesos_legajo', function (Blueprint $table) {
            $table->index(['legajo_id', 'fecha_acceso'], 'idx_auditlegajo_legajo_fecha');
        });
        Schema::table('auditoria_accesos_legajo', fn (Blueprint $table) => $table->dropIndex('idx_auditlegajo_legajo'));

        Schema::table('auditoria_descargas', function (Blueprint $table) {
            $table->index(['documento_id', 'fecha_descarga'], 'idx_auditoria_documento_fecha');
        });
        Schema::table('auditoria_descargas', fn (Blueprint $table) => $table->dropIndex('idx_auditoria_documento'));
    }

    public function down(): void
    {
        Schema::table('auditoria_descargas', fn (Blueprint $table) => $table->index('documento_id', 'idx_auditoria_documento'));
        Schema::table('auditoria_descargas', fn (Blueprint $table) => $table->dropIndex('idx_auditoria_documento_fecha'));

        Schema::table('auditoria_accesos_legajo', fn (Blueprint $table) => $table->index('legajo_id', 'idx_auditlegajo_legajo'));
        Schema::table('auditoria_accesos_legajo', fn (Blueprint $table) => $table->dropIndex('idx_auditlegajo_legajo_fecha'));

        Schema::table('legajos', fn (Blueprint $table) => $table->index('persona_id', 'idx_legajo_persona'));
        Schema::table('legajos', function (Blueprint $table) {
            $table->dropIndex('idx_legajo_persona_estado');
            $table->dropIndex('idx_legajo_estado_creado');
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->index('dni', 'idx_persona_dni');
            $table->index(['apellido', 'nombre'], 'idx_persona_apellido_nombre');
        });
        Schema::table('personas', fn (Blueprint $table) => $table->dropIndex('idx_persona_activo_apellido_nombre'));

        if (DB::table('personas')->whereNull('dni')->exists()) {
            throw new RuntimeException(
                'Hay personas sin DNI: completalo antes de revertir esta migración.'
            );
        }

        DB::statement('ALTER TABLE `personas` MODIFY `dni` VARCHAR(15) NOT NULL');
    }
};

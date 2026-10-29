<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `legajos` vuelve a ser la única fuente de verdad de un legajo. La migración
 * anterior (reconcile_legajos_from_personas) ya garantiza que todo lo que
 * hubiera quedado solo en `personas` o en `persona_problematica` esté
 * reflejado en `legajos` / `legajo_problematica`.
 */
return new class extends Migration
{
    private const COLUMNAS = [
        'numero_legajo',
        'motivo_ingreso',
        'motivo_ingreso_id',
        'nivel_riesgo',
        'origen_derivacion',
        'profesional_id',
        'fecha_apertura',
        'fecha_cierre',
        'estado',
        'observaciones_legajo',
    ];

    public function up(): void
    {
        // SQLite emula el DROP COLUMN copiando la tabla a una temporal y
        // renombrándola; eso falla mientras `vista_personas_completa`
        // siga apuntando a `personas`, así que la recreamos después.
        $esSqlite = DB::getDriverName() === 'sqlite';

        if ($esSqlite) {
            DB::statement('DROP VIEW IF EXISTS `vista_personas_completa`');
        }

        if (Schema::hasColumn('personas', 'motivo_ingreso_id')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->dropForeign(['motivo_ingreso_id']);
            });
        }

        $presentes = array_filter(self::COLUMNAS, fn (string $columna) => Schema::hasColumn('personas', $columna));

        if (in_array('numero_legajo', $presentes, true) && Schema::hasIndex('personas', 'personas_numero_legajo_unique')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->dropUnique('personas_numero_legajo_unique');
            });
        }

        if (in_array('fecha_cierre', $presentes, true) && Schema::hasIndex('personas', 'idx_persona_fecha_cierre')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->dropIndex('idx_persona_fecha_cierre');
            });
        }

        if ($presentes !== []) {
            Schema::table('personas', function (Blueprint $table) use ($presentes) {
                $table->dropColumn($presentes);
            });
        }

        Schema::dropIfExists('persona_problematica');

        if ($esSqlite) {
            DB::statement(<<<'SQL'
                CREATE VIEW `vista_personas_completa` AS
                SELECT
                    p.id,
                    p.nombre,
                    p.apellido,
                    p.dni,
                    p.activo,
                    CAST((julianday('now') - julianday(p.fecha_nacimiento)) / 365.2425 AS INTEGER) AS edad,
                    b.nombre AS barrio,
                    l.nombre AS localidad,
                    pr.nombre AS provincia,
                    c.numero_cud,
                    c.fecha_vencimiento AS cud_vencimiento,
                    CASE
                        WHEN c.id IS NULL THEN 'Sin CUD'
                        WHEN c.fecha_vencimiento >= date('now') THEN 'Vigente'
                        ELSE 'Vencido'
                    END AS estado_cud
                FROM personas p
                LEFT JOIN barrios b ON p.barrio_id = b.id
                JOIN localidades l ON p.localidad_id = l.id
                JOIN provincias pr ON l.provincia_id = pr.id
                LEFT JOIN cud c ON c.id = (
                    SELECT c2.id
                    FROM cud c2
                    WHERE c2.persona_id = p.id
                    ORDER BY c2.fecha_vencimiento DESC, c2.id DESC
                    LIMIT 1
                )
                SQL);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('persona_problematica')) {
            Schema::create('persona_problematica', function (Blueprint $table) {
                $table->unsignedInteger('persona_id');
                $table->unsignedInteger('problematica_id');

                $table->primary(['persona_id', 'problematica_id']);
                $table->foreign('persona_id')->references('id')->on('personas')->cascadeOnDelete();
                $table->foreign('problematica_id')->references('id')->on('problematicas')->restrictOnDelete();
            });
        }

        if (! Schema::hasColumn('personas', 'numero_legajo')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->string('numero_legajo', 40)->nullable();
                $table->text('motivo_ingreso')->nullable();
                $table->string('nivel_riesgo', 20)->nullable();
                $table->string('origen_derivacion', 255)->nullable();
                $table->unsignedBigInteger('profesional_id')->nullable();
                $table->date('fecha_apertura')->nullable();
                $table->date('fecha_cierre')->nullable();
                $table->string('estado', 20)->nullable();
                $table->text('observaciones_legajo')->nullable();
                $table->foreignId('motivo_ingreso_id')->nullable()->constrained('motivos_ingreso')->nullOnDelete();
                $table->unique('numero_legajo', 'personas_numero_legajo_unique');
            });
        }
    }
};

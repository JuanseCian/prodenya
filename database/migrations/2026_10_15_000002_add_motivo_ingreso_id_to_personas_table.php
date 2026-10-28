<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $recrearVista = DB::getDriverName() === 'sqlite';

        if ($recrearVista) {
            DB::statement('DROP VIEW IF EXISTS `vista_personas_completa`');
        }

        if (! Schema::hasColumn('personas', 'motivo_ingreso_id')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->foreignId('motivo_ingreso_id')
                    ->nullable()
                    ->after('motivo_ingreso')
                    ->constrained('motivos_ingreso')
                    ->nullOnDelete();
            });
        }

        if ($recrearVista) {
            DB::statement($this->vistaSql());
        }
    }

    public function down(): void
    {
        $recrearVista = DB::getDriverName() === 'sqlite';

        if ($recrearVista) {
            DB::statement('DROP VIEW IF EXISTS `vista_personas_completa`');
        }

        if (Schema::hasColumn('personas', 'motivo_ingreso_id')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->dropForeign(['motivo_ingreso_id']);
                $table->dropColumn('motivo_ingreso_id');
            });
        }

        if ($recrearVista) {
            DB::statement($this->vistaSql());
        }
    }

    private function vistaSql(): string
    {
        return <<<'SQL'
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
        SQL;
    }
};

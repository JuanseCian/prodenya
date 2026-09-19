<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        $edad = $driver === 'sqlite'
            ? "CAST((julianday('now') - julianday(p.fecha_nacimiento)) / 365.2425 AS INTEGER)"
            : 'TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE())';
        $hoy = $driver === 'sqlite' ? "date('now')" : 'CURDATE()';

        DB::statement('DROP VIEW IF EXISTS vista_personas_completa');

        DB::statement("
            CREATE VIEW vista_personas_completa AS
            SELECT
                p.id,
                p.nombre,
                p.apellido,
                p.dni,
                {$edad} AS edad,
                b.nombre AS barrio,
                l.nombre AS localidad,
                pr.nombre AS provincia,
                c.numero_cud,
                c.fecha_vencimiento AS cud_vencimiento,
                CASE WHEN c.fecha_vencimiento >= {$hoy} THEN 'Vigente' ELSE 'Vencido' END AS estado_cud
            FROM personas p
            LEFT JOIN barrios b ON p.barrio_id = b.id
            JOIN localidades l ON p.localidad_id = l.id
            JOIN provincias pr ON l.provincia_id = pr.id
            LEFT JOIN cud c ON c.persona_id = p.id
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vista_personas_completa');
    }
};

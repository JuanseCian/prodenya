<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige vista_personas_completa:
 *  - Una persona sin CUD ya no figura como "Vencido": ahora dice "Sin CUD".
 *  - Si hay varios CUD, se toma solo el más reciente (antes la persona salía repetida).
 *  - Pasa a SQL SECURITY INVOKER: ya no depende del usuario root@localhost.
 *  - Suma la columna `activo`.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS `vista_personas_completa`');

        DB::statement(<<<'SQL'
            CREATE SQL SECURITY INVOKER VIEW `vista_personas_completa` AS
            SELECT
                p.id,
                p.nombre,
                p.apellido,
                p.dni,
                p.activo,
                TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) AS edad,
                b.nombre AS barrio,
                l.nombre AS localidad,
                pr.nombre AS provincia,
                c.numero_cud,
                c.fecha_vencimiento AS cud_vencimiento,
                CASE
                    WHEN c.id IS NULL THEN 'Sin CUD'
                    WHEN c.fecha_vencimiento >= CURDATE() THEN 'Vigente'
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

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS `vista_personas_completa`');

        DB::statement(<<<'SQL'
            CREATE SQL SECURITY INVOKER VIEW `vista_personas_completa` AS
            SELECT
                p.id, p.nombre, p.apellido, p.dni,
                TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) AS edad,
                b.nombre AS barrio, l.nombre AS localidad, pr.nombre AS provincia,
                c.numero_cud, c.fecha_vencimiento AS cud_vencimiento,
                CASE WHEN c.fecha_vencimiento >= CURDATE() THEN 'Vigente' ELSE 'Vencido' END AS estado_cud
            FROM personas p
            LEFT JOIN barrios b ON p.barrio_id = b.id
            JOIN localidades l ON p.localidad_id = l.id
            JOIN provincias pr ON l.provincia_id = pr.id
            LEFT JOIN cud c ON c.persona_id = p.id
        SQL);
    }
};

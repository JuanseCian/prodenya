<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE VIEW vista_personas_completa AS
            SELECT
                p.id,
                p.nombre,
                p.apellido,
                p.dni,
                TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) AS edad,
                b.nombre AS barrio,
                l.nombre AS localidad,
                pr.nombre AS provincia,
                c.numero_cud,
                c.fecha_vencimiento AS cud_vencimiento,
                CASE WHEN c.fecha_vencimiento >= CURDATE() THEN 'Vigente' ELSE 'Vencido' END AS estado_cud
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

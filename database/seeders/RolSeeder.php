<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        Rol::insertOrIgnore([
            ['nombre' => 'admin', 'descripcion' => 'Acceso total al sistema'],
            ['nombre' => 'operador', 'descripcion' => 'Carga y edición de personas y documentos'],
            ['nombre' => 'consulta', 'descripcion' => 'Solo lectura y descarga de documentos'],
            ['nombre' => 'oficios', 'descripcion' => 'Clasificación, vinculación y derivación de comunicaciones.'],
            ['nombre' => 'coordinador', 'descripcion' => 'Supervisión, agenda, tareas y avisos.'],
            ['nombre' => 'profesional', 'descripcion' => 'Intervenciones, seguimiento y firmas profesionales.'],
            ['nombre' => 'administrativo', 'descripcion' => 'Carga y organización de información operativa.'],
        ]);
    }
}

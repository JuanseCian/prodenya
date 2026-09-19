<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsuariosSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Datos tomados del dump prodenya.sql (tabla `usuarios`).
     * insertOrIgnore para poder correr el seeder de nuevo sin
     * romper por claves primarias duplicadas.
     */
    public function run(): void
    {
        DB::table('usuarios')->insertOrIgnore([
            ['id' => 1, 'nombre_usuario' => 'admin', 'email' => 'admin@prodenya.local', 'password_hash' => '$2y$12$cpXa2KkyxoW5tBFYOwwmAuxo1t8A1fKy18CXd.UEUYjWNTZr8n1yO', 'rol_id' => 1, 'activo' => 1, 'ultimo_acceso' => null, 'remember_token' => 'FDuD2WrfUnjLGxqPNpGwrfyfIdFZ69pRV99lz3EZOKKTh3QjWKyFgVpT3l77', 'creado_en' => '2026-08-14 03:08:48', 'actualizado_en' => '2026-08-31 20:44:44'],
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UbicacionSeeder::class,
            RolSeeder::class,
            UserSeeder::class,
            UsuarioSeeder::class,
            RolesSeeder::class,
            ProvinciasSeeder::class,
            LocalidadesSeeder::class,
            BarriosSeeder::class,
            TiposDocumentoSeeder::class,
            ProblematicaSeeder::class,
        ]);
    }
}

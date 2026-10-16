<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * El orden importa por las claves foráneas:
     *  roles -> users (users.rol_id)
     *  provincias -> localidades -> barrios
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            UserSeeder::class,
            ProvinciasSeeder::class,
            LocalidadesSeeder::class,
            BarriosSeeder::class,
            TiposDocumentoSeeder::class,
            ProblematicaSeeder::class,
            TiposIntervencionSeeder::class,
        ]);
    }
}

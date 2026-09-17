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
            TipoDocumentoSeeder::class,
            TipoMaltratoSeeder::class,
            UserSeeder::class,
            UsuarioSeeder::class,
        ]);
    }
}

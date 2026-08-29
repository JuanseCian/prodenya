<?php

namespace Database\Seeders;

use App\Models\TipoMaltrato;
use Illuminate\Database\Seeder;

class TipoMaltratoSeeder extends Seeder
{
    public function run(): void
    {
        TipoMaltrato::insertOrIgnore([
            ['nombre' => 'Físico'],
            ['nombre' => 'Psicológico / Emocional'],
            ['nombre' => 'Negligencia / Abandono'],
            ['nombre' => 'Abuso sexual'],
            ['nombre' => 'Económico / Patrimonial'],
            ['nombre' => 'Otro'],
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\TipoDocumento;
use Illuminate\Database\Seeder;

class TipoDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        TipoDocumento::insertOrIgnore([
            ['nombre' => 'CUD'],
            ['nombre' => 'DNI'],
            ['nombre' => 'Certificado médico'],
            ['nombre' => 'Otro'],
        ]);
    }
}

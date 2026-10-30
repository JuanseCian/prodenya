<?php

use App\Models\Legajo;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
    ]);
});

function crearPersonaParaWord(string $nombre, string $apellido, string $dni, string $fechaNacimiento, string $genero): Persona
{
    $provincia = DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $localidad = DB::table('localidades')->insertGetId([
        'nombre' => 'San Nicolás de los Arroyos',
        'provincia_id' => $provincia,
        'codigo_postal' => 2900,
    ]);

    return Persona::create([
        'nombre' => $nombre,
        'apellido' => $apellido,
        'dni' => $dni,
        'fecha_nacimiento' => $fechaNacimiento,
        'genero' => $genero,
        'localidad_id' => $localidad,
        'activo' => true,
    ]);
}

test('an authorized user can preview a legajo before downloading its Word document', function () {
    $usuario = User::factory()->create(['rol_id' => 1]);
    $persona = crearPersonaParaWord('Ana', 'Legajo', '30111222', '2010-01-01', 'F');
    $legajo = Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'WORD-001',
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Institución',
        'fecha_apertura' => today(),
        'estado' => 'Abierto',
        'creado_por' => $usuario->id,
    ]);

    $this->actingAs($usuario)
        ->get(route('legajos.previsualizar-word', $legajo))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('legajos/word-preview')
            ->where('legajo.numero_legajo', 'WORD-001')
            ->where('legajo.persona.nombre', 'Legajo, Ana')
            ->where('downloadUrl', route('legajos.exportar-word', $legajo))
        );
});

test('an authorized user can download the Word document from the preview flow', function () {
    $usuario = User::factory()->create(['rol_id' => 1]);
    $persona = crearPersonaParaWord('Bruno', 'Documento', '30111333', '2011-02-02', 'M');
    $legajo = Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'WORD-002',
        'nivel_riesgo' => 'Medio',
        'origen_derivacion' => 'Juzgado',
        'fecha_apertura' => today(),
        'estado' => 'Abierto',
        'creado_por' => $usuario->id,
    ]);

    $this->actingAs($usuario)
        ->get(route('legajos.exportar-word', $legajo))
        ->assertDownload('legajo-WORD-002.docx');
});

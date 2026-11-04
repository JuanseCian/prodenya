<?php

use App\Models\Legajo;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
    ]);
});

test('an authorized user can see digital files generated from the case files', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaFicha($admin);

    $this->actingAs($admin)
        ->get(route('fichas.index'))
        ->assertInertia(fn ($page) => $page
            ->where('fichas.data.0.id', $legajo->id)
            ->where('fichas.data.0.numero_legajo', $legajo->numero_legajo)
            ->where('fichas.data.0.persona.nombre_completo', 'Ficha, Ana')
            ->where('fichas.data.0.persona.dni', '30111222')
            ->where('fichas.data.0.problematicas.0', 'Violencia familiar')
        );
});

test('digital files can be searched by case number and restricted sections stay masked', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $profesional = User::factory()->create([
        'rol_id' => 5,
        'secciones_legajo_visibles' => ['intervenciones'],
    ]);
    $legajo = crearLegajoParaFicha($admin);

    $this->actingAs($profesional)
        ->get(route('fichas.index', ['buscar' => $legajo->numero_legajo]))
        ->assertInertia(fn ($page) => $page
            ->where('buscar', $legajo->numero_legajo)
            ->where('fichas.data.0.id', $legajo->id)
            ->where('fichas.data.0.persona', null)
            ->where('fichas.data.0.nivel_riesgo', null)
            ->where('fichas.data.0.problematicas', [])
        );
});

test('a legajo can open its own digital file', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaFicha($admin);

    $this->actingAs($admin)
        ->get(route('fichas.index', ['legajo' => $legajo->id]))
        ->assertInertia(fn ($page) => $page
            ->where('fichas.total', 1)
            ->where('fichas.data.0.id', $legajo->id)
        );
});

function crearLegajoParaFicha(User $creador): Legajo
{
    $provincia = DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $localidad = DB::table('localidades')->insertGetId([
        'nombre' => 'San Nicolás',
        'provincia_id' => $provincia,
        'codigo_postal' => 2900,
    ]);
    $problematica = DB::table('problematicas')->insertGetId([
        'nombre' => 'Violencia familiar',
        'created_at' => now(),
    ]);
    $persona = Persona::create([
        'nombre' => 'Ana',
        'apellido' => 'Ficha',
        'dni' => '30111222',
        'fecha_nacimiento' => '2010-01-01',
        'genero' => 'F',
        'localidad_id' => $localidad,
        'activo' => true,
    ]);

    $legajo = Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'FIC-'.$creador->id.'-'.uniqid(),
        'fecha_apertura' => today(),
        'nivel_riesgo' => 'Alto',
        'origen_derivacion' => 'Institución',
        'estado' => 'Abierto',
        'observaciones' => 'Observación de ficha',
        'creado_por' => $creador->id,
    ]);

    $legajo->problematicas()->attach($problematica);

    return $legajo;
}

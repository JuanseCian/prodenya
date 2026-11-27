<?php

use App\Models\Legajo;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
        ['id' => 3, 'nombre' => 'consulta', 'descripcion' => 'Solo lectura', 'creado_en' => now()],
    ]);
});

function crearPersonaParaBuscador(array $atributos = []): Persona
{
    $provincia = DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires '.uniqid()]);
    $localidad = DB::table('localidades')->insertGetId(['nombre' => 'San Nicolás', 'provincia_id' => $provincia, 'codigo_postal' => 2900]);

    return Persona::create([
        'nombre' => 'Juan',
        'apellido' => 'Pérez',
        'dni' => '40123456',
        'fecha_nacimiento' => '2014-01-01',
        'genero' => 'M',
        'localidad_id' => $localidad,
        'activo' => true,
        ...$atributos,
    ]);
}

test('the home search finds people by dni or name and links their legajo', function () {
    $consulta = User::factory()->create(['rol_id' => 3]);
    $persona = crearPersonaParaBuscador();
    $legajo = Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'PER-JUA-456',
        'fecha_apertura' => '2026-08-10',
        'nivel_riesgo' => 'Medio',
        'origen_derivacion' => 'Escuela',
        'estado' => 'Abierto',
        'creado_por' => $consulta->id,
    ]);
    crearPersonaParaBuscador(['nombre' => 'Inactiva', 'apellido' => 'Pérez', 'dni' => '40123999', 'activo' => false]);

    $this->actingAs($consulta)
        ->getJson(route('home.buscar-personas', ['q' => '40.123']))
        ->assertOk()
        ->assertJsonCount(1, 'personas')
        ->assertJsonPath('personas.0.dni', '40123456')
        ->assertJsonPath('personas.0.legajos.0.numero', 'PER-JUA-456')
        ->assertJsonPath('personas.0.legajos.0.url', route('legajos.show', $legajo));

    $this->actingAs($consulta)
        ->getJson(route('home.buscar-personas', ['q' => 'pérez juan']))
        ->assertOk()
        ->assertJsonCount(1, 'personas')
        ->assertJsonPath('personas.0.nombre', 'Pérez, Juan');

    $this->actingAs($consulta)
        ->getJson(route('home.buscar-personas', ['q' => 'p']))
        ->assertOk()
        ->assertJsonCount(0, 'personas');
});

test('guests cannot use the home search', function () {
    $this->getJson(route('home.buscar-personas', ['q' => 'perez']))->assertUnauthorized();
});

test('home only shows shortcuts the user can open and greets them by name', function () {
    $operador = User::factory()->create(['rol_id' => 2, 'name' => 'Lucía Gómez']);

    $this->actingAs($operador)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Lucía</h1>', false)
        ->assertDontSee('Buscar o crear un legajo')
        ->assertSee('Nuevo legajo')
        ->assertSee(route('legajos.create'), false)
        ->assertDontSee(route('agenda.index'), false);
});

test('creating a legajo from the search keeps the typed dni', function () {
    $operador = User::factory()->create(['rol_id' => 2]);

    $this->actingAs($operador)
        ->get(route('legajos.create', ['dni' => '40.123.456']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('dniInicial', '40123456'));
});

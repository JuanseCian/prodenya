<?php

use App\Models\Legajo;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
        ['id' => 3, 'nombre' => 'consulta', 'descripcion' => 'Solo lectura', 'creado_en' => now()],
    ]);
});

function crearPersonaConLegajo(User $usuario): array
{
    $provincia = DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $localidad = DB::table('localidades')->insertGetId([
        'nombre' => 'San Nicolás de los Arroyos',
        'provincia_id' => $provincia,
        'codigo_postal' => 2900,
    ]);
    $persona = Persona::create([
        'nombre' => 'Ana',
        'apellido' => 'Prueba',
        'dni' => '30111222',
        'fecha_nacimiento' => '2010-01-01',
        'genero' => 'F',
        'localidad_id' => $localidad,
        'activo' => true,
    ]);
    $legajo = Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'PRA-ANA-222',
        'fecha_apertura' => today(),
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Prueba',
        'estado' => 'Abierto',
        'creado_por' => $usuario->id,
    ]);

    return [$persona, $legajo];
}

test('closing and reopening a legajo updates its closure date', function () {
    $usuario = User::factory()->create(['rol_id' => 1]);
    [, $legajo] = crearPersonaConLegajo($usuario);

    $this->actingAs($usuario)->patch(route('legajos.cerrar', $legajo));

    expect($legajo->fresh()->fecha_cierre?->toDateString())->toBe(today()->toDateString());

    $this->actingAs($usuario)->patch(route('legajos.reabrir', $legajo));

    expect($legajo->fresh()->fecha_cierre)->toBeNull();
});

test('statistics are role protected and expose the redesigned data contract', function () {
    $consulta = User::factory()->create(['rol_id' => 3]);
    crearPersonaConLegajo($consulta);

    $this->actingAs($consulta)
        ->get(route('estadisticas', ['preset' => 'hoy']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('estadisticas/index')
            ->has('kpis.personasAltas')
            ->has('kpis.legajosAltas')
            ->has('kpis.legajosBajas')
            ->has('intervencionesPorTipo')
            ->where('mapaBarrios.disponible', false)
            ->has('mapaBarrios.zonas'));

    $this->actingAs($consulta)
        ->get(route('estadisticas.legajos', ['preset' => 'hoy']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('estadisticas/legajos')
            ->where('kpis.totalLegajos', 1)
            ->where('legajosPorEstado.abiertos', 1));

    $this->actingAs($consulta)
        ->get(route('estadisticas.exportar', ['preset' => 'hoy']))
        ->assertDownload('estadisticas-'.today()->toDateString().'.csv');

    $sinRol = User::factory()->create(['rol_id' => null]);

    $this->actingAs($sinRol)->get(route('estadisticas'))->assertForbidden();
});

test('a legajo has an independent statistics page', function () {
    $consulta = User::factory()->create(['rol_id' => 3]);
    [, $legajo] = crearPersonaConLegajo($consulta);

    $this->actingAs($consulta)
        ->get(route('legajos.estadisticas', $legajo))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('legajos/estadisticas')
            ->where('legajo.numero_legajo', 'PRA-ANA-222')
            ->has('estadisticas.intervenciones')
            ->has('estadisticas.derivaciones'));
});

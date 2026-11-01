<?php

use App\Models\Legajo;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 4, 'nombre' => 'coordinador', 'descripcion' => 'Supervisión', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
    ]);
});

function crearLegajoParaSecciones(User $creador): Legajo
{
    $provincia = DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $localidad = DB::table('localidades')->insertGetId([
        'nombre' => 'San Nicolás de los Arroyos',
        'provincia_id' => $provincia,
        'codigo_postal' => 2900,
    ]);
    $persona = Persona::create([
        'nombre' => 'Ana',
        'apellido' => 'Reservada',
        'dni' => '30111222',
        'fecha_nacimiento' => '2010-01-01',
        'genero' => 'F',
        'localidad_id' => $localidad,
        'activo' => true,
    ]);

    return Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'RES-ANA-222',
        'fecha_apertura' => today(),
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Institución',
        'observaciones' => 'Observación sensible',
        'estado' => 'Abierto',
        'creado_por' => $creador->id,
    ]);
}

test('an admin can restrict which legajo sections a user may see', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $profesional = User::factory()->create(['rol_id' => 5]);

    $this->actingAs($admin)
        ->put(route('admin.usuarios.secciones-legajo', $profesional), [
            'acceso_completo' => '0',
            'secciones' => ['intervenciones'],
        ])
        ->assertRedirect();

    expect($profesional->refresh()->secciones_legajo_visibles)->toBe(['intervenciones']);
});

test('a non admin cannot restrict legajo sections', function () {
    $coordinador = User::factory()->create(['rol_id' => 4]);
    $profesional = User::factory()->create(['rol_id' => 5]);

    $this->actingAs($coordinador)
        ->put(route('admin.usuarios.secciones-legajo', $profesional), [
            'secciones' => ['intervenciones'],
        ])
        ->assertForbidden();
});

test('an admin cannot restrict another admin', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $otroAdmin = User::factory()->create(['rol_id' => 1]);

    $this->actingAs($admin)
        ->put(route('admin.usuarios.secciones-legajo', $otroAdmin), [
            'secciones' => ['intervenciones'],
        ])
        ->assertStatus(422);
});

test('a user restricted to one section only receives that section data on the legajo page', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $profesional = User::factory()->create(['rol_id' => 5, 'secciones_legajo_visibles' => ['intervenciones']]);
    $legajo = crearLegajoParaSecciones($admin);

    $response = $this->actingAs($profesional)->get(route('legajos.show', $legajo));

    $response->assertInertia(fn ($page) => $page
        ->where('secciones.persona', false)
        ->where('secciones.seguimiento', false)
        ->where('secciones.situacion', false)
        ->where('secciones.intervenciones', true)
        ->where('legajo.persona', null)
        ->where('legajo.observaciones', null)
        ->where('legajo.origen_derivacion', null),
    );
});

test('a user with no restriction configured still sees every section', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $profesional = User::factory()->create(['rol_id' => 5]);
    $legajo = crearLegajoParaSecciones($admin);

    $response = $this->actingAs($profesional)->get(route('legajos.show', $legajo));

    $response->assertInertia(fn ($page) => $page
        ->where('secciones.persona', true)
        ->where('secciones.seguimiento', true)
        ->where('secciones.situacion', true)
        ->where('legajo.persona.nombre_completo', 'Reservada, Ana')
        ->where('legajo.observaciones', 'Observación sensible'),
    );
});

test('an admin always sees every legajo section regardless of configuration', function () {
    $admin = User::factory()->create(['rol_id' => 1, 'secciones_legajo_visibles' => []]);
    $legajo = crearLegajoParaSecciones($admin);

    $response = $this->actingAs($admin)->get(route('legajos.show', $legajo));

    $response->assertInertia(fn ($page) => $page
        ->where('secciones.persona', true)
        ->where('secciones.intervenciones', true)
        ->where('secciones.derivaciones', true),
    );
});

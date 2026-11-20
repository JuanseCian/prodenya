<?php

use App\Models\Legajo;
use App\Models\Problematica;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
    ]);
});

/**
 * @param  array<string, mixed>  $extra
 * @return array<string, mixed>
 */
function datosNuevoLegajo(array $extra = []): array
{
    $provincia = DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $localidad = DB::table('localidades')->insertGetId([
        'nombre' => 'San Nicolás',
        'provincia_id' => $provincia,
        'codigo_postal' => 2900,
    ]);

    return [
        'nombre' => 'Mora',
        'apellido' => 'Problemática',
        'dni' => '42111222',
        'fecha_nacimiento' => '2012-03-04',
        'genero' => 'F',
        'provincia_id' => $provincia,
        'localidad_id' => $localidad,
        'abrir_legajo' => true,
        'legajo_nivel_riesgo' => 'Medio',
        'legajo_origen_derivacion' => 'Escuela',
        ...$extra,
    ];
}

test('choosing «Otros» requires saying which problem it is', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $otros = Problematica::create(['nombre' => 'Otros']);

    $this->actingAs($admin)
        ->post(route('legajos.store'), datosNuevoLegajo(['legajo_problematicas' => [$otros->id]]))
        ->assertInvalid(['legajo_problematica_otros' => 'Contá brevemente cuál es la problemática «Otros».']);

    expect(Legajo::count())->toBe(0);
});

test('the «Otros» detail is stored with the legajo and shown in its view', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $salud = Problematica::create(['nombre' => 'Salud']);
    $otros = Problematica::create(['nombre' => 'Otros']);

    $this->actingAs($admin)
        ->post(route('legajos.store'), datosNuevoLegajo([
            'legajo_problematicas' => [$salud->id, $otros->id],
            'legajo_problematica_otros' => 'Conflicto en la escuela',
        ]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('legajos.index'));

    $legajo = Legajo::sole();

    expect($legajo->problematicas()->where('problematicas.id', $otros->id)->sole()->pivot->detalle)
        ->toBe('Conflicto en la escuela')
        ->and($legajo->problematicas()->where('problematicas.id', $salud->id)->sole()->pivot->detalle)
        ->toBeNull();

    $this->actingAs($admin)
        ->get(route('legajos.show', $legajo))
        ->assertInertia(fn (Assert $page) => $page
            ->where('legajo.problematicas', fn ($problematicas) => collect($problematicas)->sort()->values()->all() === ['Otros: Conflicto en la escuela', 'Salud']));
});

test('the detail is ignored when «Otros» is not chosen', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $salud = Problematica::create(['nombre' => 'Salud']);
    Problematica::create(['nombre' => 'Otros']);

    $this->actingAs($admin)
        ->post(route('legajos.store'), datosNuevoLegajo([
            'legajo_problematicas' => [$salud->id],
            'legajo_problematica_otros' => 'Texto que no corresponde',
        ]))
        ->assertSessionHasNoErrors();

    expect(Legajo::sole()->problematicas()->sole()->pivot->detalle)->toBeNull();
});

test('the new legajo form receives the «Otros» id', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $otros = Problematica::create(['nombre' => 'Otros']);

    $this->actingAs($admin)
        ->get(route('legajos.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('personas/create')
            ->where('problematicaOtrosId', $otros->id));
});

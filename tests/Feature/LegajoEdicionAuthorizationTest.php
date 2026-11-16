<?php

use App\Models\Legajo;
use App\Models\Persona;
use App\Models\Problematica;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
        ['id' => 3, 'nombre' => 'consulta', 'descripcion' => 'Solo lectura', 'creado_en' => now()],
        ['id' => 4, 'nombre' => 'oficios', 'descripcion' => 'Derivaciones', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'coordinador', 'descripcion' => 'Supervisión', 'creado_en' => now()],
        ['id' => 6, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
        ['id' => 7, 'nombre' => 'administrativo', 'descripcion' => 'Carga operativa', 'creado_en' => now()],
    ]);
});

function crearLegajoParaEdicion(User $creador): Legajo
{
    $provincia = DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $localidad = DB::table('localidades')->insertGetId([
        'nombre' => 'San Nicolás de los Arroyos',
        'provincia_id' => $provincia,
        'codigo_postal' => 2900,
    ]);
    $persona = Persona::create([
        'nombre' => 'Ana',
        'apellido' => 'Editable',
        'dni' => '30111222',
        'fecha_nacimiento' => '2010-01-01',
        'genero' => 'F',
        'localidad_id' => $localidad,
        'activo' => true,
    ]);

    return Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'EDI-ANA-222',
        'fecha_apertura' => today(),
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Institución',
        'observaciones' => 'Observación inicial.',
        'estado' => 'Abierto',
        'creado_por' => $creador->id,
    ]);
}

function datosActualizacionLegajo(Problematica $problematica): array
{
    return [
        'nivel_riesgo' => 'Medio',
        'origen_derivacion' => 'Institución actualizada',
        'problematicas' => [$problematica->id],
        'observaciones' => 'Observación actualizada.',
    ];
}

test('admin and administrativo can edit a legajo from its detail page', function (int $rolId) {
    $usuario = User::factory()->create(['rol_id' => $rolId]);
    $legajo = crearLegajoParaEdicion($usuario);
    $problematica = Problematica::create(['nombre' => 'Vulneración de derechos']);

    $this->actingAs($usuario)
        ->get(route('legajos.show', $legajo))
        ->assertInertia(fn ($page) => $page->where('puedeEditarLegajo', true));

    $this->actingAs($usuario)
        ->get(route('legajos.edit', $legajo))
        ->assertOk();

    $this->actingAs($usuario)
        ->put(route('legajos.update', $legajo), datosActualizacionLegajo($problematica))
        ->assertRedirect();

    expect($legajo->refresh()->nivel_riesgo)->toBe('Medio')
        ->and($legajo->observaciones)->toBe('Observación actualizada.')
        ->and($legajo->problematicas()->whereKey($problematica->id)->exists())->toBeTrue();
})->with([1, 7]);

test('other roles cannot edit a legajo from its detail page', function (int $rolId) {
    $usuario = User::factory()->create(['rol_id' => $rolId]);
    $legajo = crearLegajoParaEdicion($usuario);
    $problematica = Problematica::create(['nombre' => 'Vulneración de derechos']);
    $datos = datosActualizacionLegajo($problematica);

    $this->actingAs($usuario)
        ->get(route('legajos.show', $legajo))
        ->assertInertia(fn ($page) => $page->where('puedeEditarLegajo', false));

    $this->actingAs($usuario)
        ->get(route('legajos.edit', $legajo))
        ->assertForbidden();

    $this->actingAs($usuario)
        ->put(route('legajos.update', $legajo), $datos)
        ->assertForbidden();

    expect($legajo->refresh()->nivel_riesgo)->toBe('Bajo')
        ->and($legajo->observaciones)->toBe('Observación inicial.')
        ->and($legajo->problematicas()->whereKey($problematica->id)->exists())->toBeFalse();
})->with([2, 3, 4, 5, 6]);

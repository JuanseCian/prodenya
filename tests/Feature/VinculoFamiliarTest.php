<?php

use App\Models\Intervencion;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

/** @return array{0: Persona, 1: Legajo} */
function crearPersonaConLegajoParaVinculo(string $sufijo, int $usuarioId): array
{
    $provinciaId = DB::table('provincias')->insertGetId(['nombre' => "Provincia {$sufijo}"]);
    $localidadId = DB::table('localidades')->insertGetId([
        'nombre' => "Localidad {$sufijo}",
        'provincia_id' => $provinciaId,
        'codigo_postal' => 1000,
    ]);

    $persona = Persona::create([
        'nombre' => 'Persona',
        'apellido' => $sufijo,
        'dni' => "300000{$sufijo}",
        'fecha_nacimiento' => '2010-01-01',
        'genero' => 'X',
        'localidad_id' => $localidadId,
        'activo' => true,
    ]);

    $legajo = Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => "LEG-{$sufijo}",
        'motivo_ingreso' => 'Acompañamiento familiar',
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Test',
        'fecha_apertura' => now()->toDateString(),
        'estado' => 'Abierto',
        'creado_por' => $usuarioId,
    ]);

    return [$persona, $legajo];
}

function crearRol(string $nombre): int
{
    return (int) DB::table('roles')->insertGetId([
        'nombre' => $nombre,
        'descripcion' => $nombre,
        'creado_en' => now(),
    ]);
}

test('authorized users can link and unlink legajos without merging histories', function () {
    $admin = User::factory()->create(['rol_id' => crearRol('admin')]);
    [$personaA, $legajoA] = crearPersonaConLegajoParaVinculo('A', $admin->id);
    [$personaB, $legajoB] = crearPersonaConLegajoParaVinculo('B', $admin->id);

    $this->actingAs($admin)
        ->post(route('legajos.vinculos.store', $legajoA), [
            'persona_relacionada_id' => $personaB->id,
            'tipo_vinculo' => 'hermano/a',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('vinculos_familiares', [
        'persona_id' => $personaA->id,
        'persona_relacionada_id' => $personaB->id,
        'tipo_vinculo' => 'hermano/a',
        'desvinculado_en' => null,
    ]);

    $this->assertDatabaseCount('intervenciones', 0);

    $response = $this->actingAs($admin)
        ->get(route('legajos.show', $legajoA))
        ->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->has('vinculos', 1)
        ->where('vinculos.0.persona.id', $legajoB->id)
        ->where('vinculos.0.tipo', 'hermano/a'),
    );

    $vinculoId = DB::table('vinculos_familiares')->value('id');

    $this->actingAs($admin)
        ->delete(route('legajos.vinculos.destroy', [$legajoA, $vinculoId]))
        ->assertRedirect();

    $this->assertDatabaseMissing('vinculos_familiares', [
        'id' => $vinculoId,
        'desvinculado_en' => null,
    ]);
});

test('consulta users cannot manage family links', function () {
    $consulta = User::factory()->create(['rol_id' => crearRol('consulta')]);
    [, $legajoA] = crearPersonaConLegajoParaVinculo('C', $consulta->id);
    [$personaB] = crearPersonaConLegajoParaVinculo('D', $consulta->id);

    $this->actingAs($consulta)
        ->post(route('legajos.vinculos.store', $legajoA), [
            'persona_relacionada_id' => $personaB->id,
            'tipo_vinculo' => 'primo/a',
        ])
        ->assertForbidden();
});

test('an intervention can be copied to linked legajos without copying signatures', function () {
    Storage::fake('local');

    $admin = User::factory()->create(['rol_id' => crearRol('admin')]);
    [$personaA, $legajoA] = crearPersonaConLegajoParaVinculo('E', $admin->id);
    [$personaB, $legajoB] = crearPersonaConLegajoParaVinculo('F', $admin->id);

    $this->actingAs($admin)->post(route('legajos.vinculos.store', $legajoA), [
        'persona_relacionada_id' => $personaB->id,
        'tipo_vinculo' => 'hermano/a',
    ])->assertRedirect();

    $tipoId = DB::table('tipos_intervencion')->insertGetId([
        'nombre' => 'Acta de prueba',
        'activo' => true,
    ]);
    DB::table('tipos_documento')->insert(['nombre' => 'Otro']);

    $response = $this->actingAs($admin)->post(route('legajos.intervenciones.store', $legajoA), [
        'fecha_intervencion' => today()->toDateString(),
        'hora_intervencion' => '10:00',
        'tipo_intervencion_id' => $tipoId,
        'profesional_id' => $admin->id,
        'detalle' => 'Entrevista familiar conjunta.',
        'firma_nombre' => 'Administrador',
        'firma_aclaracion' => 'Profesional',
        'firma_responsable_dibujo' => 'data:image/png;base64,iVBORw0KGgo=',
        'archivos' => [UploadedFile::fake()->create('acta.pdf', 10, 'application/pdf')],
        'legajos_destino_ids' => [$legajoB->id],
    ]);

    $response->assertRedirect(route('legajos.show', $legajoA));

    $intervenciones = Intervencion::orderBy('id')->get();
    $source = $intervenciones->first();
    $copy = $intervenciones->last();

    expect($intervenciones)->toHaveCount(2)
        ->and($source->legajo_id)->toBe($legajoA->id)
        ->and($copy->legajo_id)->toBe($legajoB->id)
        ->and($copy->intervencion_origen_id)->toBe($source->id)
        ->and($copy->grupo_copia_id)->toBe($source->grupo_copia_id)
        ->and($copy->detalle)->toBe($source->detalle)
        ->and($copy->firma_archivo_path)->toBeNull();

    $documentos = DB::table('documentos')->orderBy('id')->get();

    expect($documentos)->toHaveCount(2)
        ->and($documentos[0]->legajo_id)->toBe($legajoA->id)
        ->and($documentos[1]->legajo_id)->toBe($legajoB->id);

    Storage::disk('local')->assertExists($documentos[0]->ruta_archivo);
    Storage::disk('local')->assertExists($documentos[1]->ruta_archivo);
});

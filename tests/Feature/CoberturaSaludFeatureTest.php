<?php

use App\Models\CoberturaSalud;
use App\Models\CoberturaSaludArchivo;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
    ]);
});

test('a user with access to personal data can save a health coverage with documents', function () {
    Storage::fake('local');
    $profesional = User::factory()->create(['rol_id' => 5]);
    $legajo = crearLegajoParaCobertura($profesional);

    $this->actingAs($profesional)
        ->post(route('legajos.cobertura.store', $legajo), [
            'tipo' => 'obra_social',
            'origen' => 'relacion_dependencia',
            'entidad' => 'Obra Social de Prueba',
            'plan' => 'Plan familiar',
            'numero_afiliado' => 'AF-12345',
            'persona_es_titular' => true,
            'fecha_desde' => '2026-01-01',
            'credencial' => UploadedFile::fake()->create('credencial.pdf', 100, 'application/pdf'),
            'constancias' => [UploadedFile::fake()->create('constancia.pdf', 100, 'application/pdf')],
        ])
        ->assertRedirect();

    $cobertura = CoberturaSalud::firstOrFail();

    expect($cobertura->persona_id)->toBe($legajo->persona_id)
        ->and($cobertura->entidad)->toBe('Obra Social de Prueba')
        ->and(CoberturaSaludArchivo::where('cobertura_salud_id', $cobertura->id)->count())->toBe(2);

    Storage::disk('local')->assertExists(CoberturaSaludArchivo::firstOrFail()->ruta_archivo);
});

test('replacing a health coverage preserves the previous record as history', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaCobertura($admin);

    $datos = [
        'tipo' => 'obra_social',
        'origen' => 'relacion_dependencia',
        'entidad' => 'Obra Social Anterior',
        'persona_es_titular' => true,
        'fecha_desde' => '2026-01-01',
    ];

    $this->actingAs($admin)->post(route('legajos.cobertura.store', $legajo), $datos)->assertRedirect();
    $anterior = CoberturaSalud::firstOrFail();

    $this->actingAs($admin)->post(route('legajos.cobertura.store', $legajo), [
        ...$datos,
        'entidad' => 'Prepaga Nueva',
        'tipo' => 'prepaga',
        'origen' => 'voluntaria_particular',
        'fecha_desde' => '2026-06-01',
    ])->assertRedirect();

    expect(CoberturaSalud::where('activo', true)->value('entidad'))->toBe('Prepaga Nueva')
        ->and($anterior->refresh()->activo)->toBeFalse()
        ->and($anterior->fecha_hasta?->toDateString())->toBe('2026-05-31');
});

test('municipal coverage requires a program name', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaCobertura($admin);

    $this->actingAs($admin)
        ->post(route('legajos.cobertura.store', $legajo), [
            'tipo' => 'municipal',
            'origen' => 'municipal',
            'persona_es_titular' => true,
            'fecha_desde' => '2026-01-01',
        ])
        ->assertSessionHasErrors('programa');
});

test('coverage files are downloaded through the protected legajo route', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaCobertura($admin);

    $this->actingAs($admin)->post(route('legajos.cobertura.store', $legajo), [
        'tipo' => 'obra_social',
        'origen' => 'relacion_dependencia',
        'entidad' => 'Obra Social de Prueba',
        'persona_es_titular' => true,
        'fecha_desde' => '2026-01-01',
        'credencial' => UploadedFile::fake()->create('credencial.pdf', 100, 'application/pdf'),
    ])->assertRedirect();

    $archivo = CoberturaSaludArchivo::firstOrFail();

    $this->actingAs($admin)
        ->get(route('legajos.cobertura.archivos.descargar', [$legajo, $archivo]))
        ->assertDownload('credencial.pdf');
});

test('a user without personal data access cannot save coverage', function () {
    $profesional = User::factory()->create([
        'rol_id' => 5,
        'secciones_legajo_visibles' => ['intervenciones'],
    ]);
    $legajo = crearLegajoParaCobertura($profesional);

    $this->actingAs($profesional)
        ->post(route('legajos.cobertura.store', $legajo), [
            'tipo' => 'sin_cobertura',
            'origen' => 'no_aplica',
            'persona_es_titular' => true,
            'fecha_desde' => '2026-01-01',
        ])
        ->assertForbidden();
});

function crearLegajoParaCobertura(User $creador): Legajo
{
    $provincia = DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $localidad = DB::table('localidades')->insertGetId([
        'nombre' => 'San Nicolás',
        'provincia_id' => $provincia,
        'codigo_postal' => 2900,
    ]);
    $persona = Persona::create([
        'nombre' => 'Ana',
        'apellido' => 'Cobertura',
        'dni' => '30111222',
        'fecha_nacimiento' => '2010-01-01',
        'genero' => 'F',
        'localidad_id' => $localidad,
        'activo' => true,
    ]);

    return Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'COB-'.$creador->id.'-'.uniqid(),
        'fecha_apertura' => today(),
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Institución',
        'estado' => 'Abierto',
        'creado_por' => $creador->id,
    ]);
}

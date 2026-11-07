<?php

use App\Models\Intervencion;
use App\Models\Legajo;
use App\Models\MedidaAbrigo;
use App\Models\MedidaAbrigoActa;
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

test('a professional can save a measure with its acts and automatic intervention', function () {
    Storage::fake('local');
    $professional = User::factory()->create(['rol_id' => 5]);
    $legajo = crearLegajoParaMedida($professional);

    $response = $this->actingAs($professional)->post(route('legajos.medidas-abrigo.store', $legajo), [
        'ubicacion' => 'institucion',
        'duracion_dias' => 90,
        'fecha_inicio' => '2026-01-01',
        'observaciones' => 'Seguimiento semanal.',
        'actas' => [
            [
                'tipo' => 'per',
                'archivo' => UploadedFile::fake()->create('per.pdf', 100, 'application/pdf'),
            ],
            [
                'tipo' => 'informe',
                'archivo' => UploadedFile::fake()->create('informe.png', 100, 'image/png'),
            ],
        ],
    ]);

    $response->assertRedirect()->assertSessionHas('status');

    $medida = MedidaAbrigo::firstOrFail();
    $intervencion = Intervencion::firstOrFail();

    expect($medida->legajo_id)->toBe($legajo->id)
        ->and($medida->ubicacion)->toBe('institucion')
        ->and($medida->fecha_fin->toDateString())->toBe('2026-03-31')
        ->and($intervencion->legajo_id)->toBe($legajo->id)
        ->and($intervencion->detalle)->toContain('hasta 31/03/2026')
        ->and(MedidaAbrigoActa::where('medida_abrigo_id', $medida->id)->count())->toBe(2);

    Storage::disk('local')->assertExists(MedidaAbrigoActa::firstOrFail()->ruta_archivo);
});

test('a measure requires at least one valid act and allowed duration', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaMedida($admin);

    $this->actingAs($admin)
        ->post(route('legajos.medidas-abrigo.store', $legajo), [
            'ubicacion' => 'institucion',
            'duracion_dias' => 30,
            'fecha_inicio' => '2026-01-01',
            'actas' => [],
        ])
        ->assertSessionHasErrors([
            'duracion_dias' => 'La medida debe durar 90 o 180 días.',
            'actas' => 'Cargá al menos un acta.',
        ]);

    expect(MedidaAbrigo::count())->toBe(0)
        ->and(Intervencion::count())->toBe(0);
});

test('a user without measures section access cannot save a measure', function () {
    $professional = User::factory()->create([
        'rol_id' => 5,
        'secciones_legajo_visibles' => ['intervenciones'],
    ]);
    $legajo = crearLegajoParaMedida($professional);

    $this->actingAs($professional)
        ->post(route('legajos.medidas-abrigo.store', $legajo), [
            'ubicacion' => 'institucion',
            'duracion_dias' => 90,
            'fecha_inicio' => '2026-01-01',
            'actas' => [[
                'tipo' => 'per',
                'archivo' => UploadedFile::fake()->create('per.pdf', 100, 'application/pdf'),
            ]],
        ])
        ->assertForbidden();
});

test('an act can only be downloaded through its own legajo route', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaMedida($admin);
    $otroLegajo = crearLegajoParaMedida($admin);

    $this->actingAs($admin)->post(route('legajos.medidas-abrigo.store', $legajo), [
        'ubicacion' => 'familia_ampliada',
        'duracion_dias' => 180,
        'fecha_inicio' => '2026-01-01',
        'actas' => [[
            'tipo' => 'solicitud_vacante',
            'archivo' => UploadedFile::fake()->create('vacante.pdf', 100, 'application/pdf'),
        ]],
    ])->assertRedirect();

    $acta = MedidaAbrigoActa::firstOrFail();

    $this->actingAs($admin)
        ->get(route('legajos.medidas-abrigo.actas.descargar', [$legajo, $acta]))
        ->assertDownload('vacante.pdf');

    $this->actingAs($admin)
        ->get(route('legajos.medidas-abrigo.actas.descargar', [$otroLegajo, $acta]))
        ->assertNotFound();
});

function crearLegajoParaMedida(User $usuario): Legajo
{
    $provincia = DB::table('provincias')->where('nombre', 'Buenos Aires')->value('id')
        ?? DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $localidad = DB::table('localidades')
        ->where('nombre', 'San Nicolás')
        ->where('provincia_id', $provincia)
        ->value('id')
        ?? DB::table('localidades')->insertGetId([
            'nombre' => 'San Nicolás',
            'provincia_id' => $provincia,
            'codigo_postal' => 2900,
        ]);
    $persona = Persona::create([
        'nombre' => 'Ana',
        'apellido' => 'Medida',
        'dni' => '30'.random_int(100000, 999999),
        'fecha_nacimiento' => '2010-01-01',
        'genero' => 'F',
        'localidad_id' => $localidad,
        'activo' => true,
    ]);

    return Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'MED-'.$usuario->id.'-'.uniqid(),
        'fecha_apertura' => today(),
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Institución',
        'estado' => 'Abierto',
        'creado_por' => $usuario->id,
    ]);
}

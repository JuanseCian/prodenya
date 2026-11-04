<?php

use App\Models\CudArchivo;
use App\Models\CudCertificado;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 3, 'nombre' => 'coordinador', 'descripcion' => 'Supervisión', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
    ]);
});

test('admin can save a CUD with its documents', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaCud($admin);

    $this->actingAs($admin)
        ->post(route('legajos.cud.store', $legajo), [
            'numero_cud' => 'CUD-123456',
            'fecha_vigencia' => '2026-01-01',
            'fecha_vencimiento' => '2028-01-01',
            'observaciones' => 'Observación de prueba',
            'frente' => UploadedFile::fake()->create('frente.pdf', 100, 'application/pdf'),
            'dorso' => UploadedFile::fake()->create('dorso.png', 100, 'image/png'),
            'recetas' => [UploadedFile::fake()->create('receta.pdf', 100, 'application/pdf')],
        ])
        ->assertRedirect();

    $certificado = CudCertificado::firstOrFail();
    expect($certificado->legajo_id)->toBe($legajo->id)
        ->and($certificado->numero_cud)->toBe('CUD-123456')
        ->and(CudArchivo::where('cud_certificado_id', $certificado->id)->count())->toBe(3);

    Storage::disk('local')->assertExists(CudArchivo::firstOrFail()->ruta_archivo);
});

test('saving an unchanged CUD does not create a new history version', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaCud($admin);
    $datos = [
        'numero_cud' => 'CUD-SIN-CAMBIOS',
        'fecha_vigencia' => '2026-01-01',
        'fecha_vencimiento' => '2028-01-01',
        'observaciones' => 'Sin cambios',
    ];

    $this->actingAs($admin)
        ->post(route('legajos.cud.store', $legajo), $datos)
        ->assertRedirect();

    $this->actingAs($admin)
        ->post(route('legajos.cud.store', $legajo), $datos)
        ->assertRedirect()
        ->assertSessionHas('status', 'La documentación del CUD ya está actualizada. No se creó una nueva versión.');

    expect(CudCertificado::count())->toBe(1)
        ->and(CudCertificado::where('activo', true)->count())->toBe(1);
});

test('the legajo visualization includes the current CUD summary', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaCud($admin);
    $certificado = CudCertificado::create([
        'legajo_id' => $legajo->id,
        'numero_cud' => 'CUD-VISTA',
        'fecha_vigencia' => '2026-01-01',
        'fecha_vencimiento' => '2028-01-01',
        'activo' => true,
        'creado_por' => $admin->id,
        'actualizado_por' => $admin->id,
    ]);
    CudArchivo::create([
        'cud_certificado_id' => $certificado->id,
        'tipo' => 'frente',
        'nombre_archivo' => 'frente.pdf',
        'ruta_archivo' => 'cud/frente.pdf',
        'mime_type' => 'application/pdf',
        'tamanio_bytes' => 100,
        'activo' => true,
        'subido_por' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('legajos.show', $legajo))
        ->assertInertia(fn ($page) => $page
            ->component('legajos/show')
            ->where('puedeGestionarCud', true)
            ->where('cud.numero_cud', 'CUD-VISTA')
            ->where('cud.archivos.0.nombre', 'frente.pdf'),
        );
});

test('only administrators and coordinators can manage CUD documents', function () {
    $profesional = User::factory()->create(['rol_id' => 5]);
    $legajo = crearLegajoParaCud($profesional);

    $this->actingAs($profesional)
        ->get(route('legajos.documentacion', $legajo))
        ->assertOk();

    $this->actingAs($profesional)
        ->get(route('legajos.show', $legajo))
        ->assertInertia(fn ($page) => $page->where('puedeGestionarCud', false));

    $this->actingAs($profesional)
        ->post(route('legajos.cud.store', $legajo), [
            'numero_cud' => 'CUD-123456',
            'fecha_vigencia' => '2026-01-01',
            'fecha_vencimiento' => '2028-01-01',
        ])
        ->assertForbidden();
});

test('a user restricted away from personal data cannot open CUD documentation', function () {
    $profesional = User::factory()->create([
        'rol_id' => 5,
        'secciones_legajo_visibles' => ['intervenciones'],
    ]);
    $legajo = crearLegajoParaCud($profesional);

    $this->actingAs($profesional)
        ->get(route('legajos.documentacion', $legajo))
        ->assertForbidden();
});

test('replacing a CUD keeps the previous certificate and files in history', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaCud($admin);

    $datos = [
        'numero_cud' => 'CUD-ANTERIOR',
        'fecha_vigencia' => '2025-01-01',
        'fecha_vencimiento' => '2026-01-01',
        'frente' => UploadedFile::fake()->create('frente.pdf', 100, 'application/pdf'),
    ];

    $this->actingAs($admin)->post(route('legajos.cud.store', $legajo), $datos)->assertRedirect();
    $archivoAnterior = CudArchivo::firstOrFail();

    $this->actingAs($admin)->post(route('legajos.cud.store', $legajo), [
        'numero_cud' => 'CUD-NUEVO',
        'fecha_vigencia' => '2026-01-02',
        'fecha_vencimiento' => '2029-01-02',
    ])->assertRedirect();

    expect(CudCertificado::where('activo', true)->value('numero_cud'))->toBe('CUD-NUEVO')
        ->and(CudCertificado::where('activo', false)->count())->toBe(1)
        ->and(CudArchivo::whereKey($archivoAnterior->id)->value('activo'))->toBeTrue();
});

function crearLegajoParaCud(User $usuario): Legajo
{
    $provincia = DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $localidad = DB::table('localidades')->insertGetId([
        'nombre' => 'San Nicolás',
        'provincia_id' => $provincia,
        'codigo_postal' => 2900,
    ]);
    $persona = Persona::create([
        'nombre' => 'Ana',
        'apellido' => 'CUD',
        'dni' => '30111222',
        'fecha_nacimiento' => '2010-01-01',
        'genero' => 'F',
        'localidad_id' => $localidad,
        'activo' => true,
    ]);

    return Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'CUD-'.$usuario->id.'-'.uniqid(),
        'fecha_apertura' => today(),
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Juzgado',
        'estado' => 'Abierto',
        'creado_por' => $usuario->id,
    ]);
}

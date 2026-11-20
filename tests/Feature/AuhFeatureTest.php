<?php

use App\Models\Auh;
use App\Models\AuhArchivo;
use App\Models\GrupoFamiliar;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\User;
use App\Models\VinculoFamiliar;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 3, 'nombre' => 'consulta', 'descripcion' => 'Solo lectura', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
    ]);
    Storage::fake('local');
});

test('uploading the first AUH planilla opens the AUH record for the legajo', function () {
    $profesional = User::factory()->create(['rol_id' => 5]);
    $legajo = crearLegajoParaAuh($profesional);

    $this->actingAs($profesional)
        ->post(route('legajos.auh.archivos.store', $legajo), [
            'tipo' => 'nota_elevacion',
            'archivo' => UploadedFile::fake()->create('nota.pdf', 100, 'application/pdf'),
            'confirma_firma' => '1',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $auh = Auh::sole();
    $archivo = AuhArchivo::sole();

    expect($auh->legajo_id)->toBe($legajo->id)
        ->and($auh->activo)->toBeTrue()
        ->and($archivo->auh_id)->toBe($auh->id)
        ->and($archivo->tipo)->toBe('nota_elevacion')
        ->and($archivo->nombre_archivo)->toBe('nota.pdf')
        ->and($archivo->firmado)->toBeTrue();

    Storage::disk('local')->assertExists($archivo->ruta_archivo);
});

test('uploading a document of an existing type replaces the previous one and keeps history', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaAuh($admin);

    foreach (['partida-vieja.pdf', 'partida-nueva.pdf'] as $nombre) {
        $this->actingAs($admin)->post(route('legajos.auh.archivos.store', $legajo), [
            'tipo' => 'certificado_nacimiento',
            'archivo' => UploadedFile::fake()->create($nombre, 100, 'application/pdf'),
        ])->assertRedirect();
    }

    expect(Auh::count())->toBe(1)
        ->and(AuhArchivo::count())->toBe(2)
        ->and(AuhArchivo::where('activo', true)->sole()->nombre_archivo)->toBe('partida-nueva.pdf')
        ->and(AuhArchivo::where('activo', false)->sole()->reemplazado_en)->not->toBeNull();
});

test('other documentation accepts several files but requires a description', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaAuh($admin);

    $this->actingAs($admin)
        ->post(route('legajos.auh.archivos.store', $legajo), [
            'tipo' => 'otra',
            'archivo' => UploadedFile::fake()->create('cuil.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors(['descripcion' => 'Indicá qué documentación estás adjuntando.']);

    foreach (['Constancia de CUIL', 'Certificado escolar'] as $descripcion) {
        $this->actingAs($admin)->post(route('legajos.auh.archivos.store', $legajo), [
            'tipo' => 'otra',
            'descripcion' => $descripcion,
            'archivo' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ])->assertRedirect();
    }

    expect(AuhArchivo::where('tipo', 'otra')->where('activo', true)->pluck('descripcion')->all())
        ->toEqualCanonicalizing(['Constancia de CUIL', 'Certificado escolar']);
});

test('AUH files must be an allowed type', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaAuh($admin);

    $this->actingAs($admin)
        ->post(route('legajos.auh.archivos.store', $legajo), [
            'tipo' => 'declaracion_jurada',
            'archivo' => UploadedFile::fake()->create('script.exe', 10, 'application/octet-stream'),
        ])
        ->assertSessionHasErrors(['archivo' => 'El archivo debe ser PDF, Word, JPG o PNG.']);

    expect(Auh::count())->toBe(0);
});

test('users without the AUH section or a managing role cannot upload files', function (array $atributos) {
    $usuario = User::factory()->create($atributos);
    $legajo = crearLegajoParaAuh($usuario);

    $this->actingAs($usuario)
        ->post(route('legajos.auh.archivos.store', $legajo), [
            'tipo' => 'nota_elevacion',
            'archivo' => UploadedFile::fake()->create('nota.pdf', 100, 'application/pdf'),
        ])
        ->assertForbidden();

    expect(AuhArchivo::count())->toBe(0);
})->with([
    'hidden section' => [['rol_id' => 5, 'secciones_legajo_visibles' => ['intervenciones']]],
    'read only role' => [['rol_id' => 3]],
]);

test('an AUH file can only be downloaded or removed through its own legajo', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaAuh($admin);
    $otroLegajo = crearLegajoParaAuh($admin);

    $this->actingAs($admin)->post(route('legajos.auh.archivos.store', $legajo), [
        'tipo' => 'documentacion_responsable',
        'archivo' => UploadedFile::fake()->create('dni-responsable.pdf', 100, 'application/pdf'),
    ])->assertRedirect();

    $archivo = AuhArchivo::sole();

    $this->actingAs($admin)
        ->get(route('legajos.auh.archivos.descargar', [$otroLegajo, $archivo]))
        ->assertNotFound();
    $this->actingAs($admin)
        ->delete(route('legajos.auh.archivos.eliminar', [$otroLegajo, $archivo]))
        ->assertNotFound();

    $this->actingAs($admin)
        ->get(route('legajos.auh.archivos.descargar', [$legajo, $archivo]))
        ->assertDownload('dni-responsable.pdf');
    $this->actingAs($admin)
        ->delete(route('legajos.auh.archivos.eliminar', [$legajo, $archivo]))
        ->assertRedirect();

    expect($archivo->fresh()->activo)->toBeFalse();
    Storage::disk('local')->assertExists($archivo->ruta_archivo);
});

test('the legajo page shows the active AUH documentation', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaAuh($admin);

    $this->actingAs($admin)->post(route('legajos.auh.archivos.store', $legajo), [
        'tipo' => 'documentacion_menor',
        'archivo' => UploadedFile::fake()->create('dni-menor.pdf', 100, 'application/pdf'),
    ])->assertRedirect();

    $this->actingAs($admin)
        ->get(route('legajos.show', $legajo))
        ->assertInertia(fn (Assert $page) => $page
            ->component('legajos/show')
            ->where('secciones.auh', true)
            ->where('puedeGestionarAuh', true)
            ->has('auh.planillas', 3)
            ->has('auh.archivos', 1)
            ->where('auh.archivos.0.tipo', 'documentacion_menor')
            ->where('auh.archivos.0.nombre', 'dni-menor.pdf')
            ->where('auh.archivos.0.firmado', false)
            ->where('auh.firmantes.declaracion_jurada', ['mds' => 'Firma por MDS', 'responsable' => 'Adulto responsable']));
});

test('a planilla is generated as a pre-filled Word document and its data is remembered', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaAuh($admin);

    $response = $this->actingAs($admin)->post(route('legajos.auh.planillas.generar', [$legajo, 'declaracion_jurada']), [
        'fecha' => '2026-11-20',
        'ciudad' => 'San Nicolás',
        'provincia' => 'Buenos Aires',
        'responsable' => ['nombre' => 'Gómez & Pérez, Marta', 'dni' => '22333444'],
        'no_conviviente_nombre' => 'Ruiz, Carlos',
        'no_conviviente_dni' => '20111222',
        'no_conviviente_desde' => 2021,
    ]);

    $response->assertOk()->assertDownload();
    $texto = textoDePlanillaAuh($response);

    expect($texto)
        ->toContain('En la ciudad de San Nicolás Provincia de Buenos Aires')
        ->toContain('A los 20 días, del mes de noviembre del año 2026')
        ->toContain('Por la presente, Gómez &amp; Pérez, Marta, DNI 22333444')
        ->toContain('Declara bajo juramento que Ruiz, Carlos, DNI 20111222')
        ->toContain('desde el año 2021')
        ->not->toContain('${');

    expect(Auh::sole()->datos_planillas['responsable']['dni'])->toBe('22333444');

    $this->actingAs($admin)
        ->get(route('legajos.show', $legajo))
        ->assertInertia(fn (Assert $page) => $page
            ->where('auh.formulario.datos.no_conviviente_nombre', 'Ruiz, Carlos')
            ->where('auh.formulario.datos.responsable.cuil', '')
            ->where('auh.formulario.datos.fecha', today()->toDateString()));
});

test('empty planilla fields stay dotted so they can be completed by hand', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaAuh($admin);

    $response = $this->actingAs($admin)
        ->post(route('legajos.auh.planillas.generar', [$legajo, 'nota_elevacion']), [
            'fecha' => '2026-11-20',
            'nino_nombre' => 'Auh, Luz',
            'nino_dni' => '45000111',
        ])
        ->assertOk();

    expect(textoDePlanillaAuh($response))
        ->toContain('os Aires, 20 de noviembre de 2026')
        ->toContain('<w:t xml:space="preserve"> ……………………………</w:t>')
        ->toContain('Niño/a: Auh, Luz')
        ->toContain('DNI Niño/a: 45000111.')
        ->not->toContain('Modelo')
        ->not->toContain('${');
});

test('the relationship report lists the family group and keeps rows to fill by hand', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaAuh($admin);

    $response = $this->actingAs($admin)
        ->post(route('legajos.auh.planillas.generar', [$legajo, 'informe_relacion']), [
            'responsable' => ['nombre' => 'Gómez, Marta', 'dni' => '22333444', 'cuil' => '27-22333444-5', 'fecha_nacimiento' => '1980-05-02'],
            'grupo_familiar' => [
                ['nombre' => 'Auh, Luz', 'vinculo' => 'nieta', 'dni' => '45000111', 'fecha_nacimiento' => '2015-01-01'],
            ],
            'motivos' => "Primera línea.\nSegunda línea.",
        ])
        ->assertOk();

    $xml = textoDePlanillaAuh($response);

    expect($xml)
        ->toContain('Apellido y Nombre: Gómez, Marta')
        ->toContain('CUIL: 27-22333444-5')
        ->toContain('FECHA DE NAC. 02/05/1980')
        ->toContain('Auh, Luz')
        ->toContain('01/01/2015')
        ->toContain('Primera línea.</w:t><w:br/><w:t>Segunda línea.')
        ->not->toContain('${')
        ->and(substr_count($xml, '<w:tr '))->toBe(6);
});

test('only managing roles can generate planillas and only for known types', function () {
    $consulta = User::factory()->create(['rol_id' => 3]);
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaAuh($admin);

    $this->actingAs($consulta)
        ->post(route('legajos.auh.planillas.generar', [$legajo, 'nota_elevacion']))
        ->assertForbidden();

    $this->actingAs($admin)
        ->post("/legajos/{$legajo->id}/auh/planillas/otra")
        ->assertNotFound();

    expect(Auh::count())->toBe(0);
});

test('the AUH form is pre-filled with the child and their family links', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaAuh($admin);
    $abuela = Persona::create([
        'nombre' => 'Marta',
        'apellido' => 'Gómez',
        'dni' => '22333444',
        'fecha_nacimiento' => '1960-03-03',
        'genero' => 'F',
        'localidad_id' => $legajo->persona->localidad_id,
        'telefono' => '3364000000',
        'activo' => true,
    ]);
    $grupo = GrupoFamiliar::create(['nombre' => 'Familia Gómez', 'creado_por' => $admin->id]);
    VinculoFamiliar::create([
        'grupo_familiar_id' => $grupo->id,
        'persona_id' => $legajo->persona_id,
        'persona_relacionada_id' => $abuela->id,
        'tipo_vinculo' => 'abuelo/a',
        'creado_por' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('legajos.show', $legajo))
        ->assertInertia(fn (Assert $page) => $page
            ->where('auh.formulario.datos.nino_nombre', 'Auh, Luz')
            ->where('auh.formulario.datos.ciudad', 'San Nicolás')
            ->where('auh.formulario.personas.0.nombre', 'Gómez, Marta')
            ->where('auh.formulario.personas.0.telefono', '3364000000')
            ->where('auh.formulario.datos.grupo_familiar.0.vinculo', 'abuelo/a'));
});

test('a scanned planilla must be confirmed as signed, other documents do not', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaAuh($admin);

    $this->actingAs($admin)
        ->post(route('legajos.auh.archivos.store', $legajo), [
            'tipo' => 'declaracion_jurada',
            'archivo' => UploadedFile::fake()->create('ddjj.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors(['confirma_firma' => 'Confirmá que la planilla escaneada está firmada.']);

    $this->actingAs($admin)
        ->post(route('legajos.auh.archivos.store', $legajo), [
            'tipo' => 'certificado_nacimiento',
            'archivo' => UploadedFile::fake()->create('partida.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasNoErrors();

    expect(AuhArchivo::sole()->tipo)->toBe('certificado_nacimiento')
        ->and(AuhArchivo::sole()->firmado)->toBeFalse();
});

test('a planilla signed in the system embeds every signature and is stored in the legajo', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaAuh($admin);

    $this->actingAs($admin)
        ->post(route('legajos.auh.planillas.firmar', [$legajo, 'declaracion_jurada']), [
            'responsable' => ['nombre' => 'Gómez, Marta', 'dni' => '22333444'],
            'firmas' => [
                'mds' => ['nombre' => 'Lic. Paula Díaz', 'dibujo' => firmaPngDePrueba()],
                'responsable' => ['nombre' => 'Gómez, Marta', 'dibujo' => firmaPngDePrueba()],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    $archivo = AuhArchivo::sole();

    expect($archivo->tipo)->toBe('declaracion_jurada')
        ->and($archivo->firmado)->toBeTrue()
        ->and(collect($archivo->firmas)->pluck('nombre', 'rol')->all())
        ->toBe(['mds' => 'Lic. Paula Díaz', 'responsable' => 'Gómez, Marta'])
        ->and(Auh::sole()->datos_planillas)->not->toHaveKey('firmas');

    Storage::disk('local')->assertExists($archivo->ruta_archivo);

    $zip = new ZipArchive;
    $zip->open(Storage::disk('local')->path($archivo->ruta_archivo));
    $xml = $zip->getFromName('word/document.xml');
    $imagenes = collect(range(0, $zip->numFiles - 1))
        ->map(fn (int $i) => $zip->getNameIndex($i))
        ->filter(fn (string $nombre) => str_starts_with($nombre, 'word/media/'));
    $zip->close();

    expect($imagenes)->toHaveCount(2)
        ->and(simplexml_load_string($xml))->not->toBeFalse()
        ->and($xml)->not->toContain('${');
});

test('signing requires a valid signature and clarification from every signer', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaAuh($admin);

    $this->actingAs($admin)
        ->post(route('legajos.auh.planillas.firmar', [$legajo, 'declaracion_jurada']), [
            'firmas' => [
                'responsable' => ['nombre' => '', 'dibujo' => 'data:image/png;base64,'.base64_encode('no es una imagen')],
            ],
        ])
        ->assertSessionHasErrors([
            'firmas.mds.dibujo' => 'Falta la firma de: Firma por MDS.',
            'firmas.responsable.nombre' => 'Ingresá la aclaración de: Adulto responsable.',
            'firmas.responsable.dibujo' => 'La firma de Adulto responsable no es válida.',
        ]);

    expect(AuhArchivo::count())->toBe(0);
});

test('signing a planilla again replaces the previous version and keeps it in history', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaAuh($admin);

    $this->actingAs($admin)->post(route('legajos.auh.archivos.store', $legajo), [
        'tipo' => 'informe_relacion',
        'archivo' => UploadedFile::fake()->create('informe-escaneado.pdf', 100, 'application/pdf'),
        'confirma_firma' => '1',
    ])->assertRedirect();

    $this->actingAs($admin)
        ->post(route('legajos.auh.planillas.firmar', [$legajo, 'informe_relacion']), [
            'firmas' => ['profesional' => ['nombre' => 'Lic. Paula Díaz', 'dibujo' => firmaPngDePrueba()]],
        ])
        ->assertSessionHasNoErrors();

    $vigente = AuhArchivo::where('activo', true)->sole();

    expect($vigente->firmas[0]['rol_label'])->toBe('Profesional interviniente')
        ->and(AuhArchivo::where('activo', false)->sole()->nombre_archivo)->toBe('informe-escaneado.pdf');

    $zip = new ZipArchive;
    $zip->open(Storage::disk('local')->path($vigente->ruta_archivo));
    expect($zip->getFromName('word/document.xml'))->toContain('Lic. Paula Díaz');
    $zip->close();
});

test('the elevation note is signed by the claimant on the left and the reference center', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaAuh($admin);

    $this->actingAs($admin)
        ->post(route('legajos.auh.planillas.firmar', [$legajo, 'nota_elevacion']), [
            'responsable' => ['nombre' => '', 'dni' => '22333444'],
            'firmas' => [
                'solicitante' => ['dibujo' => firmaPngDePrueba()],
                'centro' => ['nombre' => 'Lic. Paula Díaz', 'dibujo' => firmaPngDePrueba()],
            ],
        ])
        ->assertSessionHasErrors(['firmas.solicitante.nombre' => 'Ingresá la aclaración de: Solicitante a cobrar.']);

    $this->actingAs($admin)
        ->post(route('legajos.auh.planillas.firmar', [$legajo, 'nota_elevacion']), [
            'responsable' => ['nombre' => 'Gómez, Marta', 'dni' => '22333444'],
            'firmas' => [
                'solicitante' => ['nombre' => 'otro nombre', 'dibujo' => firmaPngDePrueba()],
                'centro' => ['nombre' => 'Lic. Paula Díaz', 'dibujo' => firmaPngDePrueba()],
            ],
        ])
        ->assertSessionHasNoErrors();

    $archivo = AuhArchivo::sole();
    $zip = new ZipArchive;
    $zip->open(Storage::disk('local')->path($archivo->ruta_archivo));
    $xml = $zip->getFromName('word/document.xml');
    $imagenes = collect(range(0, $zip->numFiles - 1))
        ->filter(fn (int $i) => str_starts_with($zip->getNameIndex($i), 'word/media/'));
    $zip->close();

    expect(collect($archivo->firmas)->pluck('nombre', 'rol')->all())
        ->toBe(['solicitante' => 'Gómez, Marta', 'centro' => 'Lic. Paula Díaz'])
        ->and($imagenes)->toHaveCount(2)
        ->and($xml)->toContain('X Aclaración: Gómez, Marta')
        ->toContain('X DNI: 22333444')
        ->toContain('CENTRO DE REFERENCIA')
        ->not->toContain('${');
});

function firmaPngDePrueba(): string
{
    $imagen = imagecreatetruecolor(320, 80);
    imagefill($imagen, 0, 0, imagecolorallocate($imagen, 255, 255, 255));
    imageline($imagen, 10, 60, 300, 20, imagecolorallocate($imagen, 44, 74, 94));
    ob_start();
    imagepng($imagen);

    return 'data:image/png;base64,'.base64_encode((string) ob_get_clean());
}

function textoDePlanillaAuh(TestResponse $response): string
{
    $zip = new ZipArchive;
    $zip->open($response->baseResponse->getFile()->getPathname());
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();

    expect(simplexml_load_string($xml))->not->toBeFalse('La planilla generada no es un XML válido.');

    return $xml;
}

function crearLegajoParaAuh(User $usuario): Legajo
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
        'nombre' => 'Luz',
        'apellido' => 'Auh',
        'dni' => '40'.random_int(100000, 999999),
        'fecha_nacimiento' => '2015-01-01',
        'genero' => 'F',
        'localidad_id' => $localidad,
        'activo' => true,
    ]);

    return Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'AUH-'.$usuario->id.'-'.uniqid(),
        'fecha_apertura' => today(),
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Institución',
        'estado' => 'Abierto',
        'creado_por' => $usuario->id,
    ]);
}

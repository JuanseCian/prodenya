<?php

use App\Models\Acta;
use App\Models\AuhArchivo;
use App\Models\Escuela;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 3, 'nombre' => 'consulta', 'descripcion' => 'Solo lectura', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
    ]);
    Storage::fake('local');
});

test('an initial acta can be saved without a legajo, signed on screen', function () {
    $profesional = User::factory()->create(['rol_id' => 5, 'name' => 'Lic. Paula Díaz']);

    $this->actingAs($profesional)
        ->post(route('actas.store', 'inicial'), [
            'fecha' => '2026-11-20',
            'hora' => '10:30',
            'operador' => 'Lic. Paula Díaz',
            'compareciente_nombre' => 'Gómez & Hijos, Marta',
            'compareciente_dni' => '22333444',
            'nino_nombre' => 'Gómez, Luz',
            'relato' => "Se presenta por la situación escolar.\nSolicita acompañamiento.",
            'firmas' => [
                'compareciente' => ['nombre' => 'Marta Gómez', 'dibujo' => firmaActaDePrueba()],
                'equipo' => ['nombre' => 'Lic. Paula Díaz', 'dibujo' => firmaActaDePrueba()],
            ],
        ])
        ->assertRedirect(route('actas.index'))
        ->assertSessionHasNoErrors();

    $acta = Acta::sole();
    [$xml, $imagenes] = documentoDeActa($acta);

    expect($acta->legajo_id)->toBeNull()
        ->and($acta->firmado)->toBeTrue()
        ->and($acta->titulo)->toBe('Gómez & Hijos, Marta')
        ->and($acta->datos)->not->toHaveKey('firmas')
        ->and($imagenes)->toBe(2)
        ->and($xml)
        ->toContain('a los 20 días del mes noviembre del año 2026 a las 10:30 HS')
        ->toContain('se hace presente el Sr./a Gómez &amp; Hijos, Marta, quien acredita su identidad con DNI 22333444')
        ->toContain('Se presenta por la situación escolar.</w:t><w:br/><w:t>Solicita acompañamiento.')
        ->toContain('Aclaración: Lic. Paula Díaz')
        ->not->toContain('${');

    $this->actingAs($profesional)
        ->get(route('actas.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('actas/index')
            ->has('sinLegajo', 1)
            ->where('sinLegajo.0.etiqueta', 'Acta inicial')
            ->where('sinLegajo.0.firmado', true));
});

test('every acta template generates a valid document without leftover fields', function (string $tipo) {
    $admin = User::factory()->create(['rol_id' => 1]);

    $this->actingAs($admin)
        ->post(route('actas.store', $tipo), ['fecha' => '2026-11-20', 'compareciente_nombre' => 'Ruiz, Ana'])
        ->assertSessionHasNoErrors();

    [$xml] = documentoDeActa(Acta::sole());

    expect($xml)->not->toContain('${')
        ->and($xml)->toContain('……………………………');
})->with(['inicial', 'domiciliaria', 'telefonica', 'citacion', 'constancia']);

test('an unwritten story keeps the blank lines of the model; a written one replaces them', function () {
    $admin = User::factory()->create(['rol_id' => 1]);

    $this->actingAs($admin)->post(route('actas.store', 'inicial'), ['fecha' => '2026-11-20'])->assertSessionHasNoErrors();
    $this->actingAs($admin)->post(route('actas.store', 'inicial'), ['fecha' => '2026-11-20', 'relato' => 'Relato breve.', 'estrategias' => 'Seguimiento.'])->assertSessionHasNoErrors();

    [$vacia, $completa] = Acta::orderBy('id')->get()->map(fn (Acta $acta) => documentoDeActa($acta)[0])->all();

    expect(substr_count($vacia, '<w:p '))->toBeGreaterThan(substr_count($completa, '<w:p ') + 30)
        ->and($completa)->toContain('Relato breve.')
        ->toContain('Seguimiento.');
});

test('a draft can be downloaded without saving the acta', function () {
    $admin = User::factory()->create(['rol_id' => 1]);

    $this->actingAs($admin)
        ->post(route('actas.borrador', 'citacion'), ['fecha' => '2026-11-20', 'citacion_dia' => '2026-11-24', 'citacion_horario' => '9 a 11 hs'])
        ->assertOk()
        ->assertDownload('citacion-borrador.docx');

    expect(Acta::count())->toBe(0);
});

test('people in the system can be found by DNI or name, with their legajo', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaActa($admin, 'Marta', 'Gómez', '22333444');

    foreach (['22333', 'marta gómez', 'Gómez'] as $busqueda) {
        $this->actingAs($admin)
            ->getJson(route('actas.personas', ['q' => $busqueda]))
            ->assertOk()
            ->assertJsonPath('0.nombre', 'Gómez, Marta')
            ->assertJsonPath('0.legajo.numero_legajo', $legajo->numero_legajo);
    }

    $this->actingAs($admin)->getJson(route('actas.personas', ['q' => 'x']))->assertExactJson([]);
});

test('a signed AUH form linked later joins the legajo AUH without creating an intervention', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaActa($admin, 'Luz', 'Pérez', '45111222');

    $this->actingAs($admin)->post(route('actas.store', 'auh_declaracion_jurada'), [
        'responsable' => ['nombre' => 'Pérez, Ana', 'dni' => '22111222'],
        'firmas' => [
            'mds' => ['nombre' => 'Lic. Paula Díaz', 'dibujo' => firmaActaDePrueba()],
            'responsable' => ['dibujo' => firmaActaDePrueba()],
        ],
    ])->assertSessionHasNoErrors();

    $acta = Acta::sole();
    expect($acta->firmado)->toBeTrue()->and($acta->legajo_id)->toBeNull();

    $this->actingAs($admin)
        ->patch(route('actas.vincular', $acta), ['legajo_id' => $legajo->id])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $archivo = AuhArchivo::sole();

    expect($acta->fresh()->legajo_id)->toBe($legajo->id)
        ->and($acta->fresh()->intervencion_id)->toBeNull()
        ->and($archivo->tipo)->toBe('declaracion_jurada')
        ->and($archivo->firmado)->toBeTrue()
        ->and($archivo->auh->legajo_id)->toBe($legajo->id);
    Storage::disk('local')->assertExists($archivo->ruta_archivo);

    $this->actingAs($admin)
        ->patch(route('actas.vincular', $acta), ['legajo_id' => $legajo->id])
        ->assertSessionHasErrors(['legajo_id' => 'Esta acta ya está vinculada a un legajo.']);
});

test('an unsigned AUH form is linked but does not count as signed in the legajo AUH', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaActa($admin, 'Luz', 'Pérez', '45111222');

    $this->actingAs($admin)->post(route('actas.store', 'auh_nota_elevacion'), [
        'legajo_id' => $legajo->id,
        'responsable' => ['nombre' => 'Pérez, Ana'],
    ])->assertSessionHasNoErrors();

    expect(Acta::sole()->legajo_id)->toBe($legajo->id)
        ->and(AuhArchivo::count())->toBe(0);
});

test('a drawn signature needs a clarification and must be a real image', function () {
    $admin = User::factory()->create(['rol_id' => 1]);

    $this->actingAs($admin)
        ->post(route('actas.store', 'domiciliaria'), [
            'firmas' => [
                'entrevistado' => ['nombre' => '', 'dibujo' => firmaActaDePrueba()],
                'autoridad' => ['nombre' => 'Lic. Díaz', 'dibujo' => 'data:image/png;base64,'.base64_encode('texto')],
            ],
        ])
        ->assertSessionHasErrors([
            'firmas.entrevistado.nombre' => 'Ingresá la aclaración de: Entrevistado/a.',
            'firmas.autoridad.dibujo' => 'La firma de Autoridad del Servicio no es válida.',
        ]);

    expect(Acta::count())->toBe(0);
});

test('read-only users cannot use quick actas', function () {
    $consulta = User::factory()->create(['rol_id' => 3]);

    $this->actingAs($consulta)->get(route('actas.index'))->assertForbidden();
    $this->actingAs($consulta)->post(route('actas.store', 'inicial'), [])->assertForbidden();
});

test('opening a new acta from a legajo pre-fills the child and the legajo', function () {
    $admin = User::factory()->create(['rol_id' => 1, 'name' => 'Lic. Paula Díaz']);
    $legajo = crearLegajoParaActa($admin, 'Luz', 'Pérez', '45111222');

    $this->actingAs($admin)
        ->get(route('actas.create', ['tipo' => 'inicial', 'legajo' => $legajo->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('actas/create')
            ->where('legajo.numero_legajo', $legajo->numero_legajo)
            ->where('datos.nino_nombre', 'Pérez, Luz')
            ->where('datos.nino_dni', '45111222')
            ->where('datos.operador', 'Lic. Paula Díaz')
            ->where('firmantes', ['compareciente' => 'Compareciente', 'equipo' => 'Equipo técnico']));
});

test('an acta saved with a legajo is registered as an intervention of that legajo', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaActa($admin, 'Luz', 'Pérez', '45111222');

    $this->actingAs($admin)
        ->post(route('actas.store', 'telefonica'), ['legajo_id' => $legajo->id, 'compareciente_nombre' => 'Pérez, Ana', 'relato' => 'Llamado de seguimiento.'])
        ->assertSessionHasNoErrors();

    $acta = Acta::sole();

    expect($acta->intervencion)->not->toBeNull()
        ->and($acta->intervencion->legajo_id)->toBe($legajo->id)
        ->and($acta->intervencion->tipo->nombre)->toBe('Acta telefónica')
        ->and($acta->intervencion->detalle)->toBe('Llamado de seguimiento.');

    $this->actingAs($admin)
        ->get(route('legajos.show', $legajo))
        ->assertInertia(fn (Assert $page) => $page
            ->where('intervenciones.0.acta.etiqueta', 'Acta telefónica')
            ->missing('actas')
            ->missing('tiposActa'));
});

test('a quick acta linked to a legajo has a complete read-only view from the legajo and edit screens', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaActa($admin, 'Luz', 'Pérez', '45111222');

    $this->actingAs($admin)
        ->post(route('actas.store', 'inicial'), [
            'fecha' => '2026-11-20',
            'compareciente_nombre' => 'Gómez, Marta',
            'compareciente_dni' => '22333444',
            'nino_nombre' => 'Gómez, Luz',
            'relato' => 'Solicita orientación.',
        ])
        ->assertSessionHasNoErrors();

    $acta = Acta::sole();

    $this->actingAs($admin)
        ->patch(route('actas.vincular', $acta), ['legajo_id' => $legajo->id])
        ->assertSessionHasNoErrors();

    $intervencion = $acta->fresh()->intervencion;

    $this->actingAs($admin)
        ->get(route('actas.vista', $acta))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('actas/vista')
            ->where('acta.titulo', 'Gómez, Marta')
            ->where('acta.secciones.1.titulo', 'Compareciente')
            ->where('acta.secciones.1.campos.1', ['label' => 'DNI', 'valor' => '22333444'])
            ->where('acta.secciones.3.campos.0.valor', 'Solicita orientación.'));

    $this->actingAs($admin)
        ->get(route('legajos.show', $legajo))
        ->assertInertia(fn (Assert $page) => $page
            ->where('intervenciones.0.acta.vista_url', route('actas.vista', $acta)));

    $this->actingAs($admin)
        ->get(route('legajos.intervenciones.edit', [$legajo, $intervencion]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('acta.vista_url', route('actas.vista', $acta)));
});

test('the home shows the quick acta shortcut only to roles that can use it', function () {
    $this->actingAs(User::factory()->create(['rol_id' => 5]))
        ->get(route('home'))
        ->assertOk()
        ->assertSee('id="home-quick-acta-orbital"', false);

    $this->actingAs(User::factory()->create(['rol_id' => 3]))
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('id="home-quick-acta-orbital"', false);
});

test('the unlinked actas section lists only actas without legajo, with their readable content', function () {
    $admin = User::factory()->create(['rol_id' => 1, 'name' => 'Lic. Paula Díaz']);
    $legajo = crearLegajoParaActa($admin, 'Luz', 'Pérez', '45111222');

    $this->actingAs($admin)->post(route('actas.store', 'inicial'), [
        'fecha' => '2026-11-20',
        'compareciente_nombre' => 'Gómez, Marta',
        'compareciente_dni' => '22333444',
        'nino_nombre' => 'Gómez, Luz',
        'relato' => 'Solicita orientación.',
    ])->assertSessionHasNoErrors();
    $this->actingAs($admin)->post(route('actas.store', 'citacion'), ['compareciente_nombre' => 'Ruiz, Ana'])->assertSessionHasNoErrors();
    $this->actingAs($admin)->post(route('actas.store', 'telefonica'), ['legajo_id' => $legajo->id])->assertSessionHasNoErrors();

    $this->actingAs($admin)
        ->get(route('actas.pendientes'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('actas/pendientes')
            ->where('total', 2)
            ->has('actas.data', 2)
            ->where('actas.data.1.titulo', 'Gómez, Marta')
            ->where('actas.data.1.secciones.0.titulo', 'Datos')
            ->where('actas.data.1.secciones.0.campos.0', ['label' => 'Fecha', 'valor' => '20/11/2026'])
            ->where('actas.data.1.secciones.1.titulo', 'Compareciente')
            ->where('actas.data.1.secciones.1.campos', [
                ['label' => 'Nombre', 'valor' => 'Gómez, Marta'],
                ['label' => 'DNI', 'valor' => '22333444'],
            ])
            ->where('actas.data.1.secciones.3', [
                'titulo' => 'Manifestación / relato',
                'campos' => [['label' => 'Manifestación / relato', 'valor' => 'Solicita orientación.']],
                'texto' => true,
            ]));

    $this->actingAs($admin)
        ->get(route('actas.index'))
        ->assertInertia(fn (Assert $page) => $page->where('sinLegajoTotal', 2));
});

test('unlinked actas can be filtered by name, DNI and type', function () {
    $admin = User::factory()->create(['rol_id' => 1]);

    $this->actingAs($admin)->post(route('actas.store', 'inicial'), ['compareciente_nombre' => 'Gómez, Marta', 'compareciente_dni' => '22333444']);
    $this->actingAs($admin)->post(route('actas.store', 'citacion'), ['compareciente_nombre' => 'Ruiz, Ana', 'compareciente_dni' => '30999888']);

    $titulos = fn (array $consulta) => collect(
        $this->actingAs($admin)->get(route('actas.pendientes', $consulta))->viewData('page')['props']['actas']['data']
    )->pluck('titulo')->all();

    expect($titulos(['buscar' => 'Ruiz']))->toBe(['Ruiz, Ana'])
        ->and($titulos(['buscar' => '22.333']))->toBe(['Gómez, Marta'])
        ->and($titulos(['tipo' => 'citacion']))->toBe(['Ruiz, Ana'])
        ->and($titulos(['tipo' => 'no-existe']))->toHaveCount(2);
});

test('the actas menu entry is shared only with roles that can use actas', function () {
    $this->actingAs(User::factory()->create(['rol_id' => 5]))
        ->get(route('actas.pendientes'))
        ->assertInertia(fn (Assert $page) => $page->where('can.accessActas', true));

    $this->actingAs(User::factory()->create(['rol_id' => 3]))
        ->get(route('actas.pendientes'))
        ->assertForbidden();
});

test('actas pages use the frontend layout with Actas marked in its menu', function () {
    $admin = User::factory()->create(['rol_id' => 1]);

    foreach ([route('actas.index'), route('actas.pendientes'), route('actas.create', 'inicial')] as $url) {
        $this->actingAs($admin)
            ->get($url)
            ->assertOk()
            ->assertSee('id="site-shell"', false)
            ->assertSee('<a href="'.route('actas.index').'" class="site-sidebar-link is-active"', false);
    }
});

test('the barrio and school of an acta must be selected from loaded catalogs', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $provincia = DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $sanNicolas = DB::table('localidades')->insertGetId(['nombre' => 'San Nicolás de los Arroyos', 'provincia_id' => $provincia, 'codigo_postal' => 2900]);
    $otra = DB::table('localidades')->insertGetId(['nombre' => 'Ramallo', 'provincia_id' => $provincia, 'codigo_postal' => 2915]);
    DB::table('barrios')->insert([
        ['nombre' => 'Centro', 'localidad_id' => $sanNicolas],
        ['nombre' => 'Villa Pulmón', 'localidad_id' => $sanNicolas],
        ['nombre' => 'Barrio de Ramallo', 'localidad_id' => $otra],
    ]);
    Escuela::factory()->create(['nombre' => 'Escuela Activa']);
    Escuela::factory()->create(['nombre' => 'Escuela Inactiva', 'activo' => false]);

    $this->actingAs($admin)
        ->get(route('actas.create', 'inicial'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('barrios', ['Centro', 'Villa Pulmón'])
            ->where('escuelas', ['Escuela Activa', 'Escuela Inactiva']));

    foreach (['Centrito', 'Barrio de Ramallo'] as $invalido) {
        $this->actingAs($admin)
            ->post(route('actas.store', 'inicial'), ['compareciente_barrio' => $invalido])
            ->assertSessionHasErrors(['compareciente_barrio' => 'Elegí un barrio de la lista.']);
    }

    $this->actingAs($admin)
        ->post(route('actas.store', 'inicial'), ['nino_escuela' => 'Escuela Inventada'])
        ->assertSessionHasErrors(['nino_escuela' => 'Elegí una escuela de la lista.']);

    $this->actingAs($admin)
        ->post(route('actas.store', 'auh_informe_relacion'), ['responsable' => ['barrio' => 'Inventado']])
        ->assertSessionHasErrors(['responsable.barrio' => 'Elegí un barrio de la lista.']);

    $this->actingAs($admin)
        ->post(route('actas.store', 'inicial'), ['compareciente_barrio' => 'Villa Pulmón'])
        ->assertSessionHasNoErrors();

    $this->actingAs($admin)
        ->post(route('actas.store', 'inicial'), ['nino_escuela' => 'Escuela Inactiva'])
        ->assertSessionHasNoErrors();

    expect(Acta::query()->oldest('id')->firstOrFail()->datos['compareciente_barrio'])->toBe('Villa Pulmón')
        ->and(Acta::query()->latest('id')->firstOrFail()->datos['nino_escuela'])->toBe('Escuela Inactiva');
});

function crearLegajoParaActa(User $usuario, string $nombre, string $apellido, string $dni): Legajo
{
    $provincia = DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $localidad = DB::table('localidades')->insertGetId(['nombre' => 'San Nicolás', 'provincia_id' => $provincia, 'codigo_postal' => 2900]);
    $persona = Persona::create([
        'nombre' => $nombre,
        'apellido' => $apellido,
        'dni' => $dni,
        'fecha_nacimiento' => '2014-01-01',
        'genero' => 'F',
        'localidad_id' => $localidad,
        'activo' => true,
    ]);

    return Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'ACT-'.uniqid(),
        'fecha_apertura' => today(),
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Escuela',
        'estado' => 'Abierto',
        'creado_por' => $usuario->id,
    ]);
}

/**
 * @return array{0: string, 1: int}
 */
function documentoDeActa(Acta $acta): array
{
    $zip = new ZipArchive;
    $zip->open(Storage::disk('local')->path($acta->ruta_archivo));
    $xml = (string) $zip->getFromName('word/document.xml');
    $imagenes = collect(range(0, $zip->numFiles - 1))
        ->filter(fn (int $i) => str_starts_with((string) $zip->getNameIndex($i), 'word/media/') && str_ends_with((string) $zip->getNameIndex($i), '.png'))
        ->count();
    $zip->close();

    expect(simplexml_load_string($xml))->not->toBeFalse('El acta generada no es un XML válido.');

    return [$xml, $imagenes];
}

function firmaActaDePrueba(): string
{
    $imagen = imagecreatetruecolor(320, 80);
    imagefill($imagen, 0, 0, imagecolorallocate($imagen, 255, 255, 255));
    imageline($imagen, 10, 60, 300, 20, imagecolorallocate($imagen, 44, 74, 94));
    ob_start();
    imagepng($imagen);

    return 'data:image/png;base64,'.base64_encode((string) ob_get_clean());
}

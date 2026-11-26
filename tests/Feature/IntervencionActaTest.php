<?php

use App\Models\Acta;
use App\Models\Intervencion;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\TipoIntervencion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
    ]);
    Storage::fake('local');
    $this->travelTo('2026-11-24 10:00:00');

    $this->admin = User::factory()->create(['rol_id' => 1, 'name' => 'Admin']);
    $this->profesional = User::factory()->create(['rol_id' => 5, 'name' => 'Lic. Paula Díaz']);
    $this->legajo = crearLegajoParaIntervencionConActa($this->admin);
});

/**
 * @param  array<string, mixed>  $extra
 * @return array<string, mixed>
 */
function intervencionDeTipo(string $tipo, array $extra = []): array
{
    return [
        'fecha_intervencion' => '2026-11-24',
        'hora_intervencion' => '09:30',
        'tipo_intervencion_id' => TipoIntervencion::where('nombre', $tipo)->value('id'),
        'profesional_id' => test()->profesional->id,
        ...$extra,
    ];
}

test('choosing an acta type registers the intervention with its acta instead of detail and signatures', function () {
    $this->actingAs($this->admin)
        ->post(route('legajos.intervenciones.store', $this->legajo), intervencionDeTipo('Acta inicial', [
            'acta' => [
                'compareciente_nombre' => 'Gómez, Marta',
                'relato' => 'Se presenta por la situación escolar.',
                'estrategias' => 'Articular con la escuela.',
                'firmas' => [
                    'compareciente' => ['nombre' => 'Marta Gómez', 'dibujo' => firmaIntervencionDePrueba()],
                    'equipo' => ['nombre' => 'Lic. Paula Díaz', 'dibujo' => firmaIntervencionDePrueba()],
                ],
            ],
        ]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('legajos.show', $this->legajo));

    $intervencion = Intervencion::sole();
    $acta = Acta::sole();

    expect($intervencion->detalle)->toBe("Se presenta por la situación escolar.\n\nEstrategias a seguir: Articular con la escuela.")
        ->and($intervencion->firma_nombre)->toBe('Lic. Paula Díaz')
        ->and($intervencion->firmada_en)->not->toBeNull()
        ->and($acta->intervencion_id)->toBe($intervencion->id)
        ->and($acta->legajo_id)->toBe($this->legajo->id)
        ->and($acta->firmado)->toBeTrue()
        ->and($acta->fecha->toDateString())->toBe('2026-11-24')
        ->and($acta->datos['hora'])->toBe('09:30');
    Storage::disk('local')->assertExists($acta->ruta_archivo);

    $this->actingAs($this->admin)
        ->get(route('legajos.show', $this->legajo))
        ->assertInertia(fn (Assert $page) => $page
            ->where('intervenciones.0.tipo', 'Acta inicial')
            ->where('intervenciones.0.acta.etiqueta', 'Acta inicial')
            ->where('intervenciones.0.acta.firmado', true)
            ->missing('actas'));
});

test('an acta without a story gets a short summary as the intervention detail', function () {
    $this->actingAs($this->admin)
        ->post(route('legajos.intervenciones.store', $this->legajo), intervencionDeTipo('Citación', [
            'acta' => ['citacion_dia' => '2026-11-27', 'citacion_horario' => '9 a 11 hs'],
        ]))
        ->assertSessionHasNoErrors();

    expect(Intervencion::sole()->detalle)->toBe('Citación para presentarse el 27/11/2026 en el horario de 9 a 11 hs.')
        ->and(Acta::sole()->firmado)->toBeFalse();
});

test('only the acta, notice and mail types are offered; AUH forms are not intervention types', function () {
    $visibles = TipoIntervencion::where('activo', true)->orderBy('nombre')->pluck('nombre')->all();

    expect($visibles)->toBe([
        'Acta de visita domiciliaria',
        'Acta inicial',
        'Acta telefónica',
        'Citación',
        'Constancia escolar',
        'Mail',
        'Recepción de oficio',
        'Respuesta de oficio',
    ]);

    $this->actingAs($this->admin)
        ->post(route('legajos.intervenciones.store', $this->legajo), intervencionDeTipo('AUH · Declaración jurada', [
            'acta' => ['responsable' => ['nombre' => 'Pérez, Ana']],
        ]))
        ->assertSessionHasErrors(['tipo_intervencion_id' => 'El tipo de intervención seleccionado no es válido o está inactivo.']);

    expect(Intervencion::count())->toBe(0)->and(Acta::count())->toBe(0);
});

test('an existing intervention with a hidden type can still be edited', function () {
    $oculto = TipoIntervencion::create(['nombre' => 'Entrevista', 'activo' => false]);
    $vieja = Intervencion::create([
        'legajo_id' => $this->legajo->id,
        'tipo_intervencion_id' => $oculto->id,
        'profesional_id' => $this->profesional->id,
        'fecha_intervencion' => '2026-11-20',
        'detalle' => 'Entrevista.',
        'firma_nombre' => 'Admin',
        'firma_aclaracion' => 'Admin',
    ]);

    $this->actingAs($this->admin)
        ->get(route('legajos.intervenciones.edit', [$this->legajo, $vieja]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tipos', fn ($tipos) => collect($tipos)->contains('nombre', 'Entrevista')));

    $this->actingAs($this->admin)
        ->put(route('legajos.intervenciones.update', [$this->legajo, $vieja]), intervencionDeTipo('Entrevista', [
            'detalle' => 'Entrevista corregida.',
            'firma_nombre' => 'Admin',
            'firma_aclaracion' => 'Admin',
        ]))
        ->assertSessionHasNoErrors();

    expect($vieja->fresh()->detalle)->toBe('Entrevista corregida.');

    $this->actingAs($this->admin)
        ->get(route('legajos.intervenciones.create', $this->legajo))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tipos', fn ($tipos) => ! collect($tipos)->contains('nombre', 'Entrevista')));
});

test('acta interventions validate their signatures', function () {
    $this->actingAs($this->admin)
        ->post(route('legajos.intervenciones.store', $this->legajo), intervencionDeTipo('Acta de visita domiciliaria', [
            'acta' => ['firmas' => ['entrevistado' => ['nombre' => '', 'dibujo' => firmaIntervencionDePrueba()]]],
        ]))
        ->assertSessionHasErrors([
            'acta.firmas.entrevistado.nombre' => 'Ingresá la aclaración de: Entrevistado/a.',
        ]);

    expect(Intervencion::count())->toBe(0)->and(Acta::count())->toBe(0);
});

test('an acta can also be registered in the legajos of linked relatives, each with its own copy', function () {
    $hermano = Persona::create([
        'nombre' => 'Tomás',
        'apellido' => 'Pérez',
        'dni' => '47111222',
        'fecha_nacimiento' => '2016-01-01',
        'genero' => 'M',
        'localidad_id' => $this->legajo->persona->localidad_id,
        'activo' => true,
    ]);
    $legajoHermano = Legajo::create([
        'persona_id' => $hermano->id,
        'numero_legajo' => 'HER-'.uniqid(),
        'fecha_apertura' => '2026-10-01',
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Escuela',
        'estado' => 'Abierto',
        'creado_por' => $this->admin->id,
    ]);
    $ajeno = crearLegajoParaIntervencionConActa($this->admin);
    $this->actingAs($this->admin)->post(route('legajos.vinculos.store', $this->legajo), [
        'persona_relacionada_id' => $hermano->id,
        'tipo_vinculo' => 'hermano/a',
    ])->assertSessionHasNoErrors();

    $this->actingAs($this->admin)
        ->post(route('legajos.intervenciones.store', $this->legajo), intervencionDeTipo('Acta inicial', [
            'acta' => [
                'relato' => 'Entrevista con la familia.',
                'firmas' => ['equipo' => ['nombre' => 'Lic. Paula Díaz', 'dibujo' => firmaIntervencionDePrueba()]],
            ],
            'legajos_destino_ids' => [$legajoHermano->id, $ajeno->id],
        ]))
        ->assertSessionHasErrors(['legajos_destino_ids' => 'Solo podés copiar la intervención a legajos vinculados y habilitados.']);

    expect(Intervencion::count())->toBe(0);

    $this->actingAs($this->admin)
        ->post(route('legajos.intervenciones.store', $this->legajo), intervencionDeTipo('Acta inicial', [
            'acta' => [
                'relato' => 'Entrevista con la familia.',
                'firmas' => ['equipo' => ['nombre' => 'Lic. Paula Díaz', 'dibujo' => firmaIntervencionDePrueba()]],
            ],
            'legajos_destino_ids' => [$legajoHermano->id],
        ]))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Intervención registrada con su acta inicial y copiada en los legajos de los familiares seleccionados.');

    $original = Intervencion::where('legajo_id', $this->legajo->id)->sole();
    $copia = Intervencion::where('legajo_id', $legajoHermano->id)->sole();

    expect($copia->intervencion_origen_id)->toBe($original->id)
        ->and($copia->detalle)->toBe('Entrevista con la familia.')
        ->and($copia->firma_nombre)->toBe('Lic. Paula Díaz')
        ->and(Acta::count())->toBe(2)
        ->and($original->acta->legajo_id)->toBe($this->legajo->id)
        ->and($copia->acta->legajo_id)->toBe($legajoHermano->id)
        ->and($copia->acta->ruta_archivo)->not->toBe($original->acta->ruta_archivo);

    foreach ([[$original, $this->legajo], [$copia, $legajoHermano]] as [$intervencion, $legajo]) {
        $zip = new ZipArchive;
        $zip->open(Storage::disk('local')->path($intervencion->acta->ruta_archivo));
        expect($zip->getFromName('word/document.xml'))->toContain('Legajo n°: '.$legajo->numero_legajo);
        $zip->close();
    }
});

test('other intervention types still require their own detail and signature', function () {
    $this->actingAs($this->admin)
        ->post(route('legajos.intervenciones.store', $this->legajo), intervencionDeTipo('Mail'))
        ->assertSessionHasErrors(['detalle', 'firma_nombre', 'firma_responsable_digital'])
        ->assertSessionDoesntHaveErrors(['tipo_intervencion_id']);
});

test('an intervention keeps its acta type when edited, and acta types are only for new interventions', function () {
    $this->actingAs($this->admin)->post(route('legajos.intervenciones.store', $this->legajo), intervencionDeTipo('Acta telefónica', [
        'acta' => ['relato' => 'Llamado de seguimiento.'],
    ]))->assertSessionHasNoErrors();
    $conActa = Intervencion::sole();

    $this->actingAs($this->admin)
        ->put(route('legajos.intervenciones.update', [$this->legajo, $conActa]), intervencionDeTipo('Mail', ['detalle' => 'Cambio']))
        ->assertSessionHasErrors(['tipo_intervencion_id' => 'Esta intervención tiene un acta: no se puede cambiar su tipo.']);

    $this->actingAs($this->admin)
        ->put(route('legajos.intervenciones.update', [$this->legajo, $conActa]), intervencionDeTipo('Acta telefónica', ['detalle' => 'Detalle corregido.']))
        ->assertSessionHasNoErrors();
    expect($conActa->fresh()->detalle)->toBe('Detalle corregido.');

    $comun = Intervencion::create([
        'legajo_id' => $this->legajo->id,
        'tipo_intervencion_id' => TipoIntervencion::where('nombre', 'Mail')->value('id'),
        'profesional_id' => $this->profesional->id,
        'fecha_intervencion' => '2026-11-20',
        'detalle' => 'Entrevista.',
        'firma_nombre' => 'Admin',
        'firma_aclaracion' => 'Admin',
    ]);

    $this->actingAs($this->admin)
        ->put(route('legajos.intervenciones.update', [$this->legajo, $comun]), intervencionDeTipo('Acta inicial', [
            'detalle' => 'Entrevista.',
            'firma_nombre' => 'Admin',
            'firma_aclaracion' => 'Admin',
        ]))
        ->assertSessionHasErrors(['tipo_intervencion_id' => 'Para hacer un acta, registrá una nueva intervención de ese tipo.']);

    $this->actingAs($this->admin)
        ->get(route('legajos.intervenciones.edit', [$this->legajo, $conActa]))
        ->assertInertia(fn (Assert $page) => $page->where('acta.etiqueta', 'Acta telefónica'));
});

test('the new intervention form knows which types open an acta, pre-filled for the legajo', function () {
    $this->actingAs($this->admin)
        ->get(route('legajos.intervenciones.create', $this->legajo))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('intervenciones/create')
            ->where('tipos', fn ($tipos) => collect($tipos)->firstWhere('nombre', 'Acta inicial')['acta'] === 'inicial'
                && collect($tipos)->firstWhere('nombre', 'Mail')['acta'] === null
                && ! collect($tipos)->contains('nombre', 'AUH · Declaración jurada'))
            ->where('actas.inicial.datos.nino_nombre', 'Pérez, Luz')
            ->where('actas.inicial.firmantes', ['compareciente' => 'Compareciente', 'equipo' => 'Equipo técnico'])
            ->missing('actas.auh_nota_elevacion'));
});

function crearLegajoParaIntervencionConActa(User $usuario): Legajo
{
    $provincia = DB::table('provincias')->where('nombre', 'Buenos Aires')->value('id')
        ?? DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $localidad = DB::table('localidades')->where('nombre', 'San Nicolás')->value('id')
        ?? DB::table('localidades')->insertGetId(['nombre' => 'San Nicolás', 'provincia_id' => $provincia, 'codigo_postal' => 2900]);
    $persona = Persona::create([
        'nombre' => 'Luz',
        'apellido' => 'Pérez',
        'dni' => '45'.random_int(100000, 999999),
        'fecha_nacimiento' => '2014-01-01',
        'genero' => 'F',
        'localidad_id' => $localidad,
        'activo' => true,
    ]);

    return Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'INT-'.uniqid(),
        'fecha_apertura' => '2026-10-01',
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Escuela',
        'estado' => 'Abierto',
        'creado_por' => $usuario->id,
    ]);
}

function firmaIntervencionDePrueba(): string
{
    $imagen = imagecreatetruecolor(320, 80);
    imagefill($imagen, 0, 0, imagecolorallocate($imagen, 255, 255, 255));
    imageline($imagen, 10, 60, 300, 20, imagecolorallocate($imagen, 44, 74, 94));
    ob_start();
    imagepng($imagen);

    return 'data:image/png;base64,'.base64_encode((string) ob_get_clean());
}

test('an unsigned acta opens in its sheet when editing and saving regenerates its Word', function () {
    $this->actingAs($this->admin)
        ->post(route('legajos.intervenciones.store', $this->legajo), intervencionDeTipo('Acta inicial', [
            'acta' => ['compareciente_nombre' => 'Gómez, Marta', 'relato' => 'Relato original.'],
        ]))
        ->assertSessionHasNoErrors();

    $intervencion = Intervencion::sole();
    $acta = Acta::sole();
    $archivoAnterior = $acta->ruta_archivo;

    $this->actingAs($this->admin)
        ->get(route('legajos.intervenciones.edit', [$this->legajo, $intervencion]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('actaHoja.tipo', 'inicial')
            ->where('actaHoja.editable', true)
            ->where('actaHoja.datos.compareciente_nombre', 'Gómez, Marta')
            ->where('actaHoja.datos.nino_escuela', ''));

    $this->actingAs($this->admin)
        ->put(route('legajos.intervenciones.update', [$this->legajo, $intervencion]), intervencionDeTipo('Acta inicial', [
            'acta' => [
                'compareciente_nombre' => 'Gómez, Marta',
                'relato' => 'Relato corregido.',
                'firmas' => ['equipo' => ['nombre' => 'Lic. Paula Díaz', 'dibujo' => firmaIntervencionDePrueba()]],
            ],
        ]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('legajos.show', $this->legajo));

    $acta->refresh();

    expect($acta->datos['relato'])->toBe('Relato corregido.')
        ->and($acta->ruta_archivo)->not->toBe($archivoAnterior)
        ->and($acta->firmas)->toHaveCount(1)
        ->and($intervencion->refresh()->detalle)->toBe('Relato corregido.')
        ->and($intervencion->firma_nombre)->toBe('Lic. Paula Díaz');
    Storage::disk('local')->assertExists($acta->ruta_archivo);
    Storage::disk('local')->assertMissing($archivoAnterior);
});

test('a signed acta is shown read-only when editing and its Word is not regenerated', function () {
    $this->actingAs($this->admin)
        ->post(route('legajos.intervenciones.store', $this->legajo), intervencionDeTipo('Acta inicial', [
            'acta' => [
                'compareciente_nombre' => 'Gómez, Marta',
                'relato' => 'Relato firmado.',
                'firmas' => ['equipo' => ['nombre' => 'Lic. Paula Díaz', 'dibujo' => firmaIntervencionDePrueba()]],
            ],
        ]))
        ->assertSessionHasNoErrors();

    $intervencion = Intervencion::sole();
    $acta = Acta::sole();
    $archivo = $acta->ruta_archivo;

    $this->actingAs($this->admin)
        ->get(route('legajos.intervenciones.edit', [$this->legajo, $intervencion]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('actaHoja.editable', false)
            ->where('actaHoja.firmados', ['equipo'])
            ->where('actaHoja.firmas_imagenes.equipo', fn (string $imagen) => str_starts_with($imagen, 'data:image/png;base64,')));

    $this->actingAs($this->admin)
        ->put(route('legajos.intervenciones.update', [$this->legajo, $intervencion]), intervencionDeTipo('Acta inicial', [
            'acta' => ['compareciente_nombre' => 'Otra persona', 'relato' => 'Intento de cambio.'],
        ]))
        ->assertSessionHasNoErrors();

    expect($acta->refresh()->ruta_archivo)->toBe($archivo)
        ->and($acta->datos['relato'])->toBe('Relato firmado.')
        ->and($intervencion->refresh()->detalle)->toBe('Relato firmado.');
});

test('the acta sheet can be previewed from the legajo before downloading it', function () {
    $this->actingAs($this->admin)
        ->post(route('legajos.intervenciones.store', $this->legajo), intervencionDeTipo('Acta telefónica', [
            'acta' => ['compareciente_nombre' => 'Gómez, Marta', 'relato' => 'Llamado de seguimiento.'],
        ]))
        ->assertSessionHasNoErrors();

    $acta = Acta::sole();

    $this->actingAs($this->admin)
        ->get(route('legajos.show', $this->legajo))
        ->assertInertia(fn (Assert $page) => $page
            ->where('intervenciones.0.acta.hoja_url', route('actas.hoja', $acta)));

    $this->actingAs($this->admin)
        ->getJson(route('actas.hoja', $acta))
        ->assertOk()
        ->assertJsonPath('tipo', 'telefonica')
        ->assertJsonPath('datos.compareciente_nombre', 'Gómez, Marta')
        ->assertJsonPath('datos.relato', 'Llamado de seguimiento.')
        ->assertJsonPath('legajo_numero', $this->legajo->numero_legajo)
        ->assertJsonPath('descarga_url', route('actas.descargar', $acta));
});

test('users without acta access cannot preview an acta sheet', function () {
    DB::table('roles')->insert(['id' => 9, 'nombre' => 'consulta', 'descripcion' => 'Solo lectura', 'creado_en' => now()]);
    $acta = Acta::factory()->create(['legajo_id' => $this->legajo->id]);

    $this->actingAs(User::factory()->create(['rol_id' => 9]))
        ->getJson(route('actas.hoja', $acta))
        ->assertForbidden();
});

test('a signature already registered cannot be changed when editing the intervention', function () {
    $tipo = TipoIntervencion::firstOrCreate(['nombre' => 'Entrevista'], ['activo' => true]);
    Storage::disk('local')->put('firmas/responsable.png', 'firma original');
    Storage::disk('local')->put('firmas/entrevistado.png', 'firma entrevistado');
    $intervencion = Intervencion::create([
        'legajo_id' => $this->legajo->id,
        'tipo_intervencion_id' => $tipo->id,
        'profesional_id' => $this->profesional->id,
        'fecha_intervencion' => '2026-11-20',
        'detalle' => 'Entrevista.',
        'firma_nombre' => 'Lic. Paula Díaz',
        'firma_aclaracion' => 'Psicóloga',
        'firma_archivo_path' => 'firmas/responsable.png',
        'firma_entrevistado_nombre' => 'Marta Gómez',
        'firma_entrevistado_aclaracion' => 'Madre',
        'firma_entrevistado_archivo_path' => 'firmas/entrevistado.png',
    ]);

    $this->actingAs($this->admin)
        ->put(route('legajos.intervenciones.update', [$this->legajo, $intervencion]), intervencionDeTipo('Entrevista', [
            'detalle' => 'Entrevista corregida.',
            'firma_nombre' => 'Otra persona',
            'firma_aclaracion' => 'Otro cargo',
            'firma_responsable_dibujo' => firmaIntervencionDePrueba(),
            'firma_entrevistado_nombre' => '',
            'firma_entrevistado_aclaracion' => '',
        ]))
        ->assertSessionHasNoErrors();

    $intervencion->refresh();

    expect($intervencion->detalle)->toBe('Entrevista corregida.')
        ->and($intervencion->firma_nombre)->toBe('Lic. Paula Díaz')
        ->and($intervencion->firma_aclaracion)->toBe('Psicóloga')
        ->and($intervencion->firma_archivo_path)->toBe('firmas/responsable.png')
        ->and($intervencion->firma_entrevistado_nombre)->toBe('Marta Gómez')
        ->and($intervencion->firma_entrevistado_archivo_path)->toBe('firmas/entrevistado.png');
    Storage::disk('local')->assertExists(['firmas/responsable.png', 'firmas/entrevistado.png']);
});

test('editing an intervention does not ask to sign again', function () {
    $tipo = TipoIntervencion::firstOrCreate(['nombre' => 'Entrevista'], ['activo' => true]);
    $intervencion = Intervencion::create([
        'legajo_id' => $this->legajo->id,
        'tipo_intervencion_id' => $tipo->id,
        'profesional_id' => $this->profesional->id,
        'fecha_intervencion' => '2026-11-20',
        'detalle' => 'Entrevista.',
        'firma_nombre' => 'Lic. Paula Díaz',
        'firma_aclaracion' => 'Psicóloga',
        'firma_entrevistado_nombre' => 'Marta Gómez',
        'firma_entrevistado_aclaracion' => 'Madre',
    ]);

    $this->actingAs($this->admin)
        ->put(route('legajos.intervenciones.update', [$this->legajo, $intervencion]), intervencionDeTipo('Entrevista', [
            'detalle' => 'Entrevista corregida.',
            'firma_nombre' => 'Lic. Paula Díaz',
            'firma_aclaracion' => 'Psicóloga',
            'firma_entrevistado_nombre' => 'Marta Gómez',
            'firma_entrevistado_aclaracion' => 'Madre',
        ]))
        ->assertSessionHasNoErrors();

    expect($intervencion->refresh()->detalle)->toBe('Entrevista corregida.')
        ->and($intervencion->firma_entrevistado_nombre)->toBe('Marta Gómez');
});

test('the acta of an intervention has the chosen professional as its responsible', function () {
    $this->actingAs($this->admin)
        ->post(route('legajos.intervenciones.store', $this->legajo), intervencionDeTipo('Acta inicial', [
            'acta' => [
                'operador' => 'Admin',
                'compareciente_nombre' => 'Gómez, Marta',
                'firmas' => ['equipo' => ['nombre' => 'Admin', 'dibujo' => firmaIntervencionDePrueba()]],
            ],
        ]))
        ->assertSessionHasNoErrors();

    $acta = Acta::sole();

    expect($acta->datos['operador'])->toBe('Lic. Paula Díaz')
        ->and($acta->firmas[0]['nombre'])->toBe('Lic. Paula Díaz')
        ->and(Intervencion::sole()->firma_nombre)->toBe('Lic. Paula Díaz');
});

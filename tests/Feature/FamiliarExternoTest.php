<?php

use App\Models\Legajo;
use App\Models\Persona;
use App\Models\User;
use App\Models\VinculoFamiliar;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 3, 'nombre' => 'consulta', 'descripcion' => 'Solo lectura', 'creado_en' => now()],
    ]);

    $provincia = DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $this->localidad = DB::table('localidades')->insertGetId([
        'nombre' => 'San Nicolás de los Arroyos',
        'provincia_id' => $provincia,
        'codigo_postal' => 2900,
    ]);
    DB::table('barrios')->insert(['nombre' => 'Villa Pulmón', 'localidad_id' => $this->localidad]);

    $this->admin = User::factory()->create(['rol_id' => 1]);
    $this->legajo = legajoDeMenor($this, 'Luz', '45111222');
});

function legajoDeMenor($test, string $nombre, string $dni): Legajo
{
    $persona = Persona::create([
        'nombre' => $nombre,
        'apellido' => 'Pérez',
        'dni' => $dni,
        'fecha_nacimiento' => '2014-01-01',
        'genero' => 'F',
        'localidad_id' => $test->localidad,
        'activo' => true,
    ]);

    return Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'FAM-'.$dni,
        'fecha_apertura' => today(),
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Escuela',
        'estado' => 'Abierto',
        'creado_por' => $test->admin->id,
    ]);
}

/**
 * @param  array<string, mixed>  $familiar
 */
function registrarFamiliar($test, Legajo $legajo, array $familiar, string $tipo = 'madre')
{
    return $test->actingAs($test->admin)->post(route('legajos.vinculos.store', $legajo), [
        'tipo_vinculo' => $tipo,
        'familiar' => $familiar,
    ]);
}

test('a relative who is not in the system can be registered and linked to the legajo', function () {
    registrarFamiliar($this, $this->legajo, [
        'apellido' => 'Pérez',
        'nombre' => 'Ana',
        'dni' => '22111222',
        'telefono' => '3364 111111',
        'direccion_calle' => 'Mitre',
        'direccion_numero' => '123',
        'barrio' => 'Villa Pulmón',
    ])->assertSessionHasNoErrors()->assertSessionHas('status', 'Familiar registrado y vinculado al legajo.');

    $madre = Persona::where('dni', '22111222')->sole();

    expect($madre->legajos()->exists())->toBeFalse()
        ->and($madre->fecha_nacimiento)->toBeNull()
        ->and($madre->edad)->toBeNull()
        ->and($madre->barrio->nombre)->toBe('Villa Pulmón')
        ->and($madre->localidad_id)->toBe($this->localidad)
        ->and(VinculoFamiliar::sole()->tipo_vinculo)->toBe('madre');

    $this->actingAs($this->admin)
        ->get(route('legajos.show', $this->legajo))
        ->assertInertia(fn (Assert $page) => $page
            ->where('vinculos.0.tipo', 'madre')
            ->where('vinculos.0.persona.nombre', 'Pérez, Ana')
            ->where('vinculos.0.persona.externo', true)
            ->where('vinculos.0.persona.legajo', null)
            ->where('vinculos.0.persona.telefono', '3364 111111')
            ->where('vinculos.0.persona.barrio', 'Villa Pulmón')
            ->where('tiposVinculo', fn ($tipos) => collect($tipos)->contains('otro responsable'))
            ->where('barrios', ['Villa Pulmón']));
});

test('registering a relative requires a name and a known link, and does not duplicate a DNI', function () {
    registrarFamiliar($this, $this->legajo, ['apellido' => '', 'nombre' => ''], 'vecino')
        ->assertSessionHasErrors([
            'familiar.apellido' => 'Ingresá el apellido del familiar.',
            'familiar.nombre' => 'Ingresá el nombre del familiar.',
            'tipo_vinculo' => 'El tipo de vínculo no es válido.',
        ]);

    registrarFamiliar($this, $this->legajo, ['apellido' => 'Gómez', 'nombre' => 'Ana', 'dni' => '45111222'])
        ->assertSessionHasErrors(['familiar.dni' => 'Ya hay una persona registrada con ese DNI: usala desde «Ya está en el sistema».']);

    registrarFamiliar($this, $this->legajo, ['apellido' => 'Gómez', 'nombre' => 'Ana', 'barrio' => 'Inventado'])
        ->assertSessionHasErrors(['familiar.barrio' => 'Elegí un barrio de la lista.']);

    expect(Persona::count())->toBe(1);
});

test('a registered relative is reused for a sibling legajo instead of being loaded twice', function () {
    registrarFamiliar($this, $this->legajo, ['apellido' => 'Pérez', 'nombre' => 'Ana', 'dni' => '22111222'])->assertSessionHasNoErrors();
    $madre = Persona::where('dni', '22111222')->sole();
    $hermano = legajoDeMenor($this, 'Tomás', '47111222');

    $this->actingAs($this->admin)
        ->getJson(route('actas.personas', ['q' => '22111']))
        ->assertJsonPath('0.nombre', 'Pérez, Ana')
        ->assertJsonPath('0.legajo', null);

    $this->actingAs($this->admin)
        ->post(route('legajos.vinculos.store', $hermano), ['persona_relacionada_id' => $madre->id, 'tipo_vinculo' => 'madre'])
        ->assertSessionHasNoErrors();

    expect(Persona::where('dni', '22111222')->count())->toBe(1)
        ->and(VinculoFamiliar::where('persona_relacionada_id', $madre->id)->count())->toBe(2);
});

test('the data of a registered relative can be updated, but not the data of someone with a legajo', function () {
    registrarFamiliar($this, $this->legajo, ['apellido' => 'Pérez', 'nombre' => 'Ana'])->assertSessionHasNoErrors();
    $vinculo = VinculoFamiliar::sole();

    $this->actingAs($this->admin)
        ->patch(route('legajos.vinculos.familiar', [$this->legajo, $vinculo]), [
            'apellido' => 'Pérez',
            'nombre' => 'Ana María',
            'dni' => '22111222',
            'fecha_nacimiento' => '1985-04-02',
            'telefono' => '3364 222222',
        ])
        ->assertSessionHasNoErrors();

    $madre = $vinculo->personaRelacionada->fresh();
    expect($madre->nombre)->toBe('Ana María')
        ->and($madre->dni)->toBe('22111222')
        ->and($madre->edad)->toBe($madre->fecha_nacimiento->age);

    $hermano = legajoDeMenor($this, 'Tomás', '47111222');
    $this->actingAs($this->admin)
        ->post(route('legajos.vinculos.store', $this->legajo), ['persona_relacionada_id' => $hermano->persona_id, 'tipo_vinculo' => 'hermano/a'])
        ->assertSessionHasNoErrors();
    $conLegajo = VinculoFamiliar::where('persona_relacionada_id', $hermano->persona_id)->sole();

    $this->actingAs($this->admin)
        ->patch(route('legajos.vinculos.familiar', [$this->legajo, $conLegajo]), ['apellido' => 'X', 'nombre' => 'Y'])
        ->assertStatus(422);
});

test('registered relatives can fill the responsible adult of an AUH form', function () {
    registrarFamiliar($this, $this->legajo, ['apellido' => 'Pérez', 'nombre' => 'Ana', 'dni' => '22111222', 'telefono' => '3364 111111'])
        ->assertSessionHasNoErrors();

    $this->actingAs($this->admin)
        ->get(route('legajos.show', $this->legajo))
        ->assertInertia(fn (Assert $page) => $page
            ->where('auh.formulario.personas.0.nombre', 'Pérez, Ana')
            ->where('auh.formulario.personas.0.dni', '22111222')
            ->where('auh.formulario.personas.0.telefono', '3364 111111'));
});

test('read-only users cannot register relatives', function () {
    $consulta = User::factory()->create(['rol_id' => 3]);

    $this->actingAs($consulta)
        ->post(route('legajos.vinculos.store', $this->legajo), ['tipo_vinculo' => 'madre', 'familiar' => ['apellido' => 'A', 'nombre' => 'B']])
        ->assertForbidden();

    expect(Persona::count())->toBe(1);
});

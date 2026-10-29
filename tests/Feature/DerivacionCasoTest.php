<?php

use App\Models\CasoResponsable;
use App\Models\DerivacionCaso;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\User;
use App\Notifications\DerivacionCasoNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 4, 'nombre' => 'coordinador', 'descripcion' => 'Supervisión', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
    ]);
});

function crearLegajoParaDerivacion(User $creador): Legajo
{
    $provincia = DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $localidad = DB::table('localidades')->insertGetId([
        'nombre' => 'San Nicolás de los Arroyos',
        'provincia_id' => $provincia,
        'codigo_postal' => 2900,
    ]);
    $persona = Persona::create([
        'nombre' => 'Ana',
        'apellido' => 'Derivada',
        'dni' => '30111222',
        'fecha_nacimiento' => '2010-01-01',
        'genero' => 'F',
        'localidad_id' => $localidad,
        'activo' => true,
    ]);

    return Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'DER-ANA-222',
        'fecha_apertura' => today(),
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Institución',
        'estado' => 'Abierto',
        'creado_por' => $creador->id,
    ]);
}

test('a professional can accept a legajo derivation and become responsible', function () {
    Notification::fake();

    $coordinador = User::factory()->create(['rol_id' => 4]);
    $profesional = User::factory()->create(['rol_id' => 5, 'profesion' => 'ts']);
    $legajo = crearLegajoParaDerivacion($coordinador);

    $this->actingAs($coordinador)
        ->post(route('legajos.derivaciones.store', $legajo), [
            'destinatario_id' => $profesional->id,
            'modo' => 'reemplazo',
            'motivo' => 'Seguimiento especializado',
            'detalle' => 'Revisar la situación integral.',
            'fecha_limite' => today()->addWeek()->toDateString(),
        ])
        ->assertRedirect();

    $derivacion = DerivacionCaso::firstOrFail();
    expect($derivacion->estado)->toBe(DerivacionCaso::ESTADO_PENDIENTE);
    Notification::assertSentTo($profesional, DerivacionCasoNotification::class, fn ($notification) => $notification->evento === 'derivacion_recibida');

    $conversacion = $derivacion->conversaciones()->firstOrFail();
    expect($conversacion->usuarios()->whereKey($coordinador->id)->exists())->toBeTrue()
        ->and($conversacion->usuarios()->whereKey($profesional->id)->exists())->toBeTrue()
        ->and($conversacion->mensajes()->where('usuario_id', $coordinador->id)->where('cuerpo', 'like', '%Nueva derivación%')->exists())->toBeTrue()
        ->and($conversacion->mensajes()->firstOrFail()->recursos()->where('tipo_recurso', 'legajo')->where('recurso_id', $legajo->id)->exists())->toBeTrue();

    $this->actingAs($profesional)
        ->patch(route('derivaciones.aceptar', $derivacion))
        ->assertRedirect();

    expect($derivacion->fresh()->estado)->toBe(DerivacionCaso::ESTADO_ACEPTADA)
        ->and(CasoResponsable::where('legajo_id', $legajo->id)->where('usuario_id', $profesional->id)->where('activo', true)->exists())->toBeTrue()
        ->and(CasoResponsable::where('legajo_id', $legajo->id)->where('usuario_id', $profesional->id)->firstOrFail()->usuario->profesion)->toBe('ts')
        ->and($legajo->fresh()->profesional_id)->toBe($profesional->id);

    Notification::assertSentTo($coordinador, DerivacionCasoNotification::class, fn ($notification) => $notification->evento === 'derivacion_aceptada');
});

test('accepting an additional derivation makes the recipient the principal responsible', function () {
    $coordinador = User::factory()->create(['rol_id' => 4]);
    $actual = User::factory()->create(['rol_id' => 5, 'profesion' => 'psicologo']);
    $nuevo = User::factory()->create(['rol_id' => 5, 'profesion' => 'ts']);
    $legajo = crearLegajoParaDerivacion($coordinador);
    $legajo->update(['profesional_id' => $actual->id]);

    CasoResponsable::create([
        'legajo_id' => $legajo->id,
        'usuario_id' => $actual->id,
        'asignado_por' => $coordinador->id,
        'es_principal' => true,
        'activo' => true,
        'asignado_en' => now(),
    ]);

    $this->actingAs($coordinador)
        ->post(route('legajos.derivaciones.store', $legajo), [
            'destinatario_id' => $nuevo->id,
            'modo' => DerivacionCaso::MODO_ADICIONAL,
            'motivo' => 'Intervención especializada',
        ])
        ->assertRedirect();

    $derivacion = DerivacionCaso::latest('id')->firstOrFail();

    $this->actingAs($nuevo)
        ->patch(route('derivaciones.aceptar', $derivacion))
        ->assertRedirect();

    expect($legajo->fresh()->profesional_id)->toBe($nuevo->id)
        ->and(CasoResponsable::where('legajo_id', $legajo->id)->where('usuario_id', $nuevo->id)->where('es_principal', true)->where('activo', true)->exists())->toBeTrue()
        ->and(CasoResponsable::where('legajo_id', $legajo->id)->where('usuario_id', $actual->id)->where('es_principal', false)->where('activo', true)->exists())->toBeTrue();
});

test('legajo detail exposes individual statistics and derivations', function () {
    $coordinador = User::factory()->create(['rol_id' => 4]);
    $profesional = User::factory()->create(['rol_id' => 5]);
    $legajo = crearLegajoParaDerivacion($coordinador);

    DerivacionCaso::create([
        'legajo_id' => $legajo->id,
        'solicitado_por' => $coordinador->id,
        'destinatario_id' => $profesional->id,
        'modo' => DerivacionCaso::MODO_ADICIONAL,
        'motivo' => 'Revisión',
    ]);

    $this->actingAs($coordinador)
        ->get(route('legajos.show', $legajo))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('legajos/show')
            ->where('estadisticas.derivaciones.total', 1)
            ->has('estadisticas.intervenciones')
            ->has('derivaciones', 1));
});

test('cannot add a 4th active responsible to a legajo via an additional derivation', function () {
    $coordinador = User::factory()->create(['rol_id' => 4]);
    $legajo = crearLegajoParaDerivacion($coordinador);

    foreach (User::factory()->count(3)->create(['rol_id' => 5]) as $responsable) {
        CasoResponsable::create([
            'legajo_id' => $legajo->id,
            'usuario_id' => $responsable->id,
            'activo' => true,
            'asignado_en' => now(),
        ]);
    }

    $nuevoProfesional = User::factory()->create(['rol_id' => 5]);

    $this->actingAs($coordinador)
        ->post(route('legajos.derivaciones.store', $legajo), [
            'destinatario_id' => $nuevoProfesional->id,
            'modo' => 'adicional',
            'motivo' => 'Revisión conjunta',
        ])
        ->assertInvalid(['destinatario_id']);

    expect(DerivacionCaso::count())->toBe(0);
});

test('accepting an additional derivation is blocked once the legajo already has the maximum of 3 active responsibles', function () {
    $coordinador = User::factory()->create(['rol_id' => 4]);
    $legajo = crearLegajoParaDerivacion($coordinador);
    $destinatario = User::factory()->create(['rol_id' => 5]);

    $derivacion = DerivacionCaso::create([
        'legajo_id' => $legajo->id,
        'solicitado_por' => $coordinador->id,
        'destinatario_id' => $destinatario->id,
        'modo' => DerivacionCaso::MODO_ADICIONAL,
        'motivo' => 'Revisión',
    ]);

    foreach (User::factory()->count(3)->create(['rol_id' => 5]) as $responsable) {
        CasoResponsable::create([
            'legajo_id' => $legajo->id,
            'usuario_id' => $responsable->id,
            'activo' => true,
            'asignado_en' => now(),
        ]);
    }

    $this->actingAs($destinatario)
        ->patch(route('derivaciones.aceptar', $derivacion))
        ->assertStatus(422);

    expect($derivacion->fresh()->estado)->toBe(DerivacionCaso::ESTADO_PENDIENTE)
        ->and(CasoResponsable::where('legajo_id', $legajo->id)->where('usuario_id', $destinatario->id)->where('activo', true)->exists())->toBeFalse();
});

test('only the recipient or a coordinator can accept a derivation', function () {
    $coordinador = User::factory()->create(['rol_id' => 4]);
    $profesional = User::factory()->create(['rol_id' => 5]);
    $otroProfesional = User::factory()->create(['rol_id' => 5]);
    $legajo = crearLegajoParaDerivacion($coordinador);
    $derivacion = DerivacionCaso::create([
        'legajo_id' => $legajo->id,
        'solicitado_por' => $coordinador->id,
        'destinatario_id' => $profesional->id,
        'modo' => DerivacionCaso::MODO_ADICIONAL,
        'motivo' => 'Revisión',
    ]);

    $this->actingAs($otroProfesional)
        ->patch(route('derivaciones.aceptar', $derivacion))
        ->assertForbidden();

    expect($derivacion->fresh()->estado)->toBe(DerivacionCaso::ESTADO_PENDIENTE);
});

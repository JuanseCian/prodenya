<?php

use App\Models\CasoResponsable;
use App\Models\Legajo;
use App\Models\MedidaAbrigo;
use App\Models\Persona;
use App\Models\User;
use App\Notifications\MedidaAbrigoVencimientoNotification;
use App\Services\MedidaAbrigoNotificationService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
    ]);
});

test('it notifies the assigned professional and active responsables when a measure is about to expire', function () {
    $profesional = User::factory()->create(['rol_id' => 5]);
    $responsable = User::factory()->create(['rol_id' => 2]);
    $legajo = crearLegajoParaVencimiento($profesional);
    CasoResponsable::create([
        'legajo_id' => $legajo->id,
        'usuario_id' => $responsable->id,
        'asignado_por' => $profesional->id,
        'activo' => true,
        'asignado_en' => now(),
    ]);
    $medida = MedidaAbrigo::create([
        'legajo_id' => $legajo->id,
        'ubicacion' => 'institucion',
        'duracion_dias' => 90,
        'fecha_inicio' => today()->subDays(83),
        'fecha_fin' => today()->addDays(7),
    ]);

    $avisadas = app(MedidaAbrigoNotificationService::class)->avisarVencimientosProximos();

    expect($avisadas)->toBe(1);

    foreach ([$profesional, $responsable] as $usuario) {
        expect($usuario->notifications()
            ->where('type', MedidaAbrigoVencimientoNotification::class)
            ->where('data->medida_abrigo_id', $medida->id)
            ->where('data->dias_restantes', 7)
            ->exists())->toBeTrue();
    }
});

test('it does not notify a measure that is not within an alert threshold', function () {
    $profesional = User::factory()->create(['rol_id' => 5]);
    $legajo = crearLegajoParaVencimiento($profesional);
    MedidaAbrigo::create([
        'legajo_id' => $legajo->id,
        'ubicacion' => 'institucion',
        'duracion_dias' => 90,
        'fecha_inicio' => today()->subDays(60),
        'fecha_fin' => today()->addDays(30),
    ]);

    $avisadas = app(MedidaAbrigoNotificationService::class)->avisarVencimientosProximos();

    expect($avisadas)->toBe(0)
        ->and($profesional->notifications()->count())->toBe(0);
});

function crearLegajoParaVencimiento(User $profesional): Legajo
{
    $provincia = DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $localidad = DB::table('localidades')->insertGetId([
        'nombre' => 'San Nicolás',
        'provincia_id' => $provincia,
        'codigo_postal' => 2900,
    ]);
    $persona = Persona::create([
        'nombre' => 'Ana',
        'apellido' => 'Vencimiento',
        'dni' => '30'.random_int(100000, 999999),
        'fecha_nacimiento' => '2010-01-01',
        'genero' => 'F',
        'localidad_id' => $localidad,
        'activo' => true,
    ]);

    return Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'VEN-'.$profesional->id.'-'.uniqid(),
        'fecha_apertura' => today(),
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Institución',
        'estado' => 'Abierto',
        'creado_por' => $profesional->id,
        'profesional_id' => $profesional->id,
    ]);
}

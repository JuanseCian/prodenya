<?php

use App\Models\Acta;
use App\Models\PuntajeMovimiento;
use App\Models\Tarea;
use App\Models\User;
use App\Services\PuntajeService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
        ['id' => 4, 'nombre' => 'coordinador', 'descripcion' => 'Supervisión', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
        ['id' => 6, 'nombre' => 'administrativo', 'descripcion' => 'Administración', 'creado_en' => now()],
    ]);
});

test('administrators and coordinators can see the accumulated ranking', function () {
    $admin = User::factory()->create([
        'rol_id' => 1,
        'activo' => true,
        'estado_cuenta' => User::ESTADO_APROBADO,
    ]);
    $first = User::factory()->create([
        'name' => 'Ana Primero',
        'rol_id' => 5,
        'activo' => true,
        'estado_cuenta' => User::ESTADO_APROBADO,
    ]);
    $second = User::factory()->create([
        'name' => 'Bruno Segundo',
        'rol_id' => 2,
        'activo' => true,
        'estado_cuenta' => User::ESTADO_APROBADO,
    ]);

    PuntajeMovimiento::create([
        'usuario_id' => $first->id,
        'puntos' => 8,
        'evento' => 'acta.firmada',
        'clave' => 'acta:1:firmada',
        'descripcion' => 'Acta registrada con todas sus firmas.',
    ]);
    PuntajeMovimiento::create([
        'usuario_id' => $second->id,
        'puntos' => 3,
        'evento' => 'tarea.completada',
        'clave' => 'tarea:1:completada',
        'descripcion' => 'Tarea completada.',
    ]);

    $this->actingAs($admin)
        ->get(route('estadisticas.ranking'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('estadisticas/ranking')
            ->where('participantes', 2)
            ->where('ranking.0.nombre', 'Ana Primero')
            ->where('ranking.0.puntos', 8)
            ->where('ranking.1.nombre', 'Bruno Segundo')
            ->where('ranking.1.puntos', 3));
});

test('the ranking is restricted to administrators and coordinators', function () {
    $profesional = User::factory()->create([
        'rol_id' => 5,
        'activo' => true,
        'estado_cuenta' => User::ESTADO_APROBADO,
    ]);

    $this->actingAs($profesional)
        ->get(route('estadisticas.ranking'))
        ->assertForbidden();
});

test('completing a task awards points once and reopening reverses them', function () {
    $profesional = User::factory()->create([
        'rol_id' => 5,
        'activo' => true,
        'estado_cuenta' => User::ESTADO_APROBADO,
    ]);
    $tarea = Tarea::create([
        'titulo' => 'Revisar derivación',
        'tipo' => 'derivacion',
        'prioridad' => 'normal',
        'estado' => 'pendiente',
        'asignado_a' => $profesional->id,
        'creado_por' => $profesional->id,
    ]);

    $this->actingAs($profesional)
        ->patch(route('tareas.update', $tarea), ['estado' => 'completada'])
        ->assertRedirect();

    $this->actingAs($profesional)
        ->patch(route('tareas.update', $tarea), ['estado' => 'completada'])
        ->assertRedirect();

    expect(PuntajeMovimiento::query()->where('evento', 'tarea.completada')->count())->toBe(1)
        ->and(PuntajeMovimiento::query()->sum('puntos'))->toBe(3);

    $this->actingAs($profesional)
        ->patch(route('tareas.update', $tarea), ['estado' => 'pendiente'])
        ->assertRedirect();

    expect(PuntajeMovimiento::query()->where('evento', 'tarea.reabierta')->count())->toBe(1)
        ->and(PuntajeMovimiento::query()->sum('puntos'))->toBe(0);
});

test('a signed acta awards its registration and signature points only once', function () {
    $profesional = User::factory()->create([
        'rol_id' => 5,
        'activo' => true,
        'estado_cuenta' => User::ESTADO_APROBADO,
    ]);
    $acta = Acta::factory()->create([
        'creado_por' => $profesional->id,
        'firmado' => true,
    ]);

    $puntaje = app(PuntajeService::class);
    $puntaje->registrarActa($acta, $profesional);
    $puntaje->registrarActa($acta, $profesional);

    expect(PuntajeMovimiento::query()->where('origen_id', $acta->id)->count())->toBe(2)
        ->and(PuntajeMovimiento::query()->sum('puntos'))->toBe(4);
});

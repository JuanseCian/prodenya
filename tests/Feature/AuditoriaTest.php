<?php

use App\Models\AuditoriaEvento;
use App\Models\PuntajeMovimiento;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
    ]);
});

test('administrators can access the audit log', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $actor = User::factory()->create(['rol_id' => 1]);

    AuditoriaEvento::registrar('usuario_aprobado', $actor, ['estado_cuenta' => 'pendiente'], ['estado_cuenta' => 'aprobado']);

    $this->actingAs($admin)
        ->get(route('admin.auditoria.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('auditoria/index')
            ->has('eventos.data', 1)
            ->where('eventos.data.0.evento', 'usuario_aprobado'));
});

test('non administrators cannot access the audit log', function () {
    $operador = User::factory()->create(['rol_id' => 2]);

    $this->actingAs($operador)
        ->get(route('admin.auditoria.index'))
        ->assertForbidden();
});

test('the audit log can be filtered by search term', function () {
    $admin = User::factory()->create(['rol_id' => 1]);

    AuditoriaEvento::registrar('usuario_aprobado', $admin);
    AuditoriaEvento::registrar('usuario_suspendido', $admin);

    $this->actingAs($admin)
        ->get(route('admin.auditoria.index', ['buscar' => 'suspendido']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('auditoria/index')
            ->has('eventos.data', 1)
            ->where('eventos.data.0.evento', 'usuario_suspendido'));
});

test('administrators see audit events and score movements grouped by section', function () {
    $admin = User::factory()->create([
        'rol_id' => 1,
        'activo' => true,
        'estado_cuenta' => User::ESTADO_APROBADO,
    ]);
    $operador = User::factory()->create([
        'rol_id' => 2,
        'activo' => true,
        'estado_cuenta' => User::ESTADO_APROBADO,
    ]);

    AuditoriaEvento::create([
        'usuario_id' => $operador->id,
        'evento' => 'creado',
        'auditable_type' => User::class,
        'auditable_id' => $operador->id,
        'datos_antes' => null,
        'datos_despues' => ['name' => $operador->name],
        'creado_en' => now()->subMinute(),
    ]);
    PuntajeMovimiento::create([
        'usuario_id' => $operador->id,
        'puntos' => 3,
        'evento' => 'tarea.completada',
        'clave' => 'tarea:auditoria-test:completada',
        'descripcion' => 'Tarea completada.',
        'metadata' => ['prioridad' => 'normal'],
        'creado_en' => now()->addMinute(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.auditoria.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('auditoria/index')
            ->where('eventos.data.0.tipo_movimiento', 'puntaje')
            ->where('eventos.data.0.seccion', 'puntaje')
            ->where('eventos.data.0.puntos', 3)
            ->where('eventos.data.1.tipo_movimiento', 'auditoria')
            ->where('eventos.data.1.seccion', 'usuarios')
            ->where('secciones.0.value', 'todas'));

    $this->actingAs($admin)
        ->get(route('admin.auditoria.index', ['seccion' => 'puntaje']))
        ->assertInertia(fn ($page) => $page
            ->where('filtros.seccion', 'puntaje')
            ->where('eventos.total', 1)
            ->where('eventos.data.0.descripcion', 'Tarea completada.'));
});

test('audit trail resolves referenced ids into readable names', function () {
    $admin = User::factory()->create([
        'rol_id' => 1,
        'activo' => true,
        'estado_cuenta' => User::ESTADO_APROBADO,
    ]);
    $profesional = User::factory()->create(['name' => 'María López']);

    AuditoriaEvento::create([
        'usuario_id' => $admin->id,
        'evento' => 'editado',
        'auditable_type' => User::class,
        'auditable_id' => $admin->id,
        'datos_antes' => ['profesional_id' => $admin->id],
        'datos_despues' => ['profesional_id' => (string) $profesional->id],
        'creado_en' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.auditoria.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where("referencias.usuarios.{$profesional->id}", 'María López')
            ->where("referencias.usuarios.{$admin->id}", $admin->name));
});

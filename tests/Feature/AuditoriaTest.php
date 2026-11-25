<?php

use App\Models\AuditoriaEvento;
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

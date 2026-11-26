<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
    ]);
});

test('authenticated pages outside the admin panel render the institutional header', function (string $routeName) {
    $admin = User::factory()->create(['rol_id' => 1]);

    $this->actingAs($admin)
        ->get(route($routeName))
        ->assertOk()
        ->assertViewIs('layouts.frontend')
        ->assertSee('site-header', false)
        ->assertSee('Navegación principal', false);
})->with(['estadisticas', 'profile.edit']);

test('the admin panel keeps its own layout without the institutional header', function () {
    $admin = User::factory()->create(['rol_id' => 1]);

    $this->actingAs($admin)
        ->get(route('admin.auditoria.index'))
        ->assertOk()
        ->assertViewIs('app')
        ->assertDontSee('site-header', false);
});

test('guests on the login screen do not get the institutional header', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('site-header', false);
});

test('the header account menu shows the unread internal messages count', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $remitente = User::factory()->create(['rol_id' => 1]);
    $conversacionId = DB::table('conversaciones')->insertGetId([
        'tipo' => 'directa',
        'creado_por' => $remitente->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('conversacion_usuario')->insert([
        ['conversacion_id' => $conversacionId, 'usuario_id' => $admin->id, 'ultimo_leido_en' => null, 'created_at' => now(), 'updated_at' => now()],
        ['conversacion_id' => $conversacionId, 'usuario_id' => $remitente->id, 'ultimo_leido_en' => now(), 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('mensajes')->insert(collect(range(1, 3))->map(fn (int $numero): array => [
        'conversacion_id' => $conversacionId,
        'usuario_id' => $remitente->id,
        'cuerpo' => "Mensaje {$numero}",
        'created_at' => now(),
        'updated_at' => now(),
    ])->all());

    expect($admin->mensajesSinLeer())->toBe(3);

    $this->actingAs($admin)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('site-user-menu', false)
        ->assertSee('data-unread-messages >3</span>', false)
        ->assertSee('Cerrar sesión');
});

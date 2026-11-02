<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 4, 'nombre' => 'coordinador', 'descripcion' => 'Supervisión', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
    ]);
    Notification::fake();
});

test('an admin can approve a verified pending user and assign a professional role', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $pending = User::factory()->create([
        'rol_id' => null,
        'estado_cuenta' => User::ESTADO_PENDIENTE,
        'activo' => false,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.usuarios.index'))
        ->assertOk()
        ->assertSee('Gestión de usuarios')
        ->assertSee($pending->email);

    $this->actingAs($admin)
        ->post(route('admin.usuarios.aprobar', $pending), [
            'rol_id' => 5,
            'profesion' => 'psicologo',
        ])
        ->assertRedirect();

    expect($pending->fresh()->estado_cuenta)->toBe(User::ESTADO_APROBADO);
    expect($pending->fresh()->rol_id)->toBe(5);
    expect($pending->fresh()->profesion)->toBe('psicologo');
    expect($pending->fresh()->activo)->toBeTrue();
});

test('a professional role requires a catalog profession', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $pending = User::factory()->create([
        'rol_id' => null,
        'estado_cuenta' => User::ESTADO_PENDIENTE,
        'activo' => false,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.usuarios.aprobar', $pending), ['rol_id' => 5])
        ->assertSessionHasErrors('profesion');

    expect($pending->fresh()->estado_cuenta)->toBe(User::ESTADO_PENDIENTE);
});

test('changing away from professional preserves the selected profession', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $professional = User::factory()->create([
        'rol_id' => 5,
        'estado_cuenta' => User::ESTADO_APROBADO,
        'activo' => true,
        'profesion' => 'abogado',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.usuarios.rol', $professional), ['rol_id' => 4])
        ->assertRedirect();

    expect($professional->fresh()->rol_id)->toBe(4);
    expect($professional->fresh()->profesion)->toBe('abogado');
});

test('a coordinator cannot assign the admin role', function () {
    $coordinator = User::factory()->create(['rol_id' => 4]);
    $pending = User::factory()->create([
        'rol_id' => null,
        'estado_cuenta' => User::ESTADO_PENDIENTE,
        'activo' => false,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($coordinator)
        ->post(route('admin.usuarios.aprobar', $pending), ['rol_id' => 1])
        ->assertForbidden();
});

test('pending users cannot log in', function () {
    $pending = User::factory()->create([
        'estado_cuenta' => User::ESTADO_PENDIENTE,
        'activo' => false,
        'email_verified_at' => now(),
    ]);

    $this->post(route('login.store'), [
        'email' => $pending->email,
        'password' => 'password',
    ])->assertSessionHasErrors(['email' => 'Tu solicitud todavía está pendiente de aprobación.']);

    $this->assertGuest();
});

<?php

use App\Models\Aviso;
use App\Models\User;
use App\Notifications\AvisoPublicadoNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 4, 'nombre' => 'coordinador', 'descripcion' => 'Supervisión', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
    ]);
});

test('an admin can publish an aviso and it notifies every other active user', function () {
    Notification::fake();

    $admin = User::factory()->create(['rol_id' => 1]);
    $otro = User::factory()->create(['rol_id' => 5]);

    $this->actingAs($admin)
        ->post(route('admin.avisos.store'), [
            'titulo' => 'Corte de sistema programado',
            'mensaje' => 'El sistema estará en mantenimiento el viernes de 20 a 22hs.',
        ])
        ->assertRedirect(route('admin.avisos.index'));

    $aviso = Aviso::firstOrFail();
    expect($aviso->titulo)->toBe('Corte de sistema programado');
    expect($aviso->creado_por)->toBe($admin->id);
    expect($aviso->activo)->toBeTrue();

    Notification::assertSentTo($otro, AvisoPublicadoNotification::class, fn ($notification) => $notification->aviso->is($aviso));
    Notification::assertNotSentTo($admin, AvisoPublicadoNotification::class);
});

test('a coordinator can publish an aviso', function () {
    Notification::fake();

    $coordinador = User::factory()->create(['rol_id' => 4]);

    $this->actingAs($coordinador)
        ->post(route('admin.avisos.store'), [
            'titulo' => 'Recordatorio de agenda',
            'mensaje' => 'Revisen la agenda semanal antes del viernes.',
            'url' => 'https://prodenya.test/agenda',
        ])
        ->assertRedirect(route('admin.avisos.index'));

    expect(Aviso::firstOrFail()->url)->toBe('https://prodenya.test/agenda');
});

test('a user without admin or coordinator role cannot manage avisos', function () {
    $profesional = User::factory()->create(['rol_id' => 5]);

    $this->actingAs($profesional)
        ->get(route('admin.avisos.index'))
        ->assertForbidden();

    $this->actingAs($profesional)
        ->post(route('admin.avisos.store'), [
            'titulo' => 'No debería publicarse',
            'mensaje' => 'Contenido de prueba.',
        ])
        ->assertForbidden();

    expect(Aviso::count())->toBe(0);
});

test('publishing an aviso requires a title and message', function () {
    $admin = User::factory()->create(['rol_id' => 1]);

    $this->actingAs($admin)
        ->post(route('admin.avisos.store'), [])
        ->assertSessionHasErrors(['titulo', 'mensaje']);

    expect(Aviso::count())->toBe(0);
});

test('an admin can deactivate and reactivate an aviso without sending new notifications', function () {
    Notification::fake();

    $admin = User::factory()->create(['rol_id' => 1]);
    $aviso = Aviso::factory()->create(['creado_por' => $admin->id]);

    $this->actingAs($admin)
        ->post(route('admin.avisos.desactivar', $aviso))
        ->assertRedirect();

    expect($aviso->fresh()->activo)->toBeFalse();

    $this->actingAs($admin)
        ->post(route('admin.avisos.reactivar', $aviso))
        ->assertRedirect();

    expect($aviso->fresh()->activo)->toBeTrue();
    Notification::assertNothingSent();
});

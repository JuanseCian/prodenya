<?php

use App\Models\User;
use App\Notifications\MensajePrivadoNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
    ]);
});

function crearNotificacionPara(User $usuario): DatabaseNotification
{
    return DatabaseNotification::create([
        'id' => (string) Str::uuid(),
        'type' => 'derivacion_caso',
        'notifiable_type' => User::class,
        'notifiable_id' => $usuario->id,
        'data' => [
            'titulo' => 'Nueva derivación',
            'mensaje' => 'Recibiste una derivación.',
            'url' => '/legajos/1',
        ],
    ]);
}

test('a user can mark their own notification as read', function () {
    $usuario = User::factory()->create(['rol_id' => 1]);
    $notificacion = crearNotificacionPara($usuario);

    $this->actingAs($usuario)
        ->patch(route('notificaciones.leer', $notificacion))
        ->assertRedirect();

    expect($notificacion->fresh()->read_at)->not->toBeNull();
});

test('a user can retrieve their notification inbox', function () {
    $usuario = User::factory()->create(['rol_id' => 1]);
    $notificacion = crearNotificacionPara($usuario);

    $this->actingAs($usuario)
        ->getJson(route('notificaciones.index'))
        ->assertOk()
        ->assertJsonPath('notificaciones.0.id', $notificacion->id)
        ->assertJsonPath('notificacionesNoLeidas', 1);
});

test('a user cannot mark another users notification as read', function () {
    $usuario = User::factory()->create(['rol_id' => 1]);
    $otroUsuario = User::factory()->create(['rol_id' => 1]);
    $notificacion = crearNotificacionPara($otroUsuario);

    $this->actingAs($usuario)
        ->patch(route('notificaciones.leer', $notificacion))
        ->assertForbidden();

    expect($notificacion->fresh()->read_at)->toBeNull();
});

test('the notices inbox leaves private messages to their own counter', function () {
    $usuario = User::factory()->create(['rol_id' => 1]);
    $aviso = crearNotificacionPara($usuario);
    $usuario->notify(new MensajePrivadoNotification(1, 'Ana', 'Hola'));

    expect($usuario->avisosSinLeer())->toBe(1);

    $this->actingAs($usuario)
        ->getJson(route('notificaciones.index'))
        ->assertOk()
        ->assertJsonCount(1, 'notificaciones')
        ->assertJsonPath('notificaciones.0.id', $aviso->id)
        ->assertJsonPath('notificacionesNoLeidas', 1);

    $this->actingAs($usuario)
        ->patchJson(route('notificaciones.leer-todas'))
        ->assertOk();

    expect($usuario->avisosSinLeer())->toBe(0)
        ->and($usuario->unreadNotifications()->count())->toBe(1);
});

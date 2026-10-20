<?php

use App\Models\Conversacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
        ['id' => 3, 'nombre' => 'consulta', 'descripcion' => 'Solo lectura', 'creado_en' => now()],
    ]);
});

test('users can create a direct conversation and exchange messages', function () {
    $emisor = User::factory()->create(['rol_id' => 2]);
    $destinatario = User::factory()->create(['rol_id' => 3]);

    $this->actingAs($emisor)
        ->post(route('comunicacion.conversaciones.store'), [
            'usuario_id' => $destinatario->id,
        ])
        ->assertRedirect();

    $conversacion = Conversacion::firstOrFail();

    $this->actingAs($emisor)
        ->post(route('comunicacion.mensajes.store', $conversacion), [
            'cuerpo' => 'Te comparto el seguimiento del caso.',
        ])
        ->assertRedirect();

    expect($conversacion->usuarios()->count())->toBe(2);
    expect($conversacion->mensajes()->first()->cuerpo)->toBe('Te comparto el seguimiento del caso.');
});

test('a user outside a conversation cannot read or send messages', function () {
    $emisor = User::factory()->create(['rol_id' => 2]);
    $destinatario = User::factory()->create(['rol_id' => 3]);
    $externo = User::factory()->create(['rol_id' => 1]);

    $this->actingAs($emisor)->post(route('comunicacion.conversaciones.store'), [
        'usuario_id' => $destinatario->id,
    ]);

    $conversacion = Conversacion::firstOrFail();

    $this->actingAs($externo)
        ->get(route('comunicacion.show', $conversacion))
        ->assertForbidden();

    $this->actingAs($externo)
        ->post(route('comunicacion.mensajes.store', $conversacion), [
            'cuerpo' => 'No debería ingresar.',
        ])
        ->assertForbidden();
});

test('unread communication count follows the conversation read marker', function () {
    $emisor = User::factory()->create(['rol_id' => 2]);
    $destinatario = User::factory()->create(['rol_id' => 3]);

    $this->actingAs($emisor)->post(route('comunicacion.conversaciones.store'), [
        'usuario_id' => $destinatario->id,
    ]);

    $conversacion = Conversacion::firstOrFail();

    $this->actingAs($emisor)->post(route('comunicacion.mensajes.store', $conversacion), [
        'cuerpo' => 'Mensaje pendiente.',
    ]);

    $this->actingAs($destinatario)
        ->get(route('comunicacion.no-leidos'))
        ->assertOk()
        ->assertJson(['no_leidos' => 1]);

    $this->actingAs($destinatario)
        ->get(route('comunicacion.show', $conversacion))
        ->assertOk();

    $this->actingAs($destinatario)
        ->get(route('comunicacion.no-leidos'))
        ->assertOk()
        ->assertJson(['no_leidos' => 0]);
});

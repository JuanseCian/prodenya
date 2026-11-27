<?php

use App\Models\Acta;
use App\Models\Conversacion;
use App\Models\User;
use App\Notifications\MensajePrivadoNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
        ['id' => 3, 'nombre' => 'consulta', 'descripcion' => 'Solo lectura', 'creado_en' => now()],
    ]);
});

test('users can create a direct conversation and exchange messages', function () {
    Notification::fake();

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
    Notification::assertSentTo($destinatario, MensajePrivadoNotification::class, fn ($notification) => $notification->conversacionId === $conversacion->id
        && $notification->remitente === $emisor->name
        && $notification->mensaje === 'Te comparto el seguimiento del caso.'
    );
});

test('users can share an acta and upload files in a message', function () {
    Notification::fake();
    Storage::fake('local');

    $emisor = User::factory()->create(['rol_id' => 2]);
    $destinatario = User::factory()->create(['rol_id' => 3]);
    $acta = Acta::factory()->create(['creado_por' => $emisor->id]);

    $this->actingAs($emisor)->post(route('comunicacion.conversaciones.store'), [
        'usuario_id' => $destinatario->id,
    ]);

    $conversacion = Conversacion::firstOrFail();
    $foto = UploadedFile::fake()->image('seguimiento.jpg');

    $this->actingAs($emisor)
        ->post(route('comunicacion.mensajes.store', $conversacion), [
            'cuerpo' => 'Te comparto el acta y una foto.',
            'tipo_recurso' => 'acta',
            'recurso_id' => $acta->id,
            'archivos' => [$foto],
        ])
        ->assertRedirect();

    $mensaje = $conversacion->mensajes()->with(['recursos', 'archivos'])->firstOrFail();
    $archivo = $mensaje->archivos->firstOrFail();

    expect($mensaje->recursos->first()->tipo_recurso)->toBe('acta')
        ->and($mensaje->recursos->first()->recurso_id)->toBe($acta->id)
        ->and($archivo->nombre_archivo)->toBe('seguimiento.jpg');

    Storage::disk('local')->assertExists($archivo->ruta_archivo);

    $this->actingAs($destinatario)
        ->get(route('comunicacion.archivos.abrir', [$conversacion, $archivo]))
        ->assertOk();
});

test('message attachments reject unsupported files', function () {
    $emisor = User::factory()->create(['rol_id' => 2]);
    $destinatario = User::factory()->create(['rol_id' => 3]);

    $this->actingAs($emisor)->post(route('comunicacion.conversaciones.store'), [
        'usuario_id' => $destinatario->id,
    ]);

    $conversacion = Conversacion::firstOrFail();

    $this->actingAs($emisor)
        ->post(route('comunicacion.mensajes.store', $conversacion), [
            'archivos' => [UploadedFile::fake()->create('script.exe', 10, 'application/octet-stream')],
        ])
        ->assertSessionHasErrors('archivos.0');
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

test('opening a conversation clears its unread badge and message notifications', function () {
    $emisor = User::factory()->create(['rol_id' => 2]);
    $destinatario = User::factory()->create(['rol_id' => 3]);

    $this->actingAs($emisor)->post(route('comunicacion.conversaciones.store'), [
        'usuario_id' => $destinatario->id,
    ]);
    $conversacion = Conversacion::firstOrFail();

    $this->actingAs($emisor)->post(route('comunicacion.mensajes.store', $conversacion), [
        'cuerpo' => 'Hola, ¿lo viste?',
    ]);

    $otraConversacionId = DB::table('conversaciones')->insertGetId([
        'tipo' => 'directa',
        'creado_por' => $emisor->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $destinatario->notify(new MensajePrivadoNotification($otraConversacionId, $emisor->name, 'Otro tema'));

    expect($destinatario->mensajesSinLeer())->toBe(1)
        ->and($destinatario->unreadNotifications()->count())->toBe(2);

    $this->actingAs($destinatario)
        ->get(route('comunicacion.show', $conversacion))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('comunicacionNoLeidos', 0)
            ->where('conversaciones.0.no_leidos', 0));

    $pendientes = $destinatario->unreadNotifications()->get();

    expect($pendientes)->toHaveCount(1)
        ->and($pendientes->first()->data['conversacion_id'])->toBe($otraConversacionId);
});

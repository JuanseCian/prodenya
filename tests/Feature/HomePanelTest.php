<?php

use App\Models\Passkey;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
    ]);
});

test('home shows the passkey notice when the user has no passkey', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('data-passkey-enabled="1"', false);
});

test('home hides the passkey notice when the user has a passkey', function () {
    $user = User::factory()->create();

    Passkey::create([
        'user_id' => $user->id,
        'name' => 'Notebook de trabajo',
        'credential_id' => 'home-test-credential',
        'credential' => ['id' => 'home-test-credential'],
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('data-passkey-enabled="0"', false);
});

test('the personal panel returns the user obligations and visible events', function () {
    $user = User::factory()->create(['rol_id' => 5]);

    $this->actingAs($user)
        ->post(route('tareas.store'), [
            'titulo' => 'Revisar seguimiento',
            'prioridad' => 'alta',
            'fecha_limite' => today()->addDay()->toDateString(),
            'asignado_a' => $user->id,
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('agenda.store'), [
            'titulo' => 'Reunión general de equipo',
            'tipo' => 'reunion',
            'visibilidad' => 'general',
            'fecha_inicio' => now()->setTime(10, 0)->toDateTimeString(),
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('agenda.store'), [
            'titulo' => 'Nota personal',
            'tipo' => 'entrevista',
            'visibilidad' => 'personal',
            'fecha_inicio' => now()->setTime(12, 0)->toDateTimeString(),
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('tareas.store'), [
            'titulo' => 'Fuera de la ventana',
            'prioridad' => 'normal',
            'fecha_limite' => today()->addDays(4)->toDateString(),
            'asignado_a' => $user->id,
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('agenda.store'), [
            'titulo' => 'Evento fuera de la ventana',
            'tipo' => 'reunion',
            'visibilidad' => 'general',
            'fecha_inicio' => now()->addDays(4)->setTime(10, 0)->toDateTimeString(),
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->getJson(route('personal-panel'))
        ->assertOk()
        ->assertJsonPath('tareas.0.titulo', 'Revisar seguimiento')
        ->assertJsonPath('tareas_proximas', 1)
        ->assertJsonPath('eventos_proximos', 2)
        ->assertJsonFragment(['titulo' => 'Reunión general de equipo'])
        ->assertJsonFragment(['titulo' => 'Nota personal'])
        ->assertJsonMissing(['titulo' => 'Fuera de la ventana'])
        ->assertJsonMissing(['titulo' => 'Evento fuera de la ventana']);
});

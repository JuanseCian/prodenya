<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
    ]);
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
        ->getJson(route('personal-panel'))
        ->assertOk()
        ->assertJsonPath('tareas.0.titulo', 'Revisar seguimiento')
        ->assertJsonFragment(['titulo' => 'Reunión general de equipo'])
        ->assertJsonFragment(['titulo' => 'Nota personal']);
});

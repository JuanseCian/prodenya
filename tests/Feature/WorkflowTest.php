<?php

use App\Models\AgendaEvento;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
        ['id' => 3, 'nombre' => 'consulta', 'descripcion' => 'Solo lectura', 'creado_en' => now()],
        ['id' => 4, 'nombre' => 'coordinador', 'descripcion' => 'Supervisión', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
    ]);
});

test('a coordinator can create a task and the assignee can complete it', function () {
    $coordinador = User::factory()->create(['rol_id' => 4]);
    $profesional = User::factory()->create(['rol_id' => 5]);

    $this->actingAs($coordinador)
        ->post(route('tareas.store'), [
            'titulo' => 'Revisar derivación',
            'prioridad' => 'alta',
            'asignado_a' => $profesional->id,
            'fecha_limite' => today()->addDay()->toDateString(),
        ])
        ->assertRedirect();

    $tarea = Tarea::firstOrFail();

    $this->actingAs($profesional)
        ->patch(route('tareas.update', $tarea), ['estado' => 'completada'])
        ->assertRedirect();

    expect($tarea->fresh()->estado)->toBe('completada');
    expect($tarea->fresh()->completada_en)->not->toBeNull();
});

test('a professional can schedule an event with external participants', function () {
    $profesional = User::factory()->create(['rol_id' => 5]);

    $this->actingAs($profesional)
        ->post(route('agenda.store'), [
            'titulo' => 'Reunión de seguimiento',
            'tipo' => 'reunion',
            'visibilidad' => 'general',
            'fecha_inicio' => now()->addDay()->setTime(10, 30)->toDateTimeString(),
            'participantes_externos' => [
                ['nombre' => 'Institución externa', 'contacto' => 'contacto@example.test'],
            ],
        ])
        ->assertRedirect();

    expect(AgendaEvento::firstOrFail()->creado_por)->toBe($profesional->id);
    expect(AgendaEvento::firstOrFail()->visibilidad)->toBe('general');
});

test('personal agenda events are only visible to their owner', function () {
    $owner = User::factory()->create(['rol_id' => 5]);
    $otherProfessional = User::factory()->create(['rol_id' => 5]);

    $this->actingAs($owner)
        ->post(route('agenda.store'), [
            'titulo' => 'Seguimiento personal',
            'tipo' => 'entrevista',
            'visibilidad' => 'personal',
            'fecha_inicio' => now()->addDay()->setTime(10, 30)->toDateTimeString(),
        ])
        ->assertRedirect();

    $this->actingAs($owner)
        ->post(route('agenda.store'), [
            'titulo' => 'Reunión general',
            'tipo' => 'reunion',
            'visibilidad' => 'general',
            'fecha_inicio' => now()->addDay()->setTime(12, 0)->toDateTimeString(),
        ])
        ->assertRedirect();

    $this->actingAs($otherProfessional)
        ->getJson(route('agenda.index'))
        ->assertOk()
        ->assertJsonMissing(['titulo' => 'Seguimiento personal'])
        ->assertJsonFragment(['titulo' => 'Reunión general']);

    $this->actingAs($owner)
        ->getJson(route('agenda.index'))
        ->assertJsonFragment(['titulo' => 'Seguimiento personal'])
        ->assertJsonFragment(['titulo' => 'Reunión general']);
});

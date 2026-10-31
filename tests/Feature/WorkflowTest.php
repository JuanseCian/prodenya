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

test('a regular user can only create and see tasks assigned to themselves', function () {
    $usuario = User::factory()->create(['rol_id' => 5]);
    $otroUsuario = User::factory()->create(['rol_id' => 5]);

    $this->actingAs($usuario)
        ->post(route('tareas.store'), [
            'titulo' => 'Tarea propia',
            'prioridad' => 'normal',
            'asignado_a' => $otroUsuario->id,
        ])
        ->assertRedirect();

    $tareaPropia = Tarea::where('titulo', 'Tarea propia')->firstOrFail();
    expect($tareaPropia->asignado_a)->toBe($usuario->id);

    $tareaAjena = Tarea::create([
        'titulo' => 'Tarea ajena',
        'prioridad' => 'normal',
        'asignado_a' => $otroUsuario->id,
        'creado_por' => $otroUsuario->id,
        'estado' => 'pendiente',
    ]);

    $this->actingAs($usuario)
        ->getJson(route('tareas.index'))
        ->assertOk()
        ->assertJsonFragment(['titulo' => 'Tarea propia'])
        ->assertJsonMissing(['titulo' => $tareaAjena->titulo]);
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

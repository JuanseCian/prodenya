<?php

use App\Models\DerivacionCaso;
use App\Models\Legajo;
use App\Models\MailMessage;
use App\Models\Persona;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
    ]);
});

test('only administrators can access the mailbox', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $operador = User::factory()->create(['rol_id' => 2]);

    $this->actingAs($admin)->get(route('mailbox.index'))->assertOk();
    $this->actingAs($operador)->get(route('mailbox.index'))->assertForbidden();
});

test('oficios can classify a message and derive it as a task', function () {
    DB::table('roles')->insert([
        ['id' => 4, 'nombre' => 'oficios', 'descripcion' => 'Clasificación', 'creado_en' => now()],
    ]);

    $oficios = User::factory()->create(['rol_id' => 4]);
    $responsable = User::factory()->create(['rol_id' => 2]);
    $provincia = DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $localidad = DB::table('localidades')->insertGetId([
        'nombre' => 'San Nicolás de los Arroyos',
        'provincia_id' => $provincia,
        'codigo_postal' => 2900,
    ]);
    $persona = Persona::create([
        'nombre' => 'Ana',
        'apellido' => 'Derivada',
        'dni' => '30111222',
        'fecha_nacimiento' => '2010-01-01',
        'genero' => 'F',
        'localidad_id' => $localidad,
        'activo' => true,
    ]);
    $legajo = Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'DER-ANA-222',
        'fecha_apertura' => today(),
        'nivel_riesgo' => 'Bajo',
        'origen_derivacion' => 'Juzgado',
        'estado' => 'Abierto',
        'creado_por' => $oficios->id,
    ]);
    $mail = MailMessage::create([
        'from_email' => 'juzgado@example.test',
        'subject' => 'Oficio de seguimiento',
        'body_text' => 'Se solicita informe.',
    ]);

    $this->actingAs($oficios)
        ->patch(route('mailbox.classify', $mail), [
            'estado' => 'Derivado',
            'asignado_a' => $responsable->id,
            'fecha_limite' => today()->addDays(5)->toDateString(),
            'legajo_ids' => [$legajo->id],
        ])
        ->assertRedirect();

    expect($mail->fresh()->estado)->toBe('Derivado');
    $tarea = Tarea::where('mail_message_id', $mail->id)->firstOrFail();

    expect($tarea->asignado_a)->toBe($responsable->id)
        ->and($tarea->legajo_id)->toBe($legajo->id);
    expect($tarea->recursos()->where('tipo_recurso', 'legajo')->where('recurso_id', $legajo->id)->exists())->toBeTrue();
    expect(DerivacionCaso::where('mail_message_id', $mail->id)->where('legajo_id', $legajo->id)->where('destinatario_id', $responsable->id)->exists())->toBeTrue();

    $this->actingAs($oficios)
        ->patch(route('mailbox.classify', $mail), [
            'estado' => 'Clasificado',
            'legajo_ids' => [$legajo->id],
        ])
        ->assertRedirect();

    expect($tarea->fresh()->estado)->toBe('cancelada');
    expect(DerivacionCaso::where('mail_message_id', $mail->id)->where('estado', 'cancelada')->exists())->toBeTrue();
});

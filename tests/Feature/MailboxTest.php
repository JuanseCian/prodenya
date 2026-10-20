<?php

use App\Models\User;
use App\Models\MailMessage;
use App\Models\Tarea;
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
        ])
        ->assertRedirect();

    expect($mail->fresh()->estado)->toBe('Derivado');
    expect(Tarea::where('mail_message_id', $mail->id)->firstOrFail()->asignado_a)->toBe($responsable->id);
});

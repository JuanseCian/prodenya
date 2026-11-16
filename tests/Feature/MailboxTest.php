<?php

use App\Mail\MailboxMessage;
use App\Models\Conversacion;
use App\Models\DerivacionCaso;
use App\Models\Legajo;
use App\Models\MailMessage;
use App\Models\Persona;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
        ['id' => 3, 'nombre' => 'coordinador', 'descripcion' => 'Coordinación', 'creado_en' => now()],
        ['id' => 4, 'nombre' => 'oficios', 'descripcion' => 'Clasificación', 'creado_en' => now()],
    ]);
});

test('gmail connection reports missing OAuth configuration', function () {
    config([
        'services.google_gmail.enabled' => true,
        'services.google_gmail.client_id' => null,
        'services.google_gmail.client_secret' => null,
    ]);
    $admin = User::factory()->create(['rol_id' => 1]);

    $this->actingAs($admin)
        ->get(route('mailbox.google.redirect'))
        ->assertRedirect(route('mailbox.index'))
        ->assertSessionHasErrors([
            'mailbox' => 'Faltan las credenciales OAuth de Google en el entorno.',
        ]);
});

test('gmail connection redirects to Google with a session state', function () {
    config([
        'services.google_gmail.enabled' => true,
        'services.google_gmail.client_id' => 'client-id.apps.googleusercontent.com',
        'services.google_gmail.client_secret' => 'client-secret',
        'services.google_gmail.redirect_uri' => 'http://localhost:8001/auth/google/callback',
    ]);
    $admin = User::factory()->create(['rol_id' => 1]);

    $response = $this->actingAs($admin)->get(route('mailbox.google.redirect'));

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('accounts.google.com');
    expect(session('google_oauth_state'))->toBeString()->toHaveLength(64);
});

test('gmail callback rejects an invalid OAuth state', function () {
    $admin = User::factory()->create(['rol_id' => 1]);

    $this->actingAs($admin)
        ->withSession(['google_oauth_state' => 'valid-state'])
        ->get(route('mailbox.google.callback', ['state' => 'invalid-state', 'code' => 'code']))
        ->assertRedirect(route('mailbox.index'))
        ->assertSessionHasErrors([
            'mailbox' => 'La validación de seguridad de Google no fue válida.',
        ]);
});

test('mailbox is available to the configured operational roles', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $coordinador = User::factory()->create(['rol_id' => 3]);
    $oficios = User::factory()->create(['rol_id' => 4]);
    $operador = User::factory()->create(['rol_id' => 2]);

    $this->actingAs($admin)->get(route('mailbox.index'))->assertOk();
    $this->actingAs($coordinador)->get(route('mailbox.index'))->assertOk();
    $this->actingAs($oficios)->get(route('mailbox.index'))->assertOk();
    $this->actingAs($operador)->get(route('mailbox.index'))->assertForbidden();
});

test('the mailbox sync command fails safely when Hostinger credentials are missing', function () {
    config([
        'services.hostinger_mailbox.enabled' => true,
        'services.hostinger_mailbox.password' => null,
    ]);

    $this->artisan('mailbox:sync')
        ->expectsOutput('Faltan las credenciales IMAP de Hostinger en el entorno.')
        ->assertFailed();
});

test('the mailbox sync command exits cleanly when the integration is disabled', function () {
    config(['services.hostinger_mailbox.enabled' => false]);

    $this->artisan('mailbox:sync')
        ->expectsOutput('La sincronización del buzón está deshabilitada en este entorno.')
        ->assertSuccessful();
});

test('manual mailbox sync does not connect when the integration is disabled', function () {
    config([
        'services.hostinger_mailbox.enabled' => false,
        'services.google_gmail.enabled' => false,
    ]);
    $admin = User::factory()->create(['rol_id' => 1]);

    $this->actingAs($admin)
        ->post(route('mailbox.sync'))
        ->assertRedirect()
        ->assertSessionHasErrors([
            'mailbox' => 'La sincronización del buzón está deshabilitada en este entorno.',
        ]);
});

test('mailbox shows incoming mail and excludes outgoing mail', function () {
    config(['services.hostinger_mailbox.enabled' => false]);

    $admin = User::factory()->create(['rol_id' => 1]);
    $correoA = MailMessage::create([
        'direction' => 'incoming',
        'from_email' => 'juzgado@example.test',
        'subject' => 'Turno AAA',
        'body_text' => 'Contenido del oficio.',
    ]);
    $correoB = MailMessage::create([
        'direction' => 'incoming',
        'from_email' => 'otro@example.test',
        'subject' => 'Turno BBB',
        'body_text' => 'Otro contenido.',
    ]);
    $correoC = MailMessage::create([
        'direction' => 'incoming',
        'from_email' => 'otro2@example.test',
        'subject' => 'Turno XYZ',
        'body_text' => 'No corresponde a ninguna de las columnas.',
    ]);
    MailMessage::create([
        'direction' => 'outgoing',
        'from_email' => 'prodenya@desarrollohumanosn.com',
        'to_email' => 'destino@example.test',
        'subject' => 'Turno AAA',
        'body_text' => 'Respuesta enviada.',
    ]);

    $this->actingAs($admin)
        ->get(route('mailbox.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('columnas', 1)
            ->where('columnas.0.asunto', 'Bandeja de entrada')
            ->has('columnas.0.mensajes', 3)
            ->where('columnas.0.mensajes.0.id', $correoC->id)
            ->where('columnas.0.mensajes.1.id', $correoB->id)
            ->where('columnas.0.mensajes.2.id', $correoA->id)
            ->missing('columnas.0.mensajes.3'));
});

test('mailbox paginates message metadata and loads the body on demand', function () {
    config(['services.hostinger_mailbox.enabled' => false]);

    $admin = User::factory()->create(['rol_id' => 1]);
    $mail = MailMessage::create([
        'direction' => 'incoming',
        'from_email' => 'juzgado@example.test',
        'subject' => 'Oficio con contenido diferido',
        'body_text' => 'Contenido completo que no debe viajar en el listado.',
    ]);

    $this->actingAs($admin)
        ->get(route('mailbox.index'))
        ->assertInertia(fn ($page) => $page
            ->has('mensajes.data', 1)
            ->where('mensajes.data.0.id', $mail->id)
            ->where('mensajes.data.0.body_text', null)
            ->where('mensajes.data.0.preview', 'Contenido completo que no debe viajar en el listado.')
        );

    $this->actingAs($admin)
        ->get(route('mailbox.show', $mail))
        ->assertOk()
        ->assertJsonPath('message.id', $mail->id)
        ->assertJsonPath('message.body_text', 'Contenido completo que no debe viajar en el listado.');
});

test('mailbox users can download an attachment from a message', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['rol_id' => 1]);
    $operador = User::factory()->create(['rol_id' => 2]);
    $path = UploadedFile::fake()->create('oficio.pdf', 50, 'application/pdf')->store('mail-attachments');
    $mail = MailMessage::create([
        'direction' => 'incoming',
        'from_email' => 'juzgado@example.test',
        'subject' => 'Oficio con adjunto',
        'body_text' => 'Contenido del oficio.',
        'attachments' => [[
            'path' => $path,
            'name' => 'oficio.pdf',
            'mime' => 'application/pdf',
            'size' => 50 * 1024,
        ]],
    ]);

    $this->actingAs($admin)
        ->get(route('mailbox.attachment', [$mail, 0]))
        ->assertDownload('oficio.pdf');

    $this->actingAs($operador)
        ->get(route('mailbox.attachment', [$mail, 0]))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('mailbox.attachment', [$mail, 1]))
        ->assertNotFound();
});

test('mailbox users can send and retain an outgoing email', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    Mail::fake();
    config([
        'services.google_gmail.enabled' => false,
        'services.hostinger_mailbox.address' => 'prodenya@desarrollohumanosn.com',
    ]);

    $this->actingAs($admin)
        ->post(route('mailbox.send'), [
            'to_email' => 'destinatario@example.test',
            'cc_email' => 'copia@example.test',
            'bcc_email' => 'oculta@example.test',
            'subject' => 'Información solicitada',
            'body' => 'Se envía la información solicitada.',
        ])
        ->assertRedirect();

    Mail::assertQueued(MailboxMessage::class, fn (MailboxMessage $mail): bool => $mail->hasTo('destinatario@example.test')
        && $mail->hasSubject('Información solicitada')
        && $mail->ccEmail === 'copia@example.test'
        && $mail->bccEmail === 'oculta@example.test');

    expect(MailMessage::query()
        ->where('direction', 'outgoing')
        ->where('to_email', 'destinatario@example.test')
        ->where('subject', 'Información solicitada')
        ->exists())->toBeTrue();
});

test('the mailbox mailable renders text and html content', function () {
    $mail = new MailboxMessage('Asunto de prueba', 'Contenido del mensaje');

    $mail->assertHasSubject('Asunto de prueba');
    $mail->assertSeeInText('Contenido del mensaje');
    $mail->assertSeeInHtml('Contenido del mensaje');
});

test('oficios can classify a message and derive it as a task', function () {
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
    expect(Conversacion::query()
        ->whereHas('usuarios', fn ($query) => $query->whereKey($oficios->id))
        ->whereHas('usuarios', fn ($query) => $query->whereKey($responsable->id))
        ->whereHas('mensajes', fn ($query) => $query->where('cuerpo', 'like', '%Nueva derivación%'))
        ->exists())->toBeTrue();

    $this->actingAs($oficios)
        ->patch(route('mailbox.classify', $mail), [
            'estado' => 'Clasificado',
            'legajo_ids' => [$legajo->id],
        ])
        ->assertRedirect();

    expect($tarea->fresh()->estado)->toBe('cancelada');
    expect(DerivacionCaso::where('mail_message_id', $mail->id)->where('estado', 'cancelada')->exists())->toBeTrue();
});

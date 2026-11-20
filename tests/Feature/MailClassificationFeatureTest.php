<?php

use App\Enums\MailClassificationCategory;
use App\Enums\MailClassificationMethod;
use App\Enums\MailClassificationPriority;
use App\Enums\MailClassificationStatus;
use App\Models\MailClassificationEvent;
use App\Models\MailMessage;
use App\Models\User;
use App\Services\MailClassificationService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'oficios', 'descripcion' => 'Clasificación', 'creado_en' => now()],
        ['id' => 3, 'nombre' => 'operador', 'descripcion' => 'Carga', 'creado_en' => now()],
    ]);
});

test('stores an automatic suggestion and its explanation history', function () {
    $message = MailMessage::create([
        'direction' => 'incoming',
        'from_email' => 'juzgado@example.test',
        'subject' => 'Oficio judicial',
        'body_text' => 'Se solicita información del expediente.',
    ]);

    app(MailClassificationService::class)->classifyAutomatically($message->id);

    $message->refresh();

    expect($message->clasificacion_categoria)->toBe(MailClassificationCategory::Oficios)
        ->and($message->clasificacion_estado)->toBe(MailClassificationStatus::SugeridoAutomaticamente)
        ->and($message->clasificacion_metodo)->toBe(MailClassificationMethod::Reglas)
        ->and($message->clasificacion_motivos)->not->toBeEmpty();
    expect(MailClassificationEvent::query()->where('mail_message_id', $message->id)->count())->toBe(1);
});

test('manual classification records the responsible user and cannot be overwritten automatically', function () {
    $oficios = User::factory()->create(['rol_id' => 2]);
    $message = MailMessage::create([
        'direction' => 'incoming',
        'from_email' => 'persona@example.test',
        'subject' => 'Consulta general',
        'body_text' => 'Consulta sobre un trámite.',
    ]);

    $this->actingAs($oficios)
        ->patch(route('mailbox.classify', $message), [
            'estado' => 'Clasificado',
            'clasificacion_categoria' => MailClassificationCategory::OtrasComunicaciones->value,
            'prioridad' => MailClassificationPriority::Alta->value,
        ])
        ->assertRedirect();

    app(MailClassificationService::class)->classifyAutomatically($message->id);

    $message->refresh();

    expect($message->clasificacion_categoria)->toBe(MailClassificationCategory::OtrasComunicaciones)
        ->and($message->clasificacion_estado)->toBe(MailClassificationStatus::CorregidoManualmente)
        ->and($message->clasificacion_metodo)->toBe(MailClassificationMethod::Manual)
        ->and($message->clasificacion_revisado_por)->toBe($oficios->id)
        ->and($message->prioridad)->toBe(MailClassificationPriority::Alta);
    expect(MailClassificationEvent::query()->where('mail_message_id', $message->id)->count())->toBe(1);
});

test('confirms a suggested classification for an authorized operational user', function () {
    $oficios = User::factory()->create(['rol_id' => 2]);
    $message = MailMessage::create([
        'direction' => 'incoming',
        'from_email' => 'juzgado@example.test',
        'subject' => 'Oficio judicial',
        'body_text' => 'Se solicita informe.',
    ]);
    app(MailClassificationService::class)->classifyAutomatically($message->id);

    $this->actingAs($oficios)
        ->patch(route('mailbox.classification.confirm', $message))
        ->assertRedirect();

    expect($message->refresh()->clasificacion_estado)->toBe(MailClassificationStatus::Confirmado)
        ->and($message->clasificacion_revisado_por)->toBe($oficios->id);
});

test('filters the mailbox by institutional classification on the server', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $oficio = MailMessage::create([
        'direction' => 'incoming',
        'from_email' => 'juzgado@example.test',
        'subject' => 'Oficio judicial',
        'clasificacion_categoria' => MailClassificationCategory::Oficios,
    ]);
    MailMessage::create([
        'direction' => 'incoming',
        'from_email' => 'persona@example.test',
        'subject' => 'Consulta',
        'clasificacion_categoria' => MailClassificationCategory::OtrasComunicaciones,
    ]);

    $this->actingAs($admin)
        ->get(route('mailbox.index', ['clasificacion' => MailClassificationCategory::Oficios->value]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('mensajes.data', 1)
            ->where('mensajes.data.0.id', $oficio->id)
            ->where('filtros.clasificacion', MailClassificationCategory::Oficios->value));
});

test('forbids manual classification for a role outside the mailbox boundary', function () {
    $operador = User::factory()->create(['rol_id' => 3]);
    $message = MailMessage::create([
        'direction' => 'incoming',
        'from_email' => 'juzgado@example.test',
        'subject' => 'Oficio judicial',
    ]);

    $this->actingAs($operador)
        ->patch(route('mailbox.classify', $message), [
            'estado' => 'Clasificado',
            'clasificacion_categoria' => MailClassificationCategory::Oficios->value,
        ])
        ->assertForbidden();

});

<?php

use App\Enums\MailClassificationCategory;
use App\Enums\MailClassificationPriority;
use App\Enums\MailClassificationStatus;
use App\Models\MailMessage;
use App\Services\MailClassificationService;

test('suggests oficios from a judicial subject', function () {
    $message = new MailMessage([
        'subject' => 'Oficio judicial del Juzgado de Familia',
        'from_email' => 'juzgado@example.test',
        'body_text' => 'Se solicita informe de seguimiento.',
    ]);

    $suggestion = (new MailClassificationService)->suggest($message);

    expect($suggestion['categoria'])->toBe(MailClassificationCategory::Oficios)
        ->and($suggestion['estado'])->toBe(MailClassificationStatus::SugeridoAutomaticamente)
        ->and($suggestion['confianza'])->toBeGreaterThan(0.5)
        ->and($suggestion['motivos'])->toContain('Asunto contiene "oficio judicial".');
});

test('suggests denuncias and urgent priority from the message content', function () {
    $message = new MailMessage([
        'subject' => 'Denuncia por violencia',
        'from_email' => 'comunidad@example.test',
        'body_text' => 'Se informa una situación de riesgo inminente y emergencia.',
    ]);

    $suggestion = (new MailClassificationService)->suggest($message);

    expect($suggestion['categoria'])->toBe(MailClassificationCategory::Denuncias)
        ->and($suggestion['prioridad'])->toBe(MailClassificationPriority::Urgente);
});

test('leaves messages pending when the evidence is insufficient', function () {
    $message = new MailMessage([
        'subject' => 'Hola',
        'from_email' => 'persona@example.test',
        'body_text' => 'Necesito comunicarme.',
    ]);

    $suggestion = (new MailClassificationService)->suggest($message);

    expect($suggestion['categoria'])->toBe(MailClassificationCategory::Pendiente)
        ->and($suggestion['estado'])->toBe(MailClassificationStatus::Pendiente)
        ->and($suggestion['confianza'])->toBeNull();
});

test('uses attachment names as classification evidence', function () {
    $message = new MailMessage([
        'subject' => 'Documentación',
        'from_email' => 'mesa@example.test',
        'body_text' => 'Se adjunta documentación.',
        'attachments' => [['name' => 'oficio_judicial.pdf']],
    ]);

    $suggestion = (new MailClassificationService)->suggest($message);

    expect($suggestion['categoria'])->toBe(MailClassificationCategory::Oficios)
        ->and($suggestion['motivos'])->toContain('Adjuntos contiene "oficio".');
});

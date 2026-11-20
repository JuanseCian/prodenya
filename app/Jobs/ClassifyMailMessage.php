<?php

namespace App\Jobs;

use App\Services\MailClassificationService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClassifyMailMessage implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public int $uniqueFor = 3600;

    public function __construct(public int $mailMessageId) {}

    public function uniqueId(): string
    {
        return (string) $this->mailMessageId;
    }

    public function handle(MailClassificationService $service): void
    {
        $service->classifyAutomatically($this->mailMessageId);
    }

    public function failed(?Throwable $exception): void
    {
        Log::warning('No se pudo clasificar automáticamente un correo.', [
            'mail_message_id' => $this->mailMessageId,
            'exception' => $exception,
        ]);
    }
}

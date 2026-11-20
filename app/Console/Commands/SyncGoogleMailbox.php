<?php

namespace App\Console\Commands;

use App\Services\GoogleGmailService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('mailbox:gmail-sync {--days= : Cantidad de días de histórico a sincronizar}')]
#[Description('Sincroniza el buzón de Gmail con Mailbox')]
class SyncGoogleMailbox extends Command
{
    public function handle(GoogleGmailService $google): int
    {
        if (! $google->isEnabled()) {
            $this->line('La sincronización de Gmail está deshabilitada en este entorno.');

            return self::SUCCESS;
        }

        if (! $google->isConfigured() || ! $google->isConnected()) {
            $this->line('Gmail todavía no está configurado o vinculado.');

            return self::SUCCESS;
        }

        try {
            $days = $this->option('days');
            $synchronized = $google->sync($days !== null ? max(1, (int) $days) : null);
            $this->info("{$synchronized} correo(s) nuevo(s) sincronizado(s) desde Gmail.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}

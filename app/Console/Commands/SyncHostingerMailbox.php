<?php

namespace App\Console\Commands;

use App\Services\HostingerMailboxService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('mailbox:sync {--days= : Cantidad de días de histórico a sincronizar}')]
#[Description('Sincroniza el buzón de contacto de Hostinger con Mailbox')]
class SyncHostingerMailbox extends Command
{
    public function handle(HostingerMailboxService $mailbox): int
    {
        try {
            $days = $this->option('days');
            $synchronized = $mailbox->sync($days !== null ? max(1, (int) $days) : null);
            $this->info("{$synchronized} correo(s) sincronizado(s) desde Hostinger.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}

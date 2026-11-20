<?php

use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    TeamInvitation::query()
        ->whereNotNull('expires_at')
        ->where('expires_at', '<', now())
        ->delete();
})->daily()->description('Delete expired team invitations');

Schedule::command('medidas-abrigo:avisar-vencimientos')
    ->daily()
    ->description('Avisar vencimientos próximos de medidas de abrigo');

Schedule::command('mailbox:gmail-sync')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->description('Sincronizar nuevos correos de Gmail con Mailbox');

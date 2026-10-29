<?php

use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    TeamInvitation::query()
        ->whereNotNull('expires_at')
        ->where('expires_at', '<', now())
        ->delete();
})->daily()->description('Delete expired team invitations');

Schedule::command('mailbox:sync')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->runInBackground()
    ->when(fn (): bool => (bool) config('services.hostinger_mailbox.enabled'));

<?php

use App\Http\Controllers\MailboxController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'rol:admin,oficios,coordinador'])->group(function () {
    Route::get('mailbox', [MailboxController::class, 'index'])->name('mailbox.index');
    Route::post('mailbox/sync', [MailboxController::class, 'sync'])->name('mailbox.sync');
    Route::post('mailbox/send', [MailboxController::class, 'send'])->name('mailbox.send');
    Route::get('mailbox/legajos/buscar', [MailboxController::class, 'buscarLegajos'])->name('mailbox.legajos.buscar');
    Route::get('mailbox/{mailMessage}/attachments/{attachment}', [MailboxController::class, 'downloadAttachment'])
        ->whereNumber('mailMessage')
        ->whereNumber('attachment')
        ->name('mailbox.attachment');
    Route::patch('mailbox/{mailMessage}/read', [MailboxController::class, 'markRead'])
        ->name('mailbox.read');
    Route::post('mailbox/{mailMessage}/reply', [MailboxController::class, 'reply'])
        ->name('mailbox.reply');
    Route::patch('mailbox/{mailMessage}/clasificar', [MailboxController::class, 'classify'])
        ->name('mailbox.classify');
});

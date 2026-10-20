<?php

use App\Http\Controllers\MailboxController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'rol:admin,oficios,coordinador'])->group(function () {
    Route::get('mailbox', [MailboxController::class, 'index'])->name('mailbox.index');
    Route::get('mailbox/legajos/buscar', [MailboxController::class, 'buscarLegajos'])->name('mailbox.legajos.buscar');
    Route::patch('mailbox/{mailMessage}/read', [MailboxController::class, 'markRead'])
        ->name('mailbox.read');
    Route::patch('mailbox/{mailMessage}/clasificar', [MailboxController::class, 'classify'])
        ->name('mailbox.classify');
});

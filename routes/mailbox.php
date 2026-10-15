<?php

use App\Http\Controllers\MailboxController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'rol:admin'])->group(function () {
    Route::get('mailbox', [MailboxController::class, 'index'])->name('mailbox.index');
    Route::patch('mailbox/{mailMessage}/read', [MailboxController::class, 'markRead'])
        ->name('mailbox.read');
});

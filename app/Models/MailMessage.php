<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailMessage extends Model
{
    protected $fillable = [
        'message_id',
        'thread_id',
        'from_name',
        'from_email',
        'to_email',
        'subject',
        'body_text',
        'body_html',
        'received_at',
        'is_read',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'is_read' => 'boolean',
            'attachments' => 'array',
        ];
    }
}

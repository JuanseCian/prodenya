<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MailMessage extends Model
{
    use Auditable;

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
        'estado',
        'clasificado_por',
        'clasificado_en',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'is_read' => 'boolean',
            'attachments' => 'array',
            'clasificado_en' => 'datetime',
        ];
    }

    public function legajos(): BelongsToMany
    {
        return $this->belongsToMany(Legajo::class, 'mail_message_legajo', 'mail_message_id', 'legajo_id');
    }

    public function clasificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'clasificado_por');
    }
}

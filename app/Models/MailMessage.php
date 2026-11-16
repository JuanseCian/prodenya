<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailMessage extends Model
{
    use Auditable;

    protected static function booted(): void
    {
        static::saving(function (MailMessage $message): void {
            if ($message->preview === null && $message->body_text !== null) {
                $message->preview = str($message->body_text)->squish()->limit(500)->toString();
            }

            if ($message->attachments !== null) {
                $message->has_attachments = $message->attachments !== [];
            }
        });
    }

    protected $fillable = [
        'message_id',
        'provider',
        'provider_message_id',
        'folder',
        'mailbox_uid',
        'direction',
        'thread_id',
        'in_reply_to',
        'from_name',
        'from_email',
        'to_email',
        'cc_email',
        'bcc_email',
        'subject',
        'body_text',
        'body_html',
        'preview',
        'received_at',
        'sent_at',
        'synced_at',
        'is_read',
        'is_starred',
        'has_attachments',
        'estado',
        'clasificado_por',
        'clasificado_en',
        'attachments',
        'labels',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'sent_at' => 'datetime',
            'synced_at' => 'datetime',
            'is_read' => 'boolean',
            'is_starred' => 'boolean',
            'has_attachments' => 'boolean',
            'attachments' => 'array',
            'labels' => 'array',
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

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'mail_message_id');
    }

    public function derivaciones(): HasMany
    {
        return $this->hasMany(DerivacionCaso::class, 'mail_message_id');
    }
}

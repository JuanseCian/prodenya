<?php

namespace App\Models;

use App\Enums\MailClassificationCategory;
use App\Enums\MailClassificationMethod;
use App\Enums\MailClassificationPriority;
use App\Enums\MailClassificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailClassificationEvent extends Model
{
    protected $fillable = [
        'mail_message_id',
        'categoria_anterior',
        'estado_anterior',
        'categoria',
        'estado',
        'confianza',
        'metodo',
        'motivos',
        'prioridad',
        'usuario_id',
    ];

    protected function casts(): array
    {
        return [
            'categoria_anterior' => MailClassificationCategory::class,
            'estado_anterior' => MailClassificationStatus::class,
            'categoria' => MailClassificationCategory::class,
            'estado' => MailClassificationStatus::class,
            'confianza' => 'float',
            'metodo' => MailClassificationMethod::class,
            'motivos' => 'array',
            'prioridad' => MailClassificationPriority::class,
        ];
    }

    public function mailMessage(): BelongsTo
    {
        return $this->belongsTo(MailMessage::class, 'mail_message_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}

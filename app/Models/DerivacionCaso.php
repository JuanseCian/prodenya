<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DerivacionCaso extends Model
{
    use Auditable;

    protected $table = 'derivaciones_caso';

    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_ACEPTADA = 'aceptada';

    public const ESTADO_RECHAZADA = 'rechazada';

    public const ESTADO_CANCELADA = 'cancelada';

    public const MODO_ADICIONAL = 'adicional';

    public const MODO_REEMPLAZO = 'reemplazo';

    protected $fillable = [
        'legajo_id',
        'solicitado_por',
        'destinatario_id',
        'modo',
        'estado',
        'motivo',
        'detalle',
        'responsables_reemplazados',
        'grupo_derivacion_id',
        'fecha_limite',
        'aceptada_en',
        'rechazada_en',
        'motivo_rechazo',
        'cancelada_en',
        'mail_message_id',
    ];

    protected function casts(): array
    {
        return [
            'responsables_reemplazados' => 'array',
            'fecha_limite' => 'datetime',
            'aceptada_en' => 'datetime',
            'rechazada_en' => 'datetime',
            'cancelada_en' => 'datetime',
        ];
    }

    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Legajo::class, 'legajo_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitado_por');
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinatario_id');
    }

    public function mail(): BelongsTo
    {
        return $this->belongsTo(MailMessage::class, 'mail_message_id');
    }

    public function conversaciones(): BelongsToMany
    {
        return $this->belongsToMany(
            Conversacion::class,
            'derivacion_caso_conversacion',
            'derivacion_caso_id',
            'conversacion_id',
        )->withTimestamps();
    }
}

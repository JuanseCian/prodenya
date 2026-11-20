<?php

namespace App\Enums;

enum MailClassificationStatus: string
{
    case Pendiente = 'pendiente';
    case SugeridoAutomaticamente = 'sugerido_automaticamente';
    case Confirmado = 'confirmado';
    case CorregidoManualmente = 'corregido_manualmente';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $status): string => $status->value, self::cases());
    }
}

<?php

namespace App\Enums;

enum MailClassificationPriority: string
{
    case Baja = 'baja';
    case Normal = 'normal';
    case Alta = 'alta';
    case Urgente = 'urgente';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $priority): string => $priority->value, self::cases());
    }
}

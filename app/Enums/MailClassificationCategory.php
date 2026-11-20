<?php

namespace App\Enums;

enum MailClassificationCategory: string
{
    case Oficios = 'oficios';
    case Denuncias = 'denuncias';
    case OtrasComunicaciones = 'otras_comunicaciones';
    case Pendiente = 'pendiente';

    public function label(): string
    {
        return match ($this) {
            self::Oficios => 'Oficios',
            self::Denuncias => 'Denuncias',
            self::OtrasComunicaciones => 'Otras comunicaciones',
            self::Pendiente => 'Pendiente de clasificación',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $category): string => $category->value, self::cases());
    }
}

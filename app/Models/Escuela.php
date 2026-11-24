<?php

namespace App\Models;

use Database\Factories\EscuelaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Escuela extends Model
{
    /** @var array<int, string> */
    public const NIVELES = ['Inicial', 'Primario', 'Secundario'];

    /** @use HasFactory<EscuelaFactory> */
    use HasFactory;

    protected $attributes = [
        'activo' => true,
    ];

    protected $fillable = [
        'nombre',
        'niveles',
        'orientadora',
        'celular',
        'mail',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'niveles' => 'array',
            'activo' => 'boolean',
        ];
    }

    public static function normalizarNombre(string $nombre): string
    {
        return Str::of($nombre)->ascii()->lower()->squish()->toString();
    }

    protected static function booted(): void
    {
        static::saving(function (self $escuela): void {
            $escuela->nombre = trim($escuela->nombre);
            $escuela->nombre_normalizado = self::normalizarNombre($escuela->nombre);
            $escuela->niveles = array_values($escuela->niveles ?? []);
        });
    }
}

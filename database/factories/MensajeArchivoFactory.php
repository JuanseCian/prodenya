<?php

namespace Database\Factories;

use App\Models\Mensaje;
use App\Models\MensajeArchivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MensajeArchivo>
 */
class MensajeArchivoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mensaje_id' => fn () => Mensaje::query()->value('id') ?? throw new \LogicException('Creá un mensaje antes de fabricar un archivo.'),
            'nombre_archivo' => 'adjunto.txt',
            'ruta_archivo' => 'comunicacion/adjunto.txt',
            'mime_type' => 'text/plain',
            'tamanio_bytes' => 0,
        ];
    }
}

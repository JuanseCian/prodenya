<?php

namespace Database\Factories;

use App\Models\AuhArchivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuhArchivo>
 */
class AuhArchivoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'auh_id' => 1,
            'tipo' => 'nota_elevacion',
            'descripcion' => null,
            'nombre_archivo' => 'nota-elevacion.pdf',
            'ruta_archivo' => 'legajos/1/auh/1/nota-elevacion.pdf',
            'mime_type' => 'application/pdf',
            'tamanio_bytes' => 1024,
            'activo' => true,
            'subido_por' => 1,
        ];
    }
}

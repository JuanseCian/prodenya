<?php

namespace Database\Factories;

use App\Models\CoberturaSaludArchivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoberturaSaludArchivo>
 */
class CoberturaSaludArchivoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cobertura_salud_id' => 1,
            'tipo' => 'credencial',
            'nombre_archivo' => 'credencial.pdf',
            'ruta_archivo' => 'coberturas/credencial.pdf',
            'mime_type' => 'application/pdf',
            'tamanio_bytes' => 1024,
            'activo' => true,
        ];
    }
}

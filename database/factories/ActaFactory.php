<?php

namespace Database\Factories;

use App\Models\Acta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Acta>
 */
class ActaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipo' => 'inicial',
            'titulo' => fake()->name(),
            'fecha' => today(),
            'datos' => ['compareciente_nombre' => fake()->name()],
            'firmado' => false,
            'firmas' => [],
            'nombre_archivo' => 'acta-inicial.docx',
            'ruta_archivo' => 'actas/acta-inicial.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'tamanio_bytes' => 1024,
        ];
    }
}

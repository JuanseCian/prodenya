<?php

namespace Database\Factories;

use App\Models\MedidaAbrigo;
use App\Models\MedidaAbrigoActa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedidaAbrigoActa>
 */
class MedidaAbrigoActaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'medida_abrigo_id' => 1,
            'tipo' => fake()->randomElement(array_keys(MedidaAbrigo::ACTA_LABELS)),
            'nombre_archivo' => 'acta.pdf',
            'ruta_archivo' => 'legajos/1/medidas-abrigo/1/acta.pdf',
            'mime_type' => 'application/pdf',
            'tamanio_bytes' => 1024,
            'subido_por' => 1,
        ];
    }
}

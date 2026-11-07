<?php

namespace Database\Factories;

use App\Models\MedidaAbrigo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedidaAbrigo>
 */
class MedidaAbrigoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'legajo_id' => 1,
            'ubicacion' => 'familia_ampliada',
            'duracion_dias' => 90,
            'fecha_inicio' => today(),
            'fecha_fin' => today()->addDays(89),
            'observaciones' => fake()->optional()->sentence(),
            'creado_por' => 1,
            'actualizado_por' => 1,
        ];
    }
}

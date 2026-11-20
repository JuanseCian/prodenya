<?php

namespace Database\Factories;

use App\Models\Auh;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Auh>
 */
class AuhFactory extends Factory
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
            'estado' => Auh::ESTADO_ACTIVO,
            'activo' => true,
            'observaciones' => fake()->optional()->sentence(),
            'creado_por' => 1,
            'actualizado_por' => 1,
        ];
    }
}

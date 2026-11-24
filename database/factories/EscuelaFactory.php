<?php

namespace Database\Factories;

use App\Models\Escuela;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Escuela>
 */
class EscuelaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->company(),
            'niveles' => ['Primario'],
            'orientadora' => fake()->name(),
            'celular' => fake()->numerify('3364-######'),
            'mail' => fake()->safeEmail(),
            'activo' => true,
        ];
    }
}

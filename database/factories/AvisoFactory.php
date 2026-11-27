<?php

namespace Database\Factories;

use App\Models\Aviso;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Aviso>
 */
class AvisoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(4),
            'mensaje' => fake()->paragraph(),
            'url' => null,
            'creado_por' => User::factory(),
            'activo' => true,
        ];
    }
}

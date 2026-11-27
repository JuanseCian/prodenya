<?php

namespace Database\Factories;

use App\Models\PuntajeMovimiento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PuntajeMovimiento>
 */
class PuntajeMovimientoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(),
            'puntos' => 3,
            'evento' => 'tarea.completada',
            'clave' => fake()->unique()->uuid(),
            'descripcion' => 'Tarea completada.',
            'metadata' => null,
            'creado_en' => now(),
        ];
    }
}

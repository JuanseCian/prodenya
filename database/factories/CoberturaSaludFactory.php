<?php

namespace Database\Factories;

use App\Models\CoberturaSalud;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoberturaSalud>
 */
class CoberturaSaludFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'persona_id' => 1,
            'tipo' => 'obra_social',
            'origen' => 'relacion_dependencia',
            'entidad' => 'Obra Social de prueba',
            'plan' => 'Plan general',
            'numero_afiliado' => fake()->numerify('##########'),
            'persona_es_titular' => true,
            'fecha_desde' => today()->subMonth(),
            'fecha_hasta' => null,
            'observaciones' => null,
            'activo' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Folio>
 */
class FolioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $listMelamina = [
            'BLANCO',
            'EBANO BOREAL',
            'ENCINO POLAR',
            'EUCALIPTO',
            'NOGAL',
            'OXFORD',
            'P. CENIZA',
            'WENGUE',
            'ZAFIRO',
          ];

        return [
            'state' => 1,
            'reference_product' => 1,
            'project_id' => Project::factory(),
            // 'name' => fake()->title(),
            'quantity' => fake()->numberBetween(1, 10),
            'height' => fake()->numberBetween(60, 200),
            'width' => fake()->numberBetween(60, 200),
            'depth' => fake()->numberBetween(60, 200),
            'melamina_color' => fake()->randomElement($listMelamina),
            'chapacinta_color' => fake()->randomElement($listMelamina),
            'structure_color' => fake()->randomElement($listMelamina),
            'tela_color' => fake()->randomElement($listMelamina),
            'package_type' => fake()->randomElement(['Linea', 'Granel']),
            'classification' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'melamina_density' => fake()->numberBetween(10, 50),
            'description' => fake()->text(100)
        ];
    }
}

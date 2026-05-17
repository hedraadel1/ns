<?php

namespace Database\Factories;

use App\Models\AIModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AIModel>
 */
class AIModelFactory extends Factory
{
    protected $model = AIModel::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word() . ' Model',
            'provider' => fake()->randomElement(['openai', 'gemini', 'anthropic']),
            'external_id' => fake()->unique()->uuid(),
            'description' => fake()->sentence(8),
            'capabilities' => ['reasoning' => true, 'summarization' => true],
            'metadata' => ['source' => 'factory'],
            'status' => fake()->randomElement(['active', 'deprecated', 'archived']),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Entity>
 */
class EntityFactory extends Factory
{
    protected $model = Entity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'api' => $this->faker->word,
            'description' => $this->faker->sentence,
            'link' => $this->faker->url,
            'category_id' => \App\Models\Category::factory(),
        ];
    }
}

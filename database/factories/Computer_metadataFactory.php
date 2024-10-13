<?php

namespace Database\Factories;

use App\Models\Computer_metadata;
use Illuminate\Database\Eloquent\Factories\Factory;

class Computer_metadataFactory extends Factory
{
    protected $model = Computer_metadata::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'price' => $this->faker->numberBetween(1000, 999999),
        ];
    }
}

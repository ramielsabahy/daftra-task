<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-####-????')),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'unit' => $this->faker->randomElement(['pcs', 'kg', 'm', 'l']),
            'low_stock_threshold' => $this->faker->numberBetween(5, 20),
        ];
    }
}

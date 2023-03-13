<?php

namespace Database\Factories;

use App\Models\Rent;
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
            'qty' => $this->faker->randomNumber(),
            'rent_id' => Rent::factory(),
            'rent_value' => $this->faker->randomFloat(2, 0, 50),
            'unit_value' => $this->faker->randomFloat(2, 100, 5000),
            'equipment_id' => $this->faker->uuid(),
        ];
    }
}

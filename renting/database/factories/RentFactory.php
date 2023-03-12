<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Period;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rent>
 */
class RentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'start_date' => $this->faker->dateTime(),
            'end_date' => $this->faker->dateTime(),
            'payment_type_id' => $this->faker->uuid(),
            'payment_method_id' => $this->faker->uuid(),
            'payment_condition_id' => $this->faker->uuid(),
            'customer_id' => Customer::factory(),
            'period_id' => Period::factory(),
            'qty_days' => $this->faker->randomNumber(),
        ];
    }
}

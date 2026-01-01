<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_code' => 'CUST-' . $this->faker->unique()->numerify('###'),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'customer_type' => $this->faker->randomElement(['individual','farm']),
            'address' => $this->faker->address(),
        ];
    }
}

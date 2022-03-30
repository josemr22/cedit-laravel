<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SpendingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
            'description' => $this->faker->sentence(2),
            'amount' => 5000,
            'user_id' => 1,
            'date' => \Carbon\Carbon::now(),
        ];
    }
}

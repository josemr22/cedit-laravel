<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StudentFactory extends Factory
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
            'name' => $this->faker->name,
            'dni' => '54545454',
            'email' => $this->faker->email,
            'department_id' => 2,
            'address' => $this->faker->sentence(2),
            'phone' => '23232323',
            'cellphone' => '989898988',
            'date_of_birth' => \Carbon\Carbon::now()->subYear(18),
            'observation' => $this->faker->sentence(4),
            'course_id' => 1,
            'course_turn_id' => 1,
            'registered_by' => 1,
        ];
    }
}

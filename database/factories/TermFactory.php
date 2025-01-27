<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TermFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'version' => $this->faker->regexify('[0-9]\.[0-9]\.[0-9]'), // Example: "1.0.0"
            'title' => $this->faker->sentence(3), // Random title
            'content' => $this->faker->paragraph(), // Random content
            'apply_date' => $this->faker->date(), // Random date
        ];
    }
}

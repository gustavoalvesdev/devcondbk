<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DocFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'fileurl' => 'docs/fake.pdf'
        ];
    }
}

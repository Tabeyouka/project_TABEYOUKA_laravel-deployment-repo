<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;

class UserFactory extends Factory
{
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->email,
            'nickname' => 'User'.uniqid(),
        ];
    }
}

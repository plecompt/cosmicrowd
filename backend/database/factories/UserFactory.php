<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'user_login' => $this->faker->userName(),
            'user_email' => $this->faker->unique()->safeEmail(),
            'user_password' => bcrypt('user1234'),
            'user_active' => true,
            'user_role' => $this->faker->randomElement(['admin', 'member']),
            'user_last_login' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'user_date_inscription' => $this->faker->dateTimeBetween('-5 years', 'now'),
        ];
    }
}

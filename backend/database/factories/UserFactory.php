<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    // public function definition()
    // {
    //     return [
    //         'user_login' => fake()->unique()->userName(),
    //         'user_password' => Hash::make('password'),
    //         'user_email' => fake()->unique()->safeEmail(),
    //         'user_active' => true,
    //         'user_role' => 'member',
    //         'user_last_login' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
    //         'user_date_inscription' => now(),
    //     ];
    // }

    // public function admin()
    // {
    //     return $this->state(fn (array $attributes) => [
    //         'user_role' => 'admin',
    //     ]);
    // }

    // public function inactive()
    // {
    //     return $this->state(fn (array $attributes) => [
    //         'user_active' => false,
    //     ]);
    // }
}
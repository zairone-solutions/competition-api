<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $username = $this->faker->userName . rand(111, 999);
        return [
            'username' => $username,
            'email' => $username . "-" . $this->faker->email,
            'full_name' => ucwords($username),
            'email_verified_at' => date_format($this->faker->dateTimeBetween("-5 years"), 'Y-m-d H:i:s'),
            'auth_provider' => 'email',
            'password' => Hash::make("secret_pass"),
            'avatar' => 'https://coursebari.com/wp-content/uploads/2021/06/899048ab0cc455154006fdb9676964b3.jpg',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}

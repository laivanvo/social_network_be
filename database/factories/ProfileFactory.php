<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Profile;

class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'address' => $this->faker->address(),
            'gender' => 'male',
            'birthday' => $this->faker->dateTime(),
            'phone_number' => $this->faker->e164PhoneNumber(),
            'avatar' => '/storage/uploads/1654844036_avatar.png'
    ];
    }
}

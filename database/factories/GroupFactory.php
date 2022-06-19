<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->firstName(),
            'audience' => $this->faker->randomElement(['public', 'private']),
            'card' => $this->faker->phoneNumber(),
            'content' => $this->faker->text(),
            'bonus' => 'play to game',
            'avatar' => '/storage/uploads/1655655229_avatar.webp'
    ];
    }
}

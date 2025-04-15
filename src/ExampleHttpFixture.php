<?php

namespace Gromatics\HttpFixtures;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ExampleHttpFixture extends HttpFixture
{
    public function definition(): array
    {
        return [
            'status' => Arr::random(['OK', 'NOK']),
            'message' => $this->faker->sentence(),
            'items' => [
                [
                    'identifier' => Str::random(20),
                    'name' => $this->faker->company(),
                    'address' => $this->faker->address(),
                    'postcode' => $this->faker->postcode(),
                    'city' => $this->faker->city(),
                    'country' => $this->faker->country(),
                    'phone' => $this->faker->phoneNumber(),
                    'email' => $this->faker->email(),
                ],
                [
                    'identifier' => Str::random(20),
                    'name' => $this->faker->company(),
                    'address' => $this->faker->address(),
                    'postcode' => $this->faker->postcode(),
                    'city' => $this->faker->city(),
                    'country' => $this->faker->country(),
                    'phone' => $this->faker->phoneNumber(),
                    'email' => $this->faker->email(),
                ],
            ],

        ];
    }
}

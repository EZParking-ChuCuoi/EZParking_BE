<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserParkingLot>
 */
class UserParkingLotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'userId' => rand(1000000,1000009),
            'parkingId' => rand(1000001,1000009),
        ];
    }
}

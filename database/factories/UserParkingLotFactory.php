<?php

namespace Database\Factories;

use App\Models\ParkingLot;
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
        $parkingLotId = ParkingLot::inRandomOrder()->first()->id;

        return [
            'userId' => rand(1000000,1000003),
            'parkingId' => $parkingLotId,
        ];
    }
}

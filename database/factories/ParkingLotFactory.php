<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParkingLot>
 */
class ParkingLotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'nameParkingLot'=>$this->faker->name(),
            'address'=>$this->faker->address(),
            'image'=>'https://m.media-amazon.com/images/I/510kGCsWt7L._SL1000_.jpg',
            'openTime'=>$this->faker->time(),
            'endTime'=>$this->faker->time(),
            'desc'=>$this->faker->sentence($nbWords = 6, $variableNbWords = true),
            'status'=>$this->faker->boolean(),
        ];
    }
}

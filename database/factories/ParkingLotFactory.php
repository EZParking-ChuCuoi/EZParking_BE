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
        $lang = 16.05;
        $long = 108.25;
    
       
        return [
            'nameParkingLot'=>$this->faker->name(),
            'address_latitude'=>$lang.rand(00000,99999),
            'address_longitude'=>$long.rand(00000,99999),
            'address'=>$this->faker->address(),
            'image'=>'https://m.media-amazon.com/images/I/510kGCsWt7L._SL1000_.jpg',
            'openTime'=>$this->faker->time(),
            'endTime'=>$this->faker->time(),
            'desc'=>$this->faker->sentence($nbWords = 6, $variableNbWords = true),
            'status'=>$this->faker->boolean(),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\ParkingLot;

class ParkingLotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ParkingLot::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $lang = 16.05;
        $long = 108.25;
    
        return [
            'nameParkingLot' => $this->faker->name(),
            'address_latitude' => $lang . rand(00000, 99999),
            'address_longitude' => $long . rand(00000, 99999),
            'address' => $this->faker->address(),
            'images' => json_encode(['https://m.media-amazon.com/images/I/510kGCsWt7L._SL1000_.jpg', 'https://m.media-amazon.com/images/I/510kGCsWt7L._SL1000_.jpg', 'https://m.media-amazon.com/images/I/510kGCsWt7L._SL1000_.jpg']),
            'openTime' => $this->faker->time(),
            'endTime' => $this->faker->time(),
            'desc' => $this->faker->sentence($nbWords = 6, $variableNbWords = true),
            'status' => $this->faker->boolean(),
        ];
    }
}
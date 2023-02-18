<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParkingSlot>
 */
class ParkingSlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $arr =['4slot','5-7slot','16slot','29-30slot','35-47slot'];

        return [
            'blockId'=> rand(1000000,1000019),
            'slotCode'=>Str::random(10),
            'carType'=>$arr[rand(0,4)],
            'price'=>$this->faker->numberBetween($min = 1500, $max = 6000),
            'status'=>$this->faker->boolean(),
            'desc'=>$this->faker->sentence($nbWords = 6, $variableNbWords = true),
            
        ];
    }
}

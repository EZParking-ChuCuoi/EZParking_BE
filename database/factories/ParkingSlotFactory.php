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

        return [
            'blockId'=> rand(1000000,1000019),
            'slotCode'=>Str::random(10),
            'status'=>$this->faker->boolean(),
            'desc'=>$this->faker->sentence($nbWords = 6, $variableNbWords = true),
            
        ];
    }
}

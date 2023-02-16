<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Block>
 */
class BlockFactory extends Factory
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
            'capacity'=>rand(111111,999999),
            'parkingLotId'=>rand(1000000,1000009),
            'nameBlock'=>$this->faker->name(),
            'blockCode'=>Str::random(10),
            'carType'=>$arr[rand(0,4)],
            'price'=>$this->faker->numberBetween($min = 1500, $max = 6000),
            'desc'=>$this->faker->sentence($nbWords = 6, $variableNbWords = true),

        ];
    }
}

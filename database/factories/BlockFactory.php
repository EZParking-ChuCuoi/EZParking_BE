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

        return [
            'capacity'=>rand(111111,999999),
            'parkingLotId'=>rand(1000000,1000019),
            'nameBlock'=>$this->faker->name(),
            'blockCode'=>Str::random(10),
            'desc'=>$this->faker->sentence($nbWords = 6, $variableNbWords = true),

        ];
    }
}

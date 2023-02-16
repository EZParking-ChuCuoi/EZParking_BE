<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'userId'=>rand(1000000,1000009),
            'slotId'=>rand(100000000,100000020),
            'bookDate'=>$this->faker->dateTime(),
            'returnDate'=>$this->faker->dateTime(),
            'payment'=>$this->faker->numberBetween($min = 1500, $max = 6000),
            'bookingStatus'=>$this->faker->boolean(),
            'rating'=>rand(1,5),
            'comment'=>$this->faker->sentence($nbWords = 6, $variableNbWords = true),
            'rating'=>rand(1,5),
        ];
    }
}

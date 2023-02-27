<?php

namespace Database\Factories;

use Carbon\Carbon;
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
        $fromDate = "2023-01-01";
        $toDate = "2023-01-3";
        $from = "2023-01-4";
        $to = "2023-01-8";


        return [
            'userId'=>rand(1000000,1000019),
            'slotId'=>rand(100000000,100000020),
            'licensePlate'=>$this->faker->name(),
            'bookDate'=>$this->faker->dateTimeBetween($fromDate, $toDate)->format("Y-m-d H:i:s"),
            'returnDate'=>$this->faker->dateTimeBetween($from, $to)->format("Y-m-d H:i:s"),
            'payment'=>$this->faker->numberBetween($min = 1500.000, $max = 6000.000),
        ];
    }
}

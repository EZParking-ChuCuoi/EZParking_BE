<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'userId'=>rand(1000000,1000019),
            'parkingId'=>rand(1000000,1000002),
            'content'=>$this->faker->sentence(9,true),
            'ranting'=>rand(1,5),
            'created_at'=>$this->faker->dateTime()->format('d-m-Y H:i:s'),
            'updated_at'=>$this->faker->dateTime()->format('d-m-Y H:i:s'),
        ];
    }
}

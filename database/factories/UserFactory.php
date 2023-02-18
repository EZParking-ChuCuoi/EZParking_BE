<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $arr=['admin','user','owner'];

        return [
            'email' => fake()->unique()->safeEmail(),
            'role'=>$arr[rand(0,2)],
            'fullName' =>fake()->name(),
            'avatar' =>'https://asset.cloudinary.com/di9pzz9af/25dc36a357534bb21254266ea4ecda42',
            'password' =>Hash::make('12345'),
            'status' =>1
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

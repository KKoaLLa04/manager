<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class SchoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code'=>fake()->postcode(10),
            'name'=>fake()->text(50),
            'avatar'=>fake()->imageUrl(),
            'address'=>fake()->address(255),
            'logo'=>fake()->imageUrl(),
            'telephone'=>fake()->phoneNumber(10),
            'email'=>fake()->email(),
            'modified_user_id'=>1,
        ];
    }
}

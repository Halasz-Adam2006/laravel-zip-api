<?php

namespace Database\Factories;

use App\Models\PostalCode;
use App\Models\County;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostalCode>
 */
class PostalCodeFactory extends Factory
{
    protected $model = PostalCode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'zip' => $this->faker->numerify('####'),
            'city' => $this->faker->city(),
            'county_id' => County::factory(),
        ];
    }
}

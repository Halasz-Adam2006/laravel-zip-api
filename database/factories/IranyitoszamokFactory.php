<?php

namespace Database\Factories;

use App\Models\Iranyitoszamok;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Iranyitoszamok>
 */
class IranyitoszamokFactory extends Factory
{
    protected $model = Iranyitoszamok::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'zip' => $this->faker->postcode,
            'city' => $this->faker->city(),
            'county' => $this->faker->state(),
        ];
    }
}

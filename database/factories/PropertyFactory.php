<?php

namespace Database\Factories;

use App\Enums\PropertyType;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->owner(),
            'name' => fake()->streetName().' īpašums',
            'notes' => fake()->sentence(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'country' => 'Latvija',
            'price' => fake()->randomFloat(2, 10000, 500000),
            'type' => fake()->randomElement(PropertyType::cases()),
            'acquired_at' => fake()->date(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\PropertyUnitStatus;
use App\Models\Property;
use App\Models\PropertyUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyUnit>
 */
class PropertyUnitFactory extends Factory
{
    protected $model = PropertyUnit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'name' => fake()->buildingNumber().' telpa',
            'code' => strtoupper(fake()->bothify('U##')),
            'notes' => fake()->sentence(),
            'status' => PropertyUnitStatus::Vacant,
            'area' => fake()->randomFloat(2, 10, 300),
            'unit_type' => fake()->randomElement(['Dzīvoklis', 'Birojs', 'Noliktava']),
            'is_active' => true,
        ];
    }
}

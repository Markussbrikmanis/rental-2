<?php

namespace Database\Factories;

use App\Enums\MeterType;
use App\Enums\UtilityBillingMode;
use App\Models\Meter;
use App\Models\PropertyUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meter>
 */
class MeterFactory extends Factory
{
    protected $model = Meter::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_unit_id' => PropertyUnit::factory(),
            'name' => 'Skaitītājs '.fake()->randomNumber(3),
            'type' => fake()->randomElement(MeterType::cases()),
            'unit' => fake()->randomElement(['m3', 'kWh', 'gab.']),
            'utility_billing_mode' => UtilityBillingMode::None,
            'rate_per_unit' => null,
            'is_active' => true,
        ];
    }
}

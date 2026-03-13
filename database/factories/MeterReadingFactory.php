<?php

namespace Database\Factories;

use App\Enums\MeterReadingSource;
use App\Models\Meter;
use App\Models\MeterReading;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeterReading>
 */
class MeterReadingFactory extends Factory
{
    protected $model = MeterReading::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meter_id' => Meter::factory(),
            'reading_date' => now()->toDateString(),
            'value' => fake()->randomFloat(3, 1, 10000),
            'source' => MeterReadingSource::Manual,
            'notes' => fake()->sentence(),
        ];
    }
}

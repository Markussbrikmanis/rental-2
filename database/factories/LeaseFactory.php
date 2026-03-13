<?php

namespace Database\Factories;

use App\Enums\LeaseStatus;
use App\Models\Lease;
use App\Models\PropertyUnit;
use App\Models\TenantProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lease>
 */
class LeaseFactory extends Factory
{
    protected $model = Lease::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'property_unit_id' => PropertyUnit::factory(),
            'tenant_profile_id' => TenantProfile::factory(),
            'start_date' => $startDate,
            'end_date' => null,
            'billing_start_date' => $startDate,
            'due_day' => fake()->numberBetween(1, 28),
            'currency' => 'EUR',
            'status' => LeaseStatus::Active,
            'deposit' => fake()->randomFloat(2, 0, 3000),
            'notes' => fake()->sentence(),
        ];
    }
}

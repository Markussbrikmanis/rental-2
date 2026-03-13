<?php

namespace Database\Factories;

use App\Enums\ChargeFrequency;
use App\Models\Lease;
use App\Models\LeaseChargeRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaseChargeRule>
 */
class LeaseChargeRuleFactory extends Factory
{
    protected $model = LeaseChargeRule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lease_id' => Lease::factory(),
            'name' => 'Īres maksa',
            'amount' => fake()->randomFloat(2, 100, 2500),
            'frequency' => ChargeFrequency::Monthly,
            'interval_count' => 1,
            'interval_unit' => null,
            'effective_from' => now()->startOfMonth(),
            'effective_to' => null,
            'auto_invoice_enabled' => true,
        ];
    }
}

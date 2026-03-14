<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(2),
            'description' => fake()->sentence(),
            'stripe_price_id' => 'price_'.fake()->lexify('????????????'),
            'display_price' => fake()->numberBetween(9, 199).',00 EUR / mēnesī',
            'currency' => 'EUR',
            'billing_interval' => 'month',
            'property_limit' => fake()->numberBetween(1, 50),
            'trial_enabled' => false,
            'trial_days' => null,
            'is_active' => true,
            'is_public' => true,
            'is_unlimited' => false,
            'sort_order' => 1,
        ];
    }
}

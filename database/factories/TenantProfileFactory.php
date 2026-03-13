<?php

namespace Database\Factories;

use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantProfile>
 */
class TenantProfileFactory extends Factory
{
    protected $model = TenantProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->owner(),
            'user_id' => null,
            'full_name' => fake()->name(),
            'company_name' => fake()->boolean(30) ? fake()->company() : null,
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'personal_code' => fake()->boolean(50) ? fake()->numerify('######-#####') : null,
            'registration_number' => fake()->boolean(40) ? fake()->numerify('########') : null,
            'billing_name' => fake()->boolean(70) ? fake()->company() : null,
            'billing_address' => fake()->boolean(70) ? fake()->address() : null,
            'billing_registration_number' => fake()->boolean(40) ? fake()->numerify('########') : null,
            'billing_vat_number' => fake()->boolean(30) ? 'LV'.fake()->numerify('###########') : null,
            'billing_bank_name' => fake()->boolean(40) ? fake()->company().' Banka' : null,
            'billing_swift_code' => fake()->boolean(40) ? strtoupper(fake()->lexify('????LV2X')) : null,
            'billing_account_number' => fake()->boolean(40) ? 'LV'.fake()->numerify('####################') : null,
            'notes' => fake()->sentence(),
        ];
    }
}

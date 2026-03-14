<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $starterPlan = SubscriptionPlan::query()->updateOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'description' => 'Sākuma plāns mazam īpašumu skaitam.',
                'stripe_price_id' => 'price_starter_monthly',
                'display_price' => '19,00 EUR / mēnesī',
                'currency' => 'EUR',
                'billing_interval' => 'month',
                'property_limit' => 3,
                'trial_enabled' => true,
                'trial_days' => 14,
                'is_active' => true,
                'is_public' => true,
                'is_unlimited' => false,
                'sort_order' => 1,
            ],
        );

        SubscriptionPlan::query()->updateOrCreate(
            ['slug' => 'growth'],
            [
                'name' => 'Growth',
                'description' => 'Plāns augošam īpašumu portfelim.',
                'stripe_price_id' => 'price_growth_monthly',
                'display_price' => '49,00 EUR / mēnesī',
                'currency' => 'EUR',
                'billing_interval' => 'month',
                'property_limit' => 10,
                'trial_enabled' => true,
                'trial_days' => 14,
                'is_active' => true,
                'is_public' => true,
                'is_unlimited' => false,
                'sort_order' => 2,
            ],
        );

        SubscriptionPlan::query()->updateOrCreate(
            ['slug' => 'scale'],
            [
                'name' => 'Scale',
                'description' => 'Plāns lielam un aktīvam portfelim.',
                'stripe_price_id' => 'price_scale_monthly',
                'display_price' => '99,00 EUR / mēnesī',
                'currency' => 'EUR',
                'billing_interval' => 'month',
                'property_limit' => 30,
                'trial_enabled' => true,
                'trial_days' => 14,
                'is_active' => true,
                'is_public' => true,
                'is_unlimited' => false,
                'sort_order' => 3,
            ],
        );

        $adminUnlimitedPlan = SubscriptionPlan::query()->updateOrCreate(
            ['slug' => 'admin-unlimited'],
            [
                'name' => 'Admin neierobežots',
                'description' => 'Administratora piešķirts bezmaksas neierobežots plāns.',
                'stripe_price_id' => null,
                'display_price' => 'Bezmaksas',
                'currency' => 'EUR',
                'billing_interval' => 'month',
                'property_limit' => null,
                'trial_enabled' => false,
                'trial_days' => null,
                'is_active' => true,
                'is_public' => false,
                'is_unlimited' => true,
                'sort_order' => 99,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'role' => UserRole::Admin,
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Owner User',
                'role' => UserRole::Owner,
                'subscription_plan_id' => $starterPlan->id,
                'owner_trial_ends_at' => now()->addDays(14),
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'tenant@example.com'],
            [
                'name' => 'Tenant User',
                'role' => UserRole::Tenant,
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        User::query()->where('email', 'admin@example.com')->update([
            'subscription_plan_id' => null,
        ]);

        User::query()->where('email', 'owner@example.com')->whereNull('subscription_plan_id')->update([
            'subscription_plan_id' => $starterPlan->id,
        ]);

        User::query()->where('email', 'tenant@example.com')->update([
            'subscription_plan_id' => null,
        ]);
    }
}

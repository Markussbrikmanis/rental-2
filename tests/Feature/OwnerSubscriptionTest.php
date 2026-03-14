<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_property_limit_is_enforced_by_selected_plan(): void
    {
        $plan = SubscriptionPlan::factory()->create([
            'property_limit' => 1,
            'is_unlimited' => false,
        ]);

        $owner = User::factory()->owner()->create([
            'subscription_plan_id' => $plan->id,
        ]);

        Property::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->post(route('client.properties.store'), [
                'name' => 'Jauns īpašums',
                'notes' => null,
                'address' => 'Brīvības iela 1',
                'city' => 'Rīga',
                'country' => 'Latvija',
                'price' => 100000,
                'type' => 'apartment',
                'acquired_at' => '2026-03-14',
            ])
            ->assertRedirect(route('client.properties.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('properties', [
            'name' => 'Jauns īpašums',
            'user_id' => $owner->id,
        ]);
    }

    public function test_admin_can_set_owner_to_unlimited_free_plan_and_custom_trial(): void
    {
        $admin = User::factory()->admin()->create();
        $starterPlan = SubscriptionPlan::factory()->create([
            'property_limit' => 3,
            'is_unlimited' => false,
        ]);
        $unlimitedPlan = SubscriptionPlan::factory()->create([
            'name' => 'Admin neierobežots',
            'slug' => 'admin-unlimited',
            'stripe_price_id' => null,
            'property_limit' => null,
            'display_price' => 'Bezmaksas',
            'is_public' => false,
            'is_unlimited' => true,
        ]);
        $owner = User::factory()->owner()->create([
            'subscription_plan_id' => $starterPlan->id,
            'owner_trial_ends_at' => null,
        ]);

        $this->actingAs($admin)
            ->put(route('client.admin.owner-subscriptions.update', $owner), [
                'subscription_plan_id' => $unlimitedPlan->id,
                'owner_trial_ends_at' => '2026-04-30',
            ])
            ->assertRedirect(route('client.admin.owner-subscriptions.index'));

        $owner->refresh();

        $this->assertSame($unlimitedPlan->id, $owner->subscription_plan_id);
        $this->assertSame('2026-04-30', $owner->owner_trial_ends_at?->format('Y-m-d'));
        $this->assertTrue($owner->ownerHasUnlimitedPlan());
    }

    public function test_admin_unlimited_plan_bypasses_property_limit(): void
    {
        $unlimitedPlan = SubscriptionPlan::factory()->create([
            'name' => 'Admin neierobežots',
            'slug' => 'admin-unlimited-two',
            'stripe_price_id' => null,
            'property_limit' => null,
            'display_price' => 'Bezmaksas',
            'is_public' => false,
            'is_unlimited' => true,
        ]);

        $owner = User::factory()->owner()->create([
            'subscription_plan_id' => $unlimitedPlan->id,
        ]);

        Property::factory()->count(3)->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->post(route('client.properties.store'), [
                'name' => 'Neierobežots īpašums',
                'notes' => null,
                'address' => 'Tērbatas iela 10',
                'city' => 'Rīga',
                'country' => 'Latvija',
                'price' => 125000,
                'type' => 'apartment',
                'acquired_at' => '2026-03-14',
            ])
            ->assertRedirect(route('client.properties.index'));

        $this->assertDatabaseHas('properties', [
            'name' => 'Neierobežots īpašums',
            'user_id' => $owner->id,
        ]);
    }

    public function test_owner_billing_page_is_available(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('client.billing.index'))
            ->assertOk()
            ->assertSee('Mans abonements');
    }

    public function test_admin_can_manage_subscription_plan_catalog(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('client.admin.owner-subscriptions.plans.store'), [
                'name' => 'Plus',
                'slug' => 'plus',
                'description' => 'Paplašināts plāns',
                'stripe_price_id' => 'price_plus_monthly',
                'display_price' => '29,00 EUR / mēnesī',
                'currency' => 'EUR',
                'billing_interval' => 'month',
                'property_limit' => 5,
                'trial_enabled' => '1',
                'trial_days' => 10,
                'is_active' => '1',
                'is_public' => '1',
                'sort_order' => 4,
            ])
            ->assertRedirect(route('client.admin.owner-subscriptions.index'));

        $this->assertDatabaseHas('subscription_plans', [
            'slug' => 'plus',
            'stripe_price_id' => 'price_plus_monthly',
            'property_limit' => 5,
        ]);
    }
}

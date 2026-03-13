<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerPropertyCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_update_and_delete_property(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->post(route('client.properties.store'), [
                'name' => 'Centra dzīvoklis',
                'notes' => 'Plašs un gaišs.',
                'address' => 'Brīvības iela 10',
                'city' => 'Rīga',
                'country' => 'Latvija',
                'price' => '125000.50',
                'type' => PropertyType::Apartment->value,
                'acquired_at' => '2024-06-15',
            ])
            ->assertRedirect(route('client.properties.index'));

        $property = Property::first();

        $this->assertNotNull($property);
        $this->assertSame($owner->id, $property->user_id);
        $this->assertSame(PropertyType::Apartment, $property->type);

        $this->actingAs($owner)
            ->put(route('client.properties.update', $property), [
                'name' => 'Centra dzīvoklis premium',
                'notes' => 'Atjaunināts apraksts.',
                'address' => 'Brīvības iela 10',
                'city' => 'Rīga',
                'country' => 'Latvija',
                'price' => '130000.00',
                'type' => PropertyType::Apartment->value,
                'acquired_at' => '2024-06-20',
            ])
            ->assertRedirect(route('client.properties.index'));

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'name' => 'Centra dzīvoklis premium',
            'price' => '130000.00',
        ]);

        $this->actingAs($owner)
            ->delete(route('client.properties.destroy', $property))
            ->assertRedirect(route('client.properties.index'));

        $this->assertDatabaseMissing('properties', [
            'id' => $property->id,
        ]);
    }

    public function test_owner_sees_only_their_own_properties(): void
    {
        $owner = User::factory()->owner()->create();
        $otherOwner = User::factory()->owner()->create();

        $ownedProperty = Property::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Mans īpašums',
        ]);

        Property::factory()->create([
            'user_id' => $otherOwner->id,
            'name' => 'Svešs īpašums',
        ]);

        $this->actingAs($owner)
            ->get(route('client.properties.index'))
            ->assertOk()
            ->assertSee($ownedProperty->name)
            ->assertDontSee('Svešs īpašums');
    }

    public function test_owner_property_index_renders_datatable_markup(): void
    {
        $owner = User::factory()->owner()->create();

        Property::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Alfa īpašums',
        ]);

        $this->actingAs($owner)
            ->get(route('client.properties.index'))
            ->assertOk()
            ->assertSee('Alfa īpašums')
            ->assertSee('data-datatable', false);
    }

    public function test_owner_property_index_renders_all_owner_records_for_client_side_datatable(): void
    {
        $owner = User::factory()->owner()->create();

        Property::factory()->count(25)->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->get(route('client.properties.index'))
            ->assertOk()
            ->assertViewHas('properties', fn ($properties) => $properties->count() === 25);
    }

    public function test_owner_cannot_edit_another_owners_property(): void
    {
        $owner = User::factory()->owner()->create();
        $otherOwner = User::factory()->owner()->create();

        $property = Property::factory()->create([
            'user_id' => $otherOwner->id,
        ]);

        $this->actingAs($owner)
            ->get(route('client.properties.edit', $property))
            ->assertNotFound();
    }

    public function test_non_owner_roles_cannot_access_owner_property_routes(): void
    {
        foreach ([UserRole::Admin, UserRole::Tenant] as $role) {
            $user = User::factory()->create([
                'role' => $role,
            ]);

            $this->actingAs($user)
                ->get(route('client.properties.index'))
                ->assertForbidden();

            auth()->logout();
        }
    }
}

<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_exports_page_and_download_csv_and_xlsx(): void
    {
        $owner = User::factory()->owner()->create();
        $property = Property::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Eksporta īpašums',
        ]);

        PropertyUnit::factory()->create([
            'property_id' => $property->id,
            'name' => 'Eksporta vienība',
        ]);

        TenantProfile::factory()->create([
            'owner_id' => $owner->id,
            'full_name' => 'Eksporta īrnieks',
        ]);

        $this->actingAs($owner)
            ->get(route('client.exports.index'))
            ->assertOk()
            ->assertSee('Datu eksports')
            ->assertSee('Īpašumi')
            ->assertSee('Vienības');

        $csvResponse = $this->actingAs($owner)
            ->post(route('client.exports.download'), [
                'dataset' => 'properties',
                'format' => 'csv',
            ]);

        $csvResponse->assertOk();
        $csvResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Eksporta īpašums', $csvResponse->streamedContent());

        $xlsxResponse = $this->actingAs($owner)
            ->post(route('client.exports.download'), [
                'dataset' => 'tenants',
                'format' => 'xlsx',
            ]);

        $xlsxResponse->assertOk();
        $xlsxResponse->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringStartsWith('PK', $xlsxResponse->streamedContent());
    }

    public function test_non_owner_roles_cannot_access_owner_export_routes(): void
    {
        foreach ([UserRole::Admin, UserRole::Tenant] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('client.exports.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->post(route('client.exports.download'), [
                    'dataset' => 'properties',
                    'format' => 'csv',
                ])
                ->assertForbidden();

            auth()->logout();
        }
    }
}

<?php

namespace Tests\Feature;

use App\Enums\LeaseStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_registration_links_existing_tenant_profile_by_email(): void
    {
        $owner = User::factory()->owner()->create();
        $tenantProfile = TenantProfile::factory()->create([
            'owner_id' => $owner->id,
            'email' => 'tenant-linked@example.com',
            'user_id' => null,
        ]);

        $this->post(route('client.register.store'), [
            'name' => 'Linked Tenant',
            'email' => 'tenant-linked@example.com',
            'role' => UserRole::Tenant->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('client.panel'));

        $user = User::query()->firstWhere('email', 'tenant-linked@example.com');

        $this->assertNotNull($user);
        $this->assertSame($user->id, $tenantProfile->fresh()->user_id);
    }

    public function test_linked_tenant_can_view_contracts_invoices_and_meters(): void
    {
        [$tenant, $property, $unit, $invoice, $meter] = $this->tenantRentalContext();

        $this->actingAs($tenant)
            ->get(route('client.panel'))
            ->assertOk()
            ->assertSee('Mani līgumi')
            ->assertSee($property->name)
            ->assertSee($invoice->number);

        $this->actingAs($tenant)
            ->get(route('client.tenant-leases.index'))
            ->assertOk()
            ->assertSee($unit->name);

        $this->actingAs($tenant)
            ->get(route('client.tenant-invoices.index'))
            ->assertOk()
            ->assertSee($invoice->number);

        $this->actingAs($tenant)
            ->get(route('client.tenant-meters.index'))
            ->assertOk()
            ->assertSee($meter->name);
    }

    public function test_tenant_can_submit_meter_reading_for_owned_active_unit(): void
    {
        [$tenant, $property, $unit, $invoice, $meter] = $this->tenantRentalContext();

        $this->actingAs($tenant)
            ->post(route('client.tenant-meter-readings.store', $meter), [
                'reading_date' => '2026-03-13',
                'value' => '145.700',
                'notes' => 'Pašdeklarēts rādījums',
            ])
            ->assertRedirect(route('client.tenant-meters.index'));

        $this->assertDatabaseHas('meter_readings', [
            'meter_id' => $meter->id,
            'reading_date' => '2026-03-13 00:00:00',
            'value' => 145.700,
            'notes' => 'Pašdeklarēts rādījums',
        ]);
    }

    public function test_tenant_can_submit_reading_only_for_today_or_previous_three_days(): void
    {
        Carbon::setTestNow('2026-03-13 10:00:00');
        [$tenant, $property, $unit, $invoice, $meter] = $this->tenantRentalContext();

        $this->actingAs($tenant)
            ->from(route('client.tenant-meters.index'))
            ->post(route('client.tenant-meter-readings.store', $meter), [
                'reading_date' => '2026-03-09',
                'value' => '100.000',
            ])
            ->assertRedirect(route('client.tenant-meters.index'))
            ->assertSessionHasErrors('reading_date');

        $this->actingAs($tenant)
            ->from(route('client.tenant-meters.index'))
            ->post(route('client.tenant-meter-readings.store', $meter), [
                'reading_date' => '2026-03-14',
                'value' => '100.000',
            ])
            ->assertRedirect(route('client.tenant-meters.index'))
            ->assertSessionHasErrors('reading_date');

        $this->assertDatabaseMissing('meter_readings', [
            'meter_id' => $meter->id,
            'reading_date' => '2026-03-09 00:00:00',
        ]);
        $this->assertDatabaseMissing('meter_readings', [
            'meter_id' => $meter->id,
            'reading_date' => '2026-03-14 00:00:00',
        ]);

        Carbon::setTestNow();
    }

    public function test_tenant_cannot_submit_reading_lower_than_last_record_but_equal_is_allowed(): void
    {
        Carbon::setTestNow('2026-03-13 10:00:00');
        [$tenant, $property, $unit, $invoice, $meter] = $this->tenantRentalContext();

        MeterReading::factory()->create([
            'meter_id' => $meter->id,
            'reading_date' => '2026-03-12',
            'value' => 120.500,
        ]);

        $this->actingAs($tenant)
            ->post(route('client.tenant-meter-readings.store', $meter), [
                'reading_date' => '2026-03-13',
                'value' => '120.500',
            ])
            ->assertRedirect(route('client.tenant-meters.index'));

        $this->actingAs($tenant)
            ->from(route('client.tenant-meters.index'))
            ->post(route('client.tenant-meter-readings.store', $meter), [
                'reading_date' => '2026-03-13',
                'value' => '120.499',
            ])
            ->assertRedirect(route('client.tenant-meters.index'))
            ->assertSessionHasErrors('value');

        $this->assertDatabaseHas('meter_readings', [
            'meter_id' => $meter->id,
            'reading_date' => '2026-03-13 00:00:00',
            'value' => 120.500,
        ]);

        $this->assertDatabaseMissing('meter_readings', [
            'meter_id' => $meter->id,
            'reading_date' => '2026-03-13 00:00:00',
            'value' => 120.499,
        ]);

        Carbon::setTestNow();
    }

    public function test_tenant_validation_uses_highest_value_on_latest_date_when_multiple_readings_exist(): void
    {
        Carbon::setTestNow('2026-03-13 10:00:00');
        [$tenant, $property, $unit, $invoice, $meter] = $this->tenantRentalContext();

        MeterReading::factory()->create([
            'meter_id' => $meter->id,
            'reading_date' => '2026-03-12',
            'value' => 180.000,
        ]);

        MeterReading::factory()->create([
            'meter_id' => $meter->id,
            'reading_date' => '2026-03-12',
            'value' => 220.000,
        ]);

        $this->actingAs($tenant)
            ->from(route('client.tenant-meters.index'))
            ->post(route('client.tenant-meter-readings.store', $meter), [
                'reading_date' => '2026-03-13',
                'value' => '200.000',
            ])
            ->assertRedirect(route('client.tenant-meters.index'))
            ->assertSessionHasErrors('value');

        $this->actingAs($tenant)
            ->post(route('client.tenant-meter-readings.store', $meter), [
                'reading_date' => '2026-03-13',
                'value' => '220.000',
            ])
            ->assertRedirect(route('client.tenant-meters.index'));

        $this->assertDatabaseHas('meter_readings', [
            'meter_id' => $meter->id,
            'reading_date' => '2026-03-13 00:00:00',
            'value' => 220.000,
        ]);

        Carbon::setTestNow();
    }

    public function test_tenant_cannot_access_other_tenant_invoice_or_submit_other_unit_reading(): void
    {
        [$tenant] = $this->tenantRentalContext();
        [$otherTenant, $otherProperty, $otherUnit, $otherInvoice, $otherMeter] = $this->tenantRentalContext('other-tenant@example.com');

        $this->actingAs($tenant)
            ->get(route('client.tenant-invoices.show', $otherInvoice))
            ->assertNotFound();

        $this->actingAs($tenant)
            ->post(route('client.tenant-meter-readings.store', $otherMeter), [
                'reading_date' => '2026-03-13',
                'value' => '99.100',
            ])
            ->assertNotFound();
    }

    /**
     * @return array{0: User, 1: Property, 2: PropertyUnit, 3: Invoice, 4: Meter}
     */
    private function tenantRentalContext(string $email = 'tenant@example.com'): array
    {
        $owner = User::factory()->owner()->create();
        $tenant = User::factory()->tenant()->create([
            'email' => $email,
        ]);

        $tenantProfile = TenantProfile::factory()->create([
            'owner_id' => $owner->id,
            'user_id' => $tenant->id,
            'email' => $email,
            'full_name' => 'Tenant Person',
        ]);

        $property = Property::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Brivibas 12',
        ]);

        $unit = PropertyUnit::factory()->create([
            'property_id' => $property->id,
            'name' => 'Dzīvoklis 5',
            'code' => 'DZ5',
        ]);

        $lease = Lease::factory()->create([
            'property_unit_id' => $unit->id,
            'tenant_profile_id' => $tenantProfile->id,
            'status' => LeaseStatus::Active,
        ]);

        $invoice = Invoice::factory()->create([
            'lease_id' => $lease->id,
            'number' => 'INV-1001',
        ]);

        $meter = Meter::factory()->create([
            'property_unit_id' => $unit->id,
            'name' => 'Elektrība',
            'unit' => 'kWh',
            'is_active' => true,
        ]);

        return [$tenant, $property, $unit, $invoice, $meter];
    }
}

<?php

namespace Tests\Feature;

use App\Enums\ChargeFrequency;
use App\Enums\LeaseStatus;
use App\Enums\MeterReadingSource;
use App\Enums\MeterType;
use App\Enums\PropertyUnitStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\LeaseChargeRule;
use App\Models\Meter;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnerRentalManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_tenant_unit_and_lease(): void
    {
        $owner = User::factory()->owner()->create();
        $property = Property::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Krasta nams',
        ]);

        $this->actingAs($owner)
            ->post(route('client.tenants.store'), [
                'full_name' => 'SIA Nomnieks',
                'company_name' => 'SIA Nomnieks',
                'email' => 'tenant@example.com',
                'phone' => '+37120000000',
                'personal_code' => '',
                'registration_number' => '40200000000',
                'notes' => 'Galvenais konts',
            ])
            ->assertRedirect(route('client.tenants.index'));

        $tenant = TenantProfile::query()->first();

        $this->actingAs($owner)
            ->post(route('client.units.store'), [
                'property_id' => $property->id,
                'name' => '2. stāva birojs',
                'code' => 'B-201',
                'notes' => 'Ar atsevišķu ieeju',
                'status' => PropertyUnitStatus::Vacant->value,
                'area' => '85.50',
                'unit_type' => 'Birojs',
                'is_active' => '1',
            ])
            ->assertRedirect(route('client.units.index'));

        $unit = PropertyUnit::query()->first();

        $this->actingAs($owner)
            ->post(route('client.leases.store'), [
                'property_unit_id' => $unit->id,
                'tenant_profile_id' => $tenant->id,
                'start_date' => '2025-01-01',
                'end_date' => '',
                'billing_start_date' => '2025-01-01',
                'due_day' => 10,
                'currency' => 'EUR',
                'status' => LeaseStatus::Active->value,
                'deposit' => '500.00',
                'notes' => '12 mēnešu līgums',
            ])
            ->assertRedirect();

        $lease = Lease::query()->first();

        $this->assertNotNull($tenant);
        $this->assertNotNull($unit);
        $this->assertNotNull($lease);

        $this->assertDatabaseHas('tenant_profiles', [
            'id' => $tenant->id,
            'owner_id' => $owner->id,
            'full_name' => 'SIA Nomnieks',
        ]);

        $this->assertDatabaseHas('property_units', [
            'id' => $unit->id,
            'property_id' => $property->id,
            'name' => '2. stāva birojs',
            'status' => PropertyUnitStatus::Occupied->value,
        ]);

        $this->assertDatabaseHas('leases', [
            'id' => $lease->id,
            'property_unit_id' => $unit->id,
            'tenant_profile_id' => $tenant->id,
            'status' => LeaseStatus::Active->value,
        ]);
    }

    public function test_owner_cannot_attach_lease_to_another_owners_unit(): void
    {
        $owner = User::factory()->owner()->create();
        $otherOwner = User::factory()->owner()->create();

        $foreignUnit = PropertyUnit::factory()->create([
            'property_id' => Property::factory()->create(['user_id' => $otherOwner->id])->id,
        ]);

        $tenant = TenantProfile::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->from(route('client.leases.create'))
            ->post(route('client.leases.store'), [
                'property_unit_id' => $foreignUnit->id,
                'tenant_profile_id' => $tenant->id,
                'start_date' => '2025-01-01',
                'end_date' => '',
                'billing_start_date' => '2025-01-01',
                'due_day' => 10,
                'currency' => 'EUR',
                'status' => LeaseStatus::Active->value,
            ])
            ->assertRedirect(route('client.leases.create'))
            ->assertSessionHasErrors('property_unit_id');
    }

    public function test_owner_cannot_create_overlapping_lease_for_same_unit(): void
    {
        $owner = User::factory()->owner()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);
        $unit = PropertyUnit::factory()->create(['property_id' => $property->id]);
        $tenantA = TenantProfile::factory()->create(['owner_id' => $owner->id]);
        $tenantB = TenantProfile::factory()->create(['owner_id' => $owner->id]);

        Lease::factory()->create([
            'property_unit_id' => $unit->id,
            'tenant_profile_id' => $tenantA->id,
            'start_date' => '2025-01-01',
            'end_date' => null,
            'billing_start_date' => '2025-01-01',
            'status' => LeaseStatus::Active,
        ]);

        $this->actingAs($owner)
            ->from(route('client.leases.create'))
            ->post(route('client.leases.store'), [
                'property_unit_id' => $unit->id,
                'tenant_profile_id' => $tenantB->id,
                'start_date' => '2025-02-01',
                'end_date' => '',
                'billing_start_date' => '2025-02-01',
                'due_day' => 10,
                'currency' => 'EUR',
                'status' => LeaseStatus::Draft->value,
            ])
            ->assertRedirect(route('client.leases.create'))
            ->assertSessionHasErrors('property_unit_id');
    }

    public function test_deleting_property_cascades_rental_management_records(): void
    {
        $owner = User::factory()->owner()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);
        $tenant = TenantProfile::factory()->create(['owner_id' => $owner->id]);
        $unit = PropertyUnit::factory()->create(['property_id' => $property->id]);
        $lease = Lease::factory()->create([
            'property_unit_id' => $unit->id,
            'tenant_profile_id' => $tenant->id,
        ]);
        $chargeRule = LeaseChargeRule::factory()->create([
            'lease_id' => $lease->id,
            'frequency' => ChargeFrequency::Monthly,
        ]);
        $invoice = Invoice::factory()->create([
            'lease_id' => $lease->id,
        ]);
        $invoice->lines()->create([
            'description' => 'Īres maksa',
            'quantity' => 1,
            'unit_price' => 250,
            'tax' => 0,
            'line_total' => 250,
            'source_type' => LeaseChargeRule::class,
            'source_id' => $chargeRule->id,
        ]);
        $invoice->payments()->create([
            'paid_at' => '2025-03-10',
            'amount' => 250,
            'method' => 'bank_transfer',
        ]);
        $meter = Meter::factory()->create([
            'property_unit_id' => $unit->id,
            'type' => MeterType::Electricity,
        ]);
        $meter->readings()->create([
            'reading_date' => '2025-03-01',
            'value' => 150.500,
            'source' => MeterReadingSource::Manual,
        ]);
        $invoice->reminders()->create([
            'kind' => 'invoice',
            'channel' => 'email',
            'status' => 'sent',
            'recipient' => 'tenant@example.com',
        ]);

        $this->actingAs($owner)
            ->delete(route('client.properties.destroy', $property))
            ->assertRedirect(route('client.properties.index'));

        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
        $this->assertDatabaseMissing('property_units', ['id' => $unit->id]);
        $this->assertDatabaseMissing('leases', ['id' => $lease->id]);
        $this->assertDatabaseMissing('lease_charge_rules', ['id' => $chargeRule->id]);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('meters', ['id' => $meter->id]);
        $this->assertDatabaseMissing('meter_readings', ['meter_id' => $meter->id]);
        $this->assertDatabaseMissing('invoice_reminders', ['invoice_id' => $invoice->id]);
    }

    public function test_owner_can_delete_invoice_with_related_records(): void
    {
        $owner = User::factory()->owner()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);
        $unit = PropertyUnit::factory()->create(['property_id' => $property->id]);
        $tenant = TenantProfile::factory()->create(['owner_id' => $owner->id]);
        $lease = Lease::factory()->create([
            'property_unit_id' => $unit->id,
            'tenant_profile_id' => $tenant->id,
        ]);
        $invoice = Invoice::factory()->create([
            'lease_id' => $lease->id,
            'number' => 'INV-DELETE-1',
        ]);

        $invoice->lines()->create([
            'description' => 'Īres maksa',
            'quantity' => 1,
            'unit_price' => 100,
            'tax' => 0,
            'line_total' => 100,
        ]);
        $invoice->payments()->create([
            'paid_at' => '2025-03-10',
            'amount' => 50,
            'method' => 'bank_transfer',
        ]);
        $invoice->reminders()->create([
            'kind' => 'invoice',
            'channel' => 'email',
            'status' => 'sent',
            'recipient' => 'tenant@example.com',
        ]);

        $this->actingAs($owner)
            ->delete(route('client.invoices.destroy', $invoice))
            ->assertRedirect(route('client.invoices.index'));

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('invoice_lines', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseMissing('payments', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseMissing('invoice_reminders', ['invoice_id' => $invoice->id]);
    }

    public function test_owner_can_update_invoice_and_manage_custom_invoice_lines(): void
    {
        $owner = User::factory()->owner()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);
        $unit = PropertyUnit::factory()->create(['property_id' => $property->id]);
        $tenant = TenantProfile::factory()->create(['owner_id' => $owner->id]);
        $lease = Lease::factory()->create([
            'property_unit_id' => $unit->id,
            'tenant_profile_id' => $tenant->id,
        ]);
        $invoice = Invoice::factory()->create([
            'lease_id' => $lease->id,
            'number' => 'INV-LINE-1',
            'status' => 'issued',
        ]);

        $generatedLine = $invoice->lines()->create([
            'description' => 'Īres maksa',
            'quantity' => 1,
            'unit_price' => 500,
            'tax' => 0,
            'line_total' => 500,
            'source_type' => LeaseChargeRule::class,
            'source_id' => 999,
        ]);

        $this->actingAs($owner)
            ->put(route('client.invoices.update', $invoice), [
                'number' => 'INV-LINE-UPDATED',
                'issue_date' => '2025-01-05',
                'due_date' => '2025-01-15',
                'period_from' => '2025-01-01',
                'period_to' => '2025-01-31',
                'status' => 'issued',
            ])
            ->assertRedirect(route('client.invoices.show', $invoice));

        $this->actingAs($owner)
            ->post(route('client.invoice-lines.store', $invoice), [
                'description' => 'Līguma sagatavošana',
                'quantity' => 2,
                'unit_price' => 15,
                'tax' => 5,
            ])
            ->assertRedirect(route('client.invoices.show', $invoice));

        $customLine = $invoice->fresh()->lines()->whereNull('source_type')->first();

        $this->actingAs($owner)
            ->put(route('client.invoice-lines.update', [$invoice, $generatedLine]), [
                'description' => 'Īres maksa ar korekciju',
                'quantity' => 1,
                'unit_price' => 520,
                'tax' => 0,
            ])
            ->assertRedirect(route('client.invoices.show', $invoice));

        $this->actingAs($owner)
            ->delete(route('client.invoice-lines.destroy', [$invoice, $customLine]))
            ->assertRedirect(route('client.invoices.show', $invoice));

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'number' => 'INV-LINE-UPDATED',
        ]);

        $this->assertDatabaseHas('invoice_lines', [
            'id' => $generatedLine->id,
            'description' => 'Īres maksa ar korekciju',
            'line_total' => '520.00',
            'is_manual_override' => 1,
        ]);

        $this->assertDatabaseMissing('invoice_lines', [
            'id' => $customLine->id,
        ]);

        $this->assertSame('520.00', $invoice->fresh()->total);
    }

    public function test_owner_can_download_and_print_customized_invoice_document(): void
    {
        Storage::fake('public');

        $owner = User::factory()->owner()->create([
            'invoice_sender_name' => 'SIA MBC Solutions',
            'invoice_sender_address' => 'Stacijas iela 8-6, Grobiņa, LV-3430',
            'invoice_sender_registration_number' => '40203336819',
            'invoice_sender_vat_number' => 'LV40203336819',
            'invoice_sender_bank_name' => 'AS SWEDBANK',
            'invoice_sender_swift_code' => 'HABALV22',
            'invoice_sender_account_number' => 'LV49HABA0551051273335',
            'invoice_payment_terms_text' => '2025.gada 16. septembris (7 dienas) Pārskaitījums',
            'invoice_footer_text' => 'Rēķins sagatavots elektroniski un ir derīgs bez paraksta.',
            'invoice_vat_enabled' => true,
            'invoice_vat_rate' => 21,
        ]);

        Storage::disk('public')->put('invoice-logos/logo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9oNn14sAAAAASUVORK5CYII='));
        $owner->update(['invoice_logo_path' => 'invoice-logos/logo.png']);

        $property = Property::factory()->create(['user_id' => $owner->id]);
        $unit = PropertyUnit::factory()->create([
            'property_id' => $property->id,
            'name' => 'Birojs 27',
            'code' => 'MBC-27',
        ]);
        $tenant = TenantProfile::factory()->create([
            'owner_id' => $owner->id,
            'full_name' => 'SIA ESI DIGITAL',
            'billing_name' => 'SIA ESI DIGITAL',
            'billing_address' => 'Kuģinieku iela 5, Liepāja, LV-3401',
            'billing_registration_number' => '40203372345',
            'billing_vat_number' => 'LV40203372345',
            'billing_bank_name' => 'AS Citadele Banka',
            'billing_swift_code' => 'PARXLV22',
            'billing_account_number' => 'LV08PARX0027849070001',
        ]);
        $lease = Lease::factory()->create([
            'property_unit_id' => $unit->id,
            'tenant_profile_id' => $tenant->id,
        ]);
        $invoice = Invoice::factory()->create([
            'lease_id' => $lease->id,
            'number' => 'INV-DOCUMENT-1',
            'issue_date' => '2025-09-09',
            'due_date' => '2025-09-16',
            'period_from' => '2025-09-01',
            'period_to' => '2025-09-30',
            'subtotal' => 2000,
            'total' => 2420,
        ]);

        $invoice->lines()->create([
            'description' => 'LIAA projekts līguma nr. MBC-2025-04',
            'quantity' => 1,
            'unit_price' => 2000,
            'tax' => 0,
            'line_total' => 2000,
        ]);

        $this->actingAs($owner)
            ->get(route('client.invoices.print', $invoice))
            ->assertOk()
            ->assertSee('Rēķins Nr. INV-DOCUMENT-1')
            ->assertSee('SIA ESI DIGITAL')
            ->assertSee('SIA MBC Solutions')
            ->assertSee('Nosaukums')
            ->assertSee('PVN (21%)')
            ->assertSee('Rēķins sagatavots elektroniski un ir derīgs bez paraksta.');

        $downloadResponse = $this->actingAs($owner)
            ->get(route('client.invoices.download', $invoice));

        $downloadResponse->assertOk();
        $downloadResponse->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $downloadResponse->getContent());
    }

    public function test_non_owner_roles_cannot_access_owner_rental_routes(): void
    {
        foreach ([UserRole::Admin, UserRole::Tenant] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('client.units.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('client.tenants.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('client.leases.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('client.invoices.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('client.meters.index'))
                ->assertForbidden();

            auth()->logout();
        }
    }
}

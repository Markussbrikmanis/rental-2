<?php

namespace Tests\Feature;

use App\Enums\InvoiceKind;
use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\PropertyUnitStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_reports_with_core_metrics(): void
    {
        $owner = User::factory()->owner()->create();
        $propertyA = Property::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Centra nams',
            'price' => 100000,
        ]);
        $propertyB = Property::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Parka māja',
            'price' => 50000,
        ]);

        $unitA = PropertyUnit::factory()->create([
            'property_id' => $propertyA->id,
            'name' => 'A-1',
            'status' => PropertyUnitStatus::Occupied,
            'is_active' => true,
        ]);
        $unitB = PropertyUnit::factory()->create([
            'property_id' => $propertyB->id,
            'name' => 'B-1',
            'status' => PropertyUnitStatus::Vacant,
            'is_active' => true,
        ]);

        $tenant = TenantProfile::factory()->create([
            'owner_id' => $owner->id,
            'full_name' => 'SIA Īrnieks',
        ]);

        $leaseA = Lease::factory()->create([
            'property_unit_id' => $unitA->id,
            'tenant_profile_id' => $tenant->id,
            'start_date' => '2025-01-01',
            'billing_start_date' => '2025-01-01',
            'status' => LeaseStatus::Active,
        ]);

        $invoicePaid = Invoice::factory()->create([
            'lease_id' => $leaseA->id,
            'number' => 'INV-REP-1',
            'kind' => InvoiceKind::Standard,
            'status' => InvoiceStatus::Issued,
            'issue_date' => '2025-01-15',
            'due_date' => '2025-01-25',
            'period_from' => '2025-01-01',
            'period_to' => '2025-01-31',
            'subtotal' => 1000,
            'total' => 1000,
        ]);
        $invoicePaid->lines()->create([
            'description' => 'Īre janvārī',
            'quantity' => 1,
            'unit_price' => 1000,
            'tax' => 0,
            'line_total' => 1000,
        ]);

        Payment::query()->create([
            'invoice_id' => $invoicePaid->id,
            'paid_at' => '2025-01-20',
            'amount' => 1000,
            'method' => 'bank_transfer',
        ]);

        $invoiceOpen = Invoice::factory()->create([
            'lease_id' => $leaseA->id,
            'number' => 'INV-REP-2',
            'kind' => InvoiceKind::Utility,
            'status' => InvoiceStatus::Issued,
            'issue_date' => '2025-02-10',
            'due_date' => '2025-02-20',
            'period_from' => '2025-02-01',
            'period_to' => '2025-02-28',
            'subtotal' => 300,
            'total' => 300,
        ]);
        $invoiceOpen->lines()->create([
            'description' => 'Komunālie februārī',
            'quantity' => 1,
            'unit_price' => 300,
            'tax' => 0,
            'line_total' => 300,
        ]);

        $response = $this->actingAs($owner)
            ->get(route('client.reports.index', [
                'period' => 'custom',
                'from' => '2025-01-01',
                'to' => '2025-03-31',
            ]));

        $response->assertOk()
            ->assertSee('Atskaites')
            ->assertSee('Centra nams')
            ->assertSee('Parka māja')
            ->assertSee('SIA Īrnieks')
            ->assertSee('1 300,00 EUR')
            ->assertSee('1 000,00 EUR')
            ->assertSee('300,00 EUR')
            ->assertSee('50,00%');
    }

    public function test_owner_can_filter_reports_and_export_current_report(): void
    {
        $owner = User::factory()->owner()->create();
        $propertyA = Property::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Filtra nams',
        ]);
        $propertyB = Property::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Cits nams',
        ]);

        $unitA = PropertyUnit::factory()->create([
            'property_id' => $propertyA->id,
            'name' => 'U-A',
        ]);
        $unitB = PropertyUnit::factory()->create([
            'property_id' => $propertyB->id,
            'name' => 'U-B',
        ]);

        $tenant = TenantProfile::factory()->create([
            'owner_id' => $owner->id,
            'full_name' => 'Filtra īrnieks',
        ]);

        $leaseA = Lease::factory()->create([
            'property_unit_id' => $unitA->id,
            'tenant_profile_id' => $tenant->id,
            'status' => LeaseStatus::Active,
            'start_date' => '2025-01-01',
            'billing_start_date' => '2025-01-01',
        ]);
        $leaseB = Lease::factory()->create([
            'property_unit_id' => $unitB->id,
            'tenant_profile_id' => $tenant->id,
            'status' => LeaseStatus::Active,
            'start_date' => '2025-01-01',
            'billing_start_date' => '2025-01-01',
        ]);

        $invoiceA = Invoice::factory()->create([
            'lease_id' => $leaseA->id,
            'number' => 'INV-FILTER-A',
            'issue_date' => '2025-01-10',
            'due_date' => '2025-01-20',
            'period_from' => '2025-01-01',
            'period_to' => '2025-01-31',
            'subtotal' => 500,
            'total' => 500,
        ]);
        $invoiceA->lines()->create([
            'description' => 'A',
            'quantity' => 1,
            'unit_price' => 500,
            'tax' => 0,
            'line_total' => 500,
        ]);

        $invoiceB = Invoice::factory()->create([
            'lease_id' => $leaseB->id,
            'number' => 'INV-FILTER-B',
            'issue_date' => '2025-01-10',
            'due_date' => '2025-01-20',
            'period_from' => '2025-01-01',
            'period_to' => '2025-01-31',
            'subtotal' => 900,
            'total' => 900,
        ]);
        $invoiceB->lines()->create([
            'description' => 'B',
            'quantity' => 1,
            'unit_price' => 900,
            'tax' => 0,
            'line_total' => 900,
        ]);

        Payment::query()->create([
            'invoice_id' => $invoiceA->id,
            'paid_at' => '2025-01-15',
            'amount' => 500,
            'method' => 'bank_transfer',
        ]);

        $response = $this->actingAs($owner)
            ->get(route('client.reports.index', [
                'period' => 'custom',
                'from' => '2025-01-01',
                'to' => '2025-01-31',
                'property_id' => $propertyA->id,
                'unit_id' => $unitA->id,
                'view' => 'charts',
            ]));

        $response->assertOk()
            ->assertSee('Filtra nams')
            ->assertDontSee('INV-FILTER-B')
            ->assertSee('500,00 EUR')
            ->assertDontSee('900,00 EUR')
            ->assertSee('Grafiki');

        $pdfResponse = $this->actingAs($owner)
            ->get(route('client.reports.export', [
                'period' => 'custom',
                'from' => '2025-01-01',
                'to' => '2025-01-31',
                'property_id' => $propertyA->id,
                'unit_id' => $unitA->id,
                'format' => 'pdf',
            ]));

        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');

        $xlsxResponse = $this->actingAs($owner)
            ->get(route('client.reports.export', [
                'period' => 'custom',
                'from' => '2025-01-01',
                'to' => '2025-01-31',
                'property_id' => $propertyA->id,
                'format' => 'xlsx',
            ]));

        $xlsxResponse->assertOk();
        $xlsxResponse->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $csvResponse = $this->actingAs($owner)
            ->get(route('client.reports.export', [
                'period' => 'custom',
                'from' => '2025-01-01',
                'to' => '2025-01-31',
                'property_id' => $propertyA->id,
                'format' => 'csv',
            ]));

        $csvResponse->assertOk();
        $csvResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Filtra nams', $csvResponse->streamedContent());
        $this->assertStringNotContainsString('Cits nams', $csvResponse->streamedContent());
    }

    public function test_non_owner_roles_cannot_access_owner_reports(): void
    {
        foreach ([UserRole::Admin, UserRole::Tenant] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('client.reports.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('client.reports.export', ['format' => 'csv']))
                ->assertForbidden();

            auth()->logout();
        }
    }
}

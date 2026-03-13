<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_dashboard_shows_operational_summary_and_quick_actions(): void
    {
        $owner = User::factory()->owner()->create();
        $property = Property::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Dashboard nams',
        ]);
        $unit = PropertyUnit::factory()->create([
            'property_id' => $property->id,
            'name' => 'D-1',
        ]);
        $tenant = TenantProfile::factory()->create([
            'owner_id' => $owner->id,
            'full_name' => 'Dashboard īrnieks',
        ]);
        $lease = Lease::factory()->create([
            'property_unit_id' => $unit->id,
            'tenant_profile_id' => $tenant->id,
            'status' => LeaseStatus::Active,
        ]);

        $paidInvoice = Invoice::factory()->create([
            'lease_id' => $lease->id,
            'number' => 'INV-DASH-PAID',
            'status' => InvoiceStatus::Issued,
            'issue_date' => now()->startOfMonth()->addDays(2),
            'due_date' => now()->startOfMonth()->addDays(10),
            'period_from' => now()->startOfMonth(),
            'period_to' => now()->endOfMonth(),
            'subtotal' => 500,
            'total' => 500,
        ]);
        $paidInvoice->lines()->create([
            'description' => 'Īre',
            'quantity' => 1,
            'unit_price' => 500,
            'tax' => 0,
            'line_total' => 500,
        ]);
        Payment::query()->create([
            'invoice_id' => $paidInvoice->id,
            'paid_at' => now()->startOfMonth()->addDays(5),
            'amount' => 500,
            'method' => 'bank_transfer',
        ]);

        $overdueInvoice = Invoice::factory()->create([
            'lease_id' => $lease->id,
            'number' => 'INV-DASH-OPEN',
            'status' => InvoiceStatus::Issued,
            'issue_date' => now()->subMonth()->startOfMonth(),
            'due_date' => now()->subDays(5),
            'period_from' => now()->subMonth()->startOfMonth(),
            'period_to' => now()->subMonth()->endOfMonth(),
            'subtotal' => 300,
            'total' => 300,
        ]);
        $overdueInvoice->lines()->create([
            'description' => 'Komunālie',
            'quantity' => 1,
            'unit_price' => 300,
            'tax' => 0,
            'line_total' => 300,
        ]);

        $this->actingAs($owner)
            ->get(route('client.panel'))
            ->assertOk()
            ->assertSee('Saņemts šomēnes')
            ->assertSee('Jaunākie rēķini')
            ->assertSee('Kavētie rēķini')
            ->assertSee('Dashboard nams')
            ->assertSee('Dashboard īrnieks')
            ->assertSee('INV-DASH-OPEN')
            ->assertSee('Atvērt atskaites')
            ->assertSee('Atvērt eksportu');
    }
}

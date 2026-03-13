<?php

namespace Tests\Feature;

use App\Enums\ChargeFrequency;
use App\Enums\ChargeIntervalUnit;
use App\Enums\InvoiceKind;
use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\MeterReadingSource;
use App\Enums\MeterType;
use App\Enums\UtilityBillingMode;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\LeaseChargeRule;
use App\Models\Meter;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\TenantProfile;
use App\Models\User;
use App\Services\InvoiceGenerationService;
use App\Services\InvoiceStatusService;
use App\Services\MeterConsumptionService;
use App\Services\ReminderDispatchService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BillingAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_generation_supports_monthly_yearly_and_custom_intervals_without_duplicates(): void
    {
        $lease = $this->createLeaseWithBillingContext();

        LeaseChargeRule::factory()->create([
            'lease_id' => $lease->id,
            'name' => 'Mēneša īre',
            'amount' => 500,
            'frequency' => ChargeFrequency::Monthly,
            'interval_count' => 1,
            'interval_unit' => null,
            'effective_from' => '2025-01-01',
        ]);

        LeaseChargeRule::factory()->create([
            'lease_id' => $lease->id,
            'name' => 'Gada apdrošināšana',
            'amount' => 1200,
            'frequency' => ChargeFrequency::Yearly,
            'interval_count' => 1,
            'interval_unit' => null,
            'effective_from' => '2025-01-01',
        ]);

        LeaseChargeRule::factory()->create([
            'lease_id' => $lease->id,
            'name' => 'Divu nedēļu pakalpojums',
            'amount' => 50,
            'frequency' => ChargeFrequency::CustomInterval,
            'interval_count' => 2,
            'interval_unit' => ChargeIntervalUnit::Week,
            'effective_from' => '2025-01-01',
        ]);

        /** @var InvoiceGenerationService $service */
        $service = app(InvoiceGenerationService::class);

        $firstRun = $service->generateForLease($lease, Carbon::parse('2025-03-31'));
        $secondRun = $service->generateForLease($lease->fresh(), Carbon::parse('2025-03-31'));

        $this->assertCount(11, $firstRun);
        $this->assertCount(11, $secondRun);
        $this->assertDatabaseCount('invoices', 11);

        $monthlyInvoice = Invoice::query()
            ->where('lease_id', $lease->id)
            ->whereDate('period_from', '2025-01-01')
            ->whereDate('period_to', '2025-01-31')
            ->first();

        $yearlyInvoice = Invoice::query()
            ->where('lease_id', $lease->id)
            ->whereDate('period_from', '2025-01-01')
            ->whereDate('period_to', '2025-12-31')
            ->first();

        $customInvoice = Invoice::query()
            ->where('lease_id', $lease->id)
            ->whereDate('period_from', '2025-01-01')
            ->whereDate('period_to', '2025-01-14')
            ->first();

        $this->assertNotNull($monthlyInvoice);
        $this->assertNotNull($yearlyInvoice);
        $this->assertNotNull($customInvoice);

        $this->assertSame(InvoiceStatus::Overdue, $monthlyInvoice->status);
        $this->assertSame('500.00', $monthlyInvoice->total);
        $this->assertSame('1200.00', $yearlyInvoice->total);
        $this->assertSame('50.00', $customInvoice->total);
    }

    public function test_utility_charges_can_be_included_in_standard_invoice(): void
    {
        $lease = $this->createLeaseWithBillingContext();

        LeaseChargeRule::factory()->create([
            'lease_id' => $lease->id,
            'amount' => 500,
            'effective_from' => '2025-01-01',
        ]);

        $meter = Meter::factory()->create([
            'property_unit_id' => $lease->property_unit_id,
            'name' => 'Elektrība',
            'type' => MeterType::Electricity,
            'unit' => 'kWh',
            'utility_billing_mode' => UtilityBillingMode::Included,
            'rate_per_unit' => 0.25,
        ]);

        $meter->readings()->create([
            'reading_date' => '2025-01-01',
            'value' => 100,
            'source' => MeterReadingSource::Manual,
        ]);

        $meter->readings()->create([
            'reading_date' => '2025-01-31',
            'value' => 140,
            'source' => MeterReadingSource::Manual,
        ]);

        /** @var InvoiceGenerationService $service */
        $service = app(InvoiceGenerationService::class);
        $generated = $service->generateForLease($lease, Carbon::parse('2025-01-31'));

        $this->assertCount(1, $generated);
        $invoice = $generated->first();

        $this->assertSame(InvoiceKind::Standard, $invoice->kind);
        $this->assertSame('510.00', $invoice->total);
        $this->assertDatabaseHas('invoice_lines', [
            'invoice_id' => $invoice->id,
            'description' => 'Elektrība',
            'quantity' => '40.00',
            'unit_price' => '0.2500',
            'line_total' => '10.00',
        ]);
    }

    public function test_utility_charges_can_be_generated_as_separate_invoice(): void
    {
        $lease = $this->createLeaseWithBillingContext();

        LeaseChargeRule::factory()->create([
            'lease_id' => $lease->id,
            'amount' => 500,
            'effective_from' => '2025-01-01',
        ]);

        $meter = Meter::factory()->create([
            'property_unit_id' => $lease->property_unit_id,
            'name' => 'Aukstais ūdens',
            'type' => MeterType::ColdWater,
            'unit' => 'm3',
            'utility_billing_mode' => UtilityBillingMode::Separate,
            'rate_per_unit' => 1.50,
        ]);

        $meter->readings()->create([
            'reading_date' => '2025-01-01',
            'value' => 10,
            'source' => MeterReadingSource::Manual,
        ]);

        $meter->readings()->create([
            'reading_date' => '2025-01-31',
            'value' => 18,
            'source' => MeterReadingSource::Manual,
        ]);

        /** @var InvoiceGenerationService $service */
        $service = app(InvoiceGenerationService::class);
        $generated = $service->generateForLease($lease, Carbon::parse('2025-01-31'));

        $this->assertCount(2, $generated);
        $this->assertDatabaseCount('invoices', 2);

        $standardInvoice = Invoice::query()->where('kind', InvoiceKind::Standard->value)->first();
        $utilityInvoice = Invoice::query()->where('kind', InvoiceKind::Utility->value)->first();

        $this->assertNotNull($standardInvoice);
        $this->assertNotNull($utilityInvoice);
        $this->assertSame('500.00', $standardInvoice->total);
        $this->assertSame('12.00', $utilityInvoice->total);
        $this->assertDatabaseHas('invoice_lines', [
            'invoice_id' => $utilityInvoice->id,
            'description' => 'Aukstais ūdens',
            'quantity' => '8.00',
            'unit_price' => '1.5000',
            'line_total' => '12.00',
        ]);
    }

    public function test_utility_charges_can_be_ignored(): void
    {
        $lease = $this->createLeaseWithBillingContext();

        LeaseChargeRule::factory()->create([
            'lease_id' => $lease->id,
            'amount' => 500,
            'effective_from' => '2025-01-01',
        ]);

        $meter = Meter::factory()->create([
            'property_unit_id' => $lease->property_unit_id,
            'name' => 'Gāze',
            'type' => MeterType::Gas,
            'unit' => 'm3',
            'utility_billing_mode' => UtilityBillingMode::None,
            'rate_per_unit' => 2.10,
        ]);

        $meter->readings()->create([
            'reading_date' => '2025-01-01',
            'value' => 10,
            'source' => MeterReadingSource::Manual,
        ]);

        $meter->readings()->create([
            'reading_date' => '2025-01-31',
            'value' => 15,
            'source' => MeterReadingSource::Manual,
        ]);

        /** @var InvoiceGenerationService $service */
        $service = app(InvoiceGenerationService::class);
        $generated = $service->generateForLease($lease, Carbon::parse('2025-01-31'));

        $this->assertCount(1, $generated);
        $invoice = $generated->first();

        $this->assertSame('500.00', $invoice->total);
        $this->assertDatabaseMissing('invoice_lines', [
            'invoice_id' => $invoice->id,
            'description' => 'Gāze',
        ]);
    }

    public function test_manual_override_on_generated_invoice_line_is_preserved_on_regeneration(): void
    {
        $lease = $this->createLeaseWithBillingContext();

        $rule = LeaseChargeRule::factory()->create([
            'lease_id' => $lease->id,
            'name' => 'Mēneša īre',
            'amount' => 500,
            'effective_from' => '2025-01-01',
        ]);

        /** @var InvoiceGenerationService $service */
        $service = app(InvoiceGenerationService::class);
        $invoice = $service->generateForLease($lease, Carbon::parse('2025-01-31'))->first();
        $line = $invoice->lines()->where('source_type', LeaseChargeRule::class)->where('source_id', $rule->id)->first();

        $line->update([
            'description' => 'Koriģēta īres maksa',
            'quantity' => 1,
            'unit_price' => 650,
            'tax' => 0,
            'line_total' => 650,
            'is_manual_override' => true,
        ]);

        $service->generateForLease($lease->fresh(), Carbon::parse('2025-01-31'));

        $line = $line->fresh();

        $this->assertSame('Koriģēta īres maksa', $line->description);
        $this->assertSame('650.00', $line->line_total);
        $this->assertTrue($line->is_manual_override);
        $this->assertSame('650.00', $invoice->fresh()->total);
    }

    public function test_invoice_number_format_scopes_sequence_by_year_and_property_unit_code(): void
    {
        $owner = User::factory()->owner()->create([
            'invoice_number_format' => '{year}-{property_unit_code}-{num}',
        ]);
        $property = Property::factory()->create(['user_id' => $owner->id]);
        $unitA = PropertyUnit::factory()->create(['property_id' => $property->id, 'code' => 'A-01']);
        $unitB = PropertyUnit::factory()->create(['property_id' => $property->id, 'code' => 'B-01']);
        $tenant = TenantProfile::factory()->create(['owner_id' => $owner->id]);

        $leaseA = Lease::factory()->create([
            'property_unit_id' => $unitA->id,
            'tenant_profile_id' => $tenant->id,
            'start_date' => '2025-01-01',
            'billing_start_date' => '2025-01-01',
            'status' => LeaseStatus::Active,
        ]);

        $leaseB = Lease::factory()->create([
            'property_unit_id' => $unitB->id,
            'tenant_profile_id' => $tenant->id,
            'start_date' => '2025-01-01',
            'billing_start_date' => '2025-01-01',
            'status' => LeaseStatus::Active,
        ]);

        Invoice::factory()->create([
            'lease_id' => $leaseA->id,
            'number' => '2024-A-01-1',
            'issue_date' => '2024-01-01',
            'period_from' => '2024-01-01',
            'period_to' => '2024-01-31',
        ]);

        Invoice::factory()->create([
            'lease_id' => $leaseA->id,
            'number' => '2025-A-01-1',
            'issue_date' => '2025-01-01',
            'period_from' => '2025-01-01',
            'period_to' => '2025-01-31',
        ]);

        Invoice::factory()->create([
            'lease_id' => $leaseB->id,
            'number' => '2025-B-01-1',
            'issue_date' => '2025-01-01',
            'period_from' => '2025-01-01',
            'period_to' => '2025-01-31',
        ]);

        LeaseChargeRule::factory()->create([
            'lease_id' => $leaseA->id,
            'amount' => 300,
            'effective_from' => '2025-02-01',
        ]);

        /** @var InvoiceGenerationService $service */
        $service = app(InvoiceGenerationService::class);
        $generated = $service->generateForLease($leaseA, Carbon::parse('2025-02-15'));

        $this->assertCount(1, $generated);
        $this->assertSame('2025-A-01-2', $generated->first()->number);
    }

    public function test_invoice_number_format_scopes_sequence_by_property_unit_code_when_year_is_not_used(): void
    {
        $owner = User::factory()->owner()->create([
            'invoice_number_format' => '{property_unit_code}-{num}',
        ]);
        $property = Property::factory()->create(['user_id' => $owner->id]);
        $unitA = PropertyUnit::factory()->create(['property_id' => $property->id, 'code' => 'A-01']);
        $unitB = PropertyUnit::factory()->create(['property_id' => $property->id, 'code' => 'B-01']);
        $tenant = TenantProfile::factory()->create(['owner_id' => $owner->id]);

        $leaseA = Lease::factory()->create([
            'property_unit_id' => $unitA->id,
            'tenant_profile_id' => $tenant->id,
            'start_date' => '2025-01-01',
            'billing_start_date' => '2025-01-01',
            'status' => LeaseStatus::Active,
        ]);

        $leaseB = Lease::factory()->create([
            'property_unit_id' => $unitB->id,
            'tenant_profile_id' => $tenant->id,
            'start_date' => '2025-01-01',
            'billing_start_date' => '2025-01-01',
            'status' => LeaseStatus::Active,
        ]);

        Invoice::factory()->create([
            'lease_id' => $leaseA->id,
            'number' => 'A-01-1',
            'issue_date' => '2024-01-01',
            'period_from' => '2024-01-01',
            'period_to' => '2024-01-31',
        ]);

        Invoice::factory()->create([
            'lease_id' => $leaseA->id,
            'number' => 'A-01-2',
            'issue_date' => '2025-01-01',
            'period_from' => '2025-01-01',
            'period_to' => '2025-01-31',
        ]);

        Invoice::factory()->create([
            'lease_id' => $leaseB->id,
            'number' => 'B-01-1',
            'issue_date' => '2025-01-01',
            'period_from' => '2025-01-01',
            'period_to' => '2025-01-31',
        ]);

        LeaseChargeRule::factory()->create([
            'lease_id' => $leaseA->id,
            'amount' => 325,
            'effective_from' => '2025-02-01',
        ]);

        /** @var InvoiceGenerationService $service */
        $service = app(InvoiceGenerationService::class);
        $generated = $service->generateForLease($leaseA, Carbon::parse('2025-02-15'));

        $this->assertCount(1, $generated);
        $this->assertSame('A-01-3', $generated->first()->number);
    }

    public function test_invoice_number_format_scopes_sequence_globally_when_year_is_not_used(): void
    {
        $owner = User::factory()->owner()->create([
            'invoice_number_format' => 'INV-{num}',
        ]);
        $lease = $this->createLeaseWithBillingContext($owner);

        Invoice::factory()->create([
            'lease_id' => $lease->id,
            'number' => 'INV-1',
            'issue_date' => '2024-01-01',
            'period_from' => '2024-01-01',
            'period_to' => '2024-01-31',
        ]);

        $secondLease = $this->createLeaseWithBillingContext($owner);

        Invoice::factory()->create([
            'lease_id' => $secondLease->id,
            'number' => 'INV-2',
            'issue_date' => '2025-01-01',
            'period_from' => '2025-01-01',
            'period_to' => '2025-01-31',
        ]);

        LeaseChargeRule::factory()->create([
            'lease_id' => $lease->id,
            'amount' => 200,
            'effective_from' => '2025-02-01',
        ]);

        /** @var InvoiceGenerationService $service */
        $service = app(InvoiceGenerationService::class);
        $generated = $service->generateForLease($lease, Carbon::parse('2025-02-15'));

        $this->assertCount(1, $generated);
        $this->assertSame('INV-3', $generated->first()->number);
    }

    public function test_payments_refresh_invoice_status_and_reminders_are_logged(): void
    {
        Mail::fake();

        $lease = $this->createLeaseWithBillingContext();
        $invoice = $lease->invoices()->create([
            'number' => 'INV-TEST-1',
            'issue_date' => '2025-01-01',
            'due_date' => '2025-01-10',
            'period_from' => '2025-01-01',
            'period_to' => '2025-01-31',
            'status' => InvoiceStatus::Issued,
            'subtotal' => 100,
            'total' => 100,
        ]);
        $invoice->lines()->create([
            'description' => 'Īres maksa',
            'quantity' => 1,
            'unit_price' => 100,
            'tax' => 0,
            'line_total' => 100,
        ]);

        /** @var InvoiceStatusService $invoiceStatusService */
        $invoiceStatusService = app(InvoiceStatusService::class);
        $invoiceStatusService->refresh($invoice, Carbon::parse('2025-01-20'));

        $this->assertSame(InvoiceStatus::Overdue, $invoice->fresh()->status);

        $invoice->payments()->create([
            'paid_at' => '2025-01-15',
            'amount' => 40,
            'method' => 'bank_transfer',
        ]);
        $invoiceStatusService->refresh($invoice->fresh(), Carbon::parse('2025-01-20'));

        $this->assertSame(InvoiceStatus::Overdue, $invoice->fresh()->status);

        $invoice->payments()->create([
            'paid_at' => '2025-01-18',
            'amount' => 60,
            'method' => 'bank_transfer',
        ]);
        $invoiceStatusService->refresh($invoice->fresh(), Carbon::parse('2025-01-20'));

        $this->assertSame(InvoiceStatus::Paid, $invoice->fresh()->status);

        /** @var ReminderDispatchService $reminderDispatchService */
        $reminderDispatchService = app(ReminderDispatchService::class);

        $sentInvoice = $reminderDispatchService->sendInvoice($invoice->fresh()->load('lease.tenantProfile'));

        $this->assertSame('invoice', $sentInvoice->kind);
        $this->assertSame('sent', $sentInvoice->status);
        Mail::assertSent(InvoiceMail::class, fn (InvoiceMail $mail) => $mail->hasTo('tenant@example.com') && $mail->pdfFileName === 'invoice-inv-test-1.pdf');

        $overdueInvoice = $lease->invoices()->create([
            'number' => 'INV-TEST-2',
            'issue_date' => '2025-02-01',
            'due_date' => '2025-02-10',
            'period_from' => '2025-02-01',
            'period_to' => '2025-02-28',
            'status' => InvoiceStatus::Issued,
            'subtotal' => 75,
            'total' => 75,
        ]);
        $overdueInvoice->lines()->create([
            'description' => 'Īres maksa',
            'quantity' => 1,
            'unit_price' => 75,
            'tax' => 0,
            'line_total' => 75,
        ]);

        $firstReminderBatch = $reminderDispatchService->sendOverdueReminders(Carbon::parse('2025-02-20'));
        $secondReminderBatch = $reminderDispatchService->sendOverdueReminders(Carbon::parse('2025-02-20'));

        $this->assertCount(1, $firstReminderBatch);
        $this->assertCount(0, $secondReminderBatch);

        $this->assertDatabaseHas('invoice_reminders', [
            'invoice_id' => $overdueInvoice->id,
            'kind' => 'overdue',
            'status' => 'sent',
        ]);
    }

    public function test_meter_consumption_service_returns_reading_delta(): void
    {
        $lease = $this->createLeaseWithBillingContext();
        $meter = Meter::factory()->create([
            'property_unit_id' => $lease->property_unit_id,
            'unit' => 'kWh',
        ]);

        $meter->readings()->create([
            'reading_date' => '2025-01-01',
            'value' => 120.500,
            'source' => MeterReadingSource::Manual,
        ]);

        $meter->readings()->create([
            'reading_date' => '2025-02-01',
            'value' => 145.750,
            'source' => MeterReadingSource::Manual,
        ]);

        /** @var MeterConsumptionService $meterConsumptionService */
        $meterConsumptionService = app(MeterConsumptionService::class);
        $deltas = $meterConsumptionService->readingDeltas($meter);

        $this->assertCount(2, $deltas);
        $this->assertNull($deltas->first()['consumption']);
        $this->assertSame(25.25, $deltas->last()['consumption']);
        $this->assertSame(25.25, $meterConsumptionService->latestConsumption($meter));
    }

    private function createLeaseWithBillingContext(?User $owner = null): Lease
    {
        $owner ??= User::factory()->owner()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);
        $unit = PropertyUnit::factory()->create(['property_id' => $property->id]);
        $tenant = TenantProfile::factory()->create([
            'owner_id' => $owner->id,
            'email' => 'tenant@example.com',
        ]);

        return Lease::factory()->create([
            'property_unit_id' => $unit->id,
            'tenant_profile_id' => $tenant->id,
            'start_date' => '2025-01-01',
            'end_date' => null,
            'billing_start_date' => '2025-01-01',
            'due_day' => 10,
            'currency' => 'EUR',
            'status' => LeaseStatus::Active,
        ]);
    }
}

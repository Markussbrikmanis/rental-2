<?php

namespace App\Services;

use App\Enums\InvoiceKind;
use App\Enums\InvoiceStatus;
use App\Enums\UtilityBillingMode;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\LeaseChargeRule;
use App\Models\Meter;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceGenerationService
{
    public function __construct(
        private readonly LeaseBillingService $leaseBillingService,
        private readonly InvoiceStatusService $invoiceStatusService,
        private readonly MeterConsumptionService $meterConsumptionService,
    ) {
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function generateForDate(CarbonInterface $targetDate, ?Collection $leases = null): Collection
    {
        $leases ??= Lease::query()
            ->with(['chargeRules', 'tenantProfile', 'propertyUnit.property.user'])
            ->whereIn('status', [\App\Enums\LeaseStatus::Active->value, \App\Enums\LeaseStatus::Draft->value])
            ->get();

        $generated = collect();

        foreach ($leases as $lease) {
            foreach ($lease->chargeRules as $rule) {
                if (! $rule->auto_invoice_enabled) {
                    continue;
                }

                foreach ($this->leaseBillingService->duePeriodsUpToDate($lease, $rule, $targetDate) as $period) {
                    $invoices = DB::transaction(function () use ($lease, $rule, $period) {
                        $periodFrom = $period['period_from']->toDateString();
                        $periodTo = $period['period_to']->toDateString();

                        $invoice = $this->resolveInvoice($lease, $period['period_from'], $period['period_to'], InvoiceKind::Standard);

                        $line = $invoice->lines()
                            ->where('source_type', $rule::class)
                            ->where('source_id', $rule->id)
                            ->first();

                        if (! $line) {
                            $invoice->lines()->create([
                                'description' => $rule->name,
                                'quantity' => 1,
                                'unit_price' => $rule->amount,
                                'tax' => 0,
                                'line_total' => $rule->amount,
                                'source_type' => $rule::class,
                                'source_id' => $rule->id,
                            ]);
                        } elseif (! $line->is_manual_override) {
                            $line->update([
                                'description' => $rule->name,
                                'quantity' => 1,
                                'unit_price' => $rule->amount,
                                'tax' => 0,
                                'line_total' => $rule->amount,
                            ]);
                        }

                        $touchedInvoices = collect()->put($invoice->id, $invoice);
                        $utilityInvoices = $this->syncUtilityCharges($lease, $period['period_from'], $period['period_to'], $invoice);

                        foreach ($utilityInvoices as $utilityInvoice) {
                            $touchedInvoices->put($utilityInvoice->id, $utilityInvoice);
                        }

                        return $touchedInvoices
                            ->map(fn (Invoice $touchedInvoice) => $this->invoiceStatusService->recalculate($touchedInvoice))
                            ->values();
                    });

                    foreach ($invoices as $invoice) {
                        $generated->push($this->invoiceStatusService->refresh($invoice, $targetDate));
                    }
                }
            }
        }

        return $generated->unique('id')->values();
    }

    public function generateForLease(Lease $lease, CarbonInterface $targetDate): Collection
    {
        return $this->generateForDate($targetDate, collect([$lease->load(['chargeRules', 'tenantProfile', 'propertyUnit.property.user'])]));
    }

    private function resolveInvoice(
        Lease $lease,
        CarbonInterface $periodFrom,
        CarbonInterface $periodTo,
        InvoiceKind $kind,
    ): Invoice {
        $periodFromDate = $periodFrom->toDateString();
        $periodToDate = $periodTo->toDateString();

        $invoice = Invoice::query()
            ->where('lease_id', $lease->id)
            ->whereDate('period_from', $periodFromDate)
            ->whereDate('period_to', $periodToDate)
            ->where('kind', $kind->value)
            ->first();

        if ($invoice) {
            return $invoice;
        }

        return Invoice::query()->create([
            'lease_id' => $lease->id,
            'period_from' => $periodFromDate,
            'period_to' => $periodToDate,
            'number' => $this->invoiceNumber($lease, $periodFrom),
            'issue_date' => $periodFromDate,
            'due_date' => $this->leaseBillingService->dueDateForLease($lease, $periodFrom)->toDateString(),
            'kind' => $kind,
            'status' => InvoiceStatus::Issued,
            'subtotal' => 0,
            'total' => 0,
        ]);
    }

    private function invoiceNumber(Lease $lease, CarbonInterface $periodFrom): string
    {
        $owner = $lease->propertyUnit->property->user;
        $format = $owner->invoiceNumberFormat();
        $sequenceNumber = $this->nextInvoiceSequenceNumber($lease, $periodFrom, $format);
        $propertyUnitCode = trim((string) $lease->propertyUnit->code);

        return strtr($format, [
            '{year}' => $periodFrom->format('Y'),
            '{num}' => (string) $sequenceNumber,
            '{property_unit_code}' => $propertyUnitCode,
        ]);
    }

    private function nextInvoiceSequenceNumber(Lease $lease, CarbonInterface $periodFrom, string $format): int
    {
        $ownerId = $lease->propertyUnit->property->user_id;
        $query = Invoice::query()
            ->whereHas('lease.propertyUnit.property', fn ($builder) => $builder->where('user_id', $ownerId));

        if (str_contains($format, '{year}')) {
            $query->whereYear('issue_date', $periodFrom->year);
        }

        if (str_contains($format, '{property_unit_code}')) {
            $propertyUnitCode = trim((string) $lease->propertyUnit->code);

            if ($propertyUnitCode !== '') {
                $query->whereHas('lease.propertyUnit', fn ($builder) => $builder->where('code', $propertyUnitCode));
            } else {
                $query->whereHas('lease.propertyUnit', fn ($builder) => $builder->whereKey($lease->property_unit_id));
            }
        }

        return $query->count() + 1;
    }

    /**
     * @return Collection<int, Invoice>
     */
    private function syncUtilityCharges(
        Lease $lease,
        CarbonInterface $periodFrom,
        CarbonInterface $periodTo,
        Invoice $standardInvoice,
    ): Collection {
        $touchedInvoices = collect();

        foreach ($lease->propertyUnit->meters()->where('is_active', true)->get() as $meter) {
            if ($meter->utility_billing_mode === UtilityBillingMode::None || $meter->rate_per_unit === null) {
                continue;
            }

            $consumption = $this->meterConsumptionService->consumptionForPeriod($meter, $periodFrom, $periodTo);

            if ($consumption === null) {
                continue;
            }

            $targetInvoice = $meter->utility_billing_mode === UtilityBillingMode::Separate
                ? $this->resolveInvoice($lease, $periodFrom, $periodTo, InvoiceKind::Utility)
                : $standardInvoice;

            $lineTotal = round($consumption * (float) $meter->rate_per_unit, 2);

            $line = $targetInvoice->lines()
                ->where('source_type', Meter::class)
                ->where('source_id', $meter->id)
                ->first();

            if (! $line) {
                $targetInvoice->lines()->create([
                    'description' => $meter->name,
                    'quantity' => round($consumption, 2),
                    'unit_price' => $meter->rate_per_unit,
                    'tax' => 0,
                    'line_total' => $lineTotal,
                    'source_type' => Meter::class,
                    'source_id' => $meter->id,
                ]);
            } elseif (! $line->is_manual_override) {
                $line->update([
                    'description' => $meter->name,
                    'quantity' => round($consumption, 2),
                    'unit_price' => $meter->rate_per_unit,
                    'tax' => 0,
                    'line_total' => $lineTotal,
                ]);
            }

            $touchedInvoices->put($targetInvoice->id, $targetInvoice);
        }

        return $touchedInvoices->values();
    }
}

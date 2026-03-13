<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OwnerReportService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function build(User $owner, array $filters = []): array
    {
        [$from, $to, $period] = $this->resolveRange($owner, $filters);
        $ownerProperties = Property::query()
            ->where('user_id', $owner->id)
            ->with('units')
            ->orderBy('name')
            ->get();

        $selectedProperty = $ownerProperties->firstWhere('id', (int) ($filters['property_id'] ?? 0));
        $availableUnits = ($selectedProperty?->units ?? $ownerProperties->flatMap->units)->sortBy('name')->values();
        $selectedUnit = $availableUnits->firstWhere('id', (int) ($filters['unit_id'] ?? 0));
        $requestedView = (string) ($filters['view'] ?? 'table');
        $view = in_array($requestedView, ['table', 'charts', 'split'], true)
            ? $requestedView
            : 'table';

        $propertiesQuery = Property::query()
            ->where('user_id', $owner->id)
            ->with([
                'units.leases' => fn ($query) => $query->whereIn('status', [LeaseStatus::Active->value, LeaseStatus::Draft->value]),
            ]);

        if ($selectedProperty) {
            $propertiesQuery->whereKey($selectedProperty->id);
        } elseif ($selectedUnit) {
            $propertiesQuery->whereHas('units', fn (Builder $query) => $query->whereKey($selectedUnit->id));
        }

        $properties = $propertiesQuery->get()
            ->map(function (Property $property) use ($selectedUnit) {
                if (! $selectedUnit) {
                    return $property;
                }

                $property->setRelation(
                    'units',
                    $property->units->where('id', $selectedUnit->id)->values(),
                );

                return $property;
            })
            ->filter(fn (Property $property) => $property->units->isNotEmpty())
            ->values();

        $invoicesQuery = Invoice::query()
            ->whereHas('lease.propertyUnit.property', fn (Builder $query) => $query->where('user_id', $owner->id))
            ->with([
                'payments',
                'lease.propertyUnit.property',
                'lease.tenantProfile',
            ])
            ->whereDate('issue_date', '<=', $to);

        if ($selectedProperty) {
            $invoicesQuery->whereHas('lease.propertyUnit.property', fn (Builder $query) => $query->whereKey($selectedProperty->id));
        }

        if ($selectedUnit) {
            $invoicesQuery->whereHas('lease.propertyUnit', fn (Builder $query) => $query->whereKey($selectedUnit->id));
        }

        $invoices = $invoicesQuery->get();

        $paymentsQuery = Payment::query()
            ->whereHas('invoice.lease.propertyUnit.property', fn (Builder $query) => $query->where('user_id', $owner->id))
            ->with(['invoice.lease.propertyUnit.property', 'invoice.lease.tenantProfile'])
            ->whereDate('paid_at', '<=', $to);

        if ($selectedProperty) {
            $paymentsQuery->whereHas('invoice.lease.propertyUnit.property', fn (Builder $query) => $query->whereKey($selectedProperty->id));
        }

        if ($selectedUnit) {
            $paymentsQuery->whereHas('invoice.lease.propertyUnit', fn (Builder $query) => $query->whereKey($selectedUnit->id));
        }

        $payments = $paymentsQuery->get();

        $activeUnits = $properties->flatMap->units->where('is_active', true)->values();
        $periodInvoices = $invoices
            ->filter(fn (Invoice $invoice) => $invoice->status !== InvoiceStatus::Cancelled && $invoice->issue_date->betweenIncluded($from, $to))
            ->values();
        $periodPayments = $payments
            ->filter(fn (Payment $payment) => $payment->paid_at->betweenIncluded($from, $to))
            ->values();

        $overdueInvoices = $invoices
            ->filter(function (Invoice $invoice) use ($to): bool {
                return $invoice->status !== InvoiceStatus::Cancelled
                    && $invoice->due_date->lt($to)
                    && $this->outstandingAmount($invoice, $to) > 0;
            })
            ->sortBy('due_date')
            ->values();

        $occupancyOccupied = $activeUnits
            ->filter(fn (PropertyUnit $unit) => $this->isUnitOccupied($unit, $to))
            ->count();
        $occupancyRate = $activeUnits->count() > 0
            ? round(($occupancyOccupied / $activeUnits->count()) * 100, 2)
            : 0.0;

        $purchaseTotal = (float) $properties->sum(fn (Property $property) => (float) $property->price);
        $allCollectedToDate = (float) $payments->sum(fn (Payment $payment) => (float) $payment->amount);
        $periodInvoiced = (float) $periodInvoices->sum(fn (Invoice $invoice) => (float) $invoice->total);
        $periodCollected = (float) $periodPayments->sum(fn (Payment $payment) => (float) $payment->amount);
        $periodOutstanding = (float) $periodInvoices->sum(fn (Invoice $invoice) => $this->outstandingAmount($invoice, $to));
        $overdueOutstanding = (float) $overdueInvoices->sum(fn (Invoice $invoice) => $this->outstandingAmount($invoice, $to));
        $periodIssuedCount = $periodInvoices->count();
        $collectionRate = $periodInvoiced > 0 ? round(($periodCollected / $periodInvoiced) * 100, 2) : 0.0;
        $portfolioPaybackRate = $purchaseTotal > 0 ? round(($allCollectedToDate / $purchaseTotal) * 100, 2) : 0.0;

        return [
            'filters' => [
                'period' => $period,
                'from' => $from,
                'to' => $to,
                'period_options' => $this->periodOptions(),
                'label' => $this->periodLabel($period, $from, $to),
                'property_id' => $selectedProperty?->id,
                'unit_id' => $selectedUnit?->id,
                'view' => $view,
                'view_options' => [
                    'table' => __('app.rental.reports.views.table'),
                    'charts' => __('app.rental.reports.views.charts'),
                    'split' => __('app.rental.reports.views.split'),
                ],
                'property_options' => $ownerProperties->map(fn (Property $property) => [
                    'id' => $property->id,
                    'name' => $property->name,
                ])->values(),
                'unit_options' => $availableUnits->map(fn (PropertyUnit $unit) => [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'property_name' => $unit->property?->name,
                ])->values(),
            ],
            'overview' => [
                'period_invoiced' => $periodInvoiced,
                'period_collected' => $periodCollected,
                'period_outstanding' => $periodOutstanding,
                'overdue_outstanding' => $overdueOutstanding,
                'period_issued_count' => $periodIssuedCount,
                'collection_rate' => $collectionRate,
                'purchase_total' => $purchaseTotal,
                'all_collected' => $allCollectedToDate,
                'portfolio_payback_rate' => $portfolioPaybackRate,
                'occupancy_rate' => $occupancyRate,
                'occupied_units' => $occupancyOccupied,
                'active_units' => $activeUnits->count(),
            ],
            'monthly_trend' => $this->monthlyTrend($invoices, $payments, $from, $to),
            'property_performance' => $this->propertyPerformance($properties, $invoices, $payments, $from, $to),
            'tenant_balances' => $this->tenantBalances($invoices, $payments, $to),
            'overdue_invoices' => $this->overdueInvoices($overdueInvoices, $to),
            'occupancy_by_property' => $this->occupancyByProperty($properties, $to),
            'vacant_units' => $this->vacantUnits($properties, $to),
            'invoice_mix' => $this->invoiceMix($periodInvoices, $periodPayments),
            'charts' => $this->chartData($properties, $periodInvoices, $periodPayments),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolveRange(User $owner, array $filters): array
    {
        $period = (string) ($filters['period'] ?? 'this_year');
        $today = now()->startOfDay();

        return match ($period) {
            'this_month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth(), $period],
            'this_quarter' => [$today->copy()->startOfQuarter(), $today->copy()->endOfQuarter(), $period],
            'last_12_months' => [$today->copy()->subMonths(11)->startOfMonth(), $today->copy()->endOfMonth(), $period],
            'all' => [$this->allTimeStart($owner), $today->copy()->endOfDay(), $period],
            'custom' => [
                $this->parseDate((string) ($filters['from'] ?? $today->copy()->startOfMonth()->toDateString()), $today->copy()->startOfMonth()),
                $this->parseDate((string) ($filters['to'] ?? $today->toDateString()), $today)->endOfDay(),
                $period,
            ],
            default => [$today->copy()->startOfYear(), $today->copy()->endOfYear(), 'this_year'],
        };
    }

    private function allTimeStart(User $owner): Carbon
    {
        $propertyDate = Property::query()
            ->where('user_id', $owner->id)
            ->min('acquired_at');

        $invoiceDate = Invoice::query()
            ->whereHas('lease.propertyUnit.property', fn (Builder $query) => $query->where('user_id', $owner->id))
            ->min('issue_date');

        $paymentDate = Payment::query()
            ->whereHas('invoice.lease.propertyUnit.property', fn (Builder $query) => $query->where('user_id', $owner->id))
            ->min('paid_at');

        $candidates = collect([$propertyDate, $invoiceDate, $paymentDate])
            ->filter()
            ->map(fn (string $date) => Carbon::parse($date))
            ->sort()
            ->values();

        return $candidates->first()?->startOfDay() ?? now()->startOfYear();
    }

    private function parseDate(string $value, Carbon $fallback): Carbon
    {
        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return $fallback->copy();
        }
    }

    /**
     * @return array<string, string>
     */
    private function periodOptions(): array
    {
        return [
            'this_month' => __('app.rental.reports.periods.this_month'),
            'this_quarter' => __('app.rental.reports.periods.this_quarter'),
            'this_year' => __('app.rental.reports.periods.this_year'),
            'last_12_months' => __('app.rental.reports.periods.last_12_months'),
            'all' => __('app.rental.reports.periods.all'),
            'custom' => __('app.rental.reports.periods.custom'),
        ];
    }

    private function periodLabel(string $period, CarbonInterface $from, CarbonInterface $to): string
    {
        if ($period !== 'custom') {
            return $this->periodOptions()[$period] ?? $this->periodOptions()['this_year'];
        }

        return $from->format('d.m.Y').' - '.$to->format('d.m.Y');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function monthlyTrend(Collection $invoices, Collection $payments, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $start = Carbon::instance($from)->startOfMonth();
        $end = Carbon::instance($to)->startOfMonth();
        $rows = collect();
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth()->min($to);

            $invoiced = (float) $invoices
                ->filter(fn (Invoice $invoice) => $invoice->status !== InvoiceStatus::Cancelled && $invoice->issue_date->betweenIncluded($monthStart, $monthEnd))
                ->sum(fn (Invoice $invoice) => (float) $invoice->total);

            $collected = (float) $payments
                ->filter(fn (Payment $payment) => $payment->paid_at->betweenIncluded($monthStart, $monthEnd))
                ->sum(fn (Payment $payment) => (float) $payment->amount);

            $openAtMonthEnd = (float) $invoices
                ->filter(fn (Invoice $invoice) => $invoice->status !== InvoiceStatus::Cancelled && $invoice->issue_date->lte($monthEnd))
                ->sum(fn (Invoice $invoice) => $this->outstandingAmount($invoice, $monthEnd));

            $rows->push([
                'label' => $cursor->translatedFormat('F Y'),
                'invoiced' => $invoiced,
                'collected' => $collected,
                'open' => $openAtMonthEnd,
            ]);

            $cursor->addMonth();
        }

        return $rows;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function propertyPerformance(Collection $properties, Collection $invoices, Collection $payments, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $properties
            ->map(function (Property $property) use ($invoices, $payments, $from, $to): array {
                $propertyInvoices = $invoices->filter(fn (Invoice $invoice) => $invoice->lease->propertyUnit->property_id === $property->id);
                $propertyPayments = $payments->filter(fn (Payment $payment) => $payment->invoice->lease->propertyUnit->property_id === $property->id);
                $activeUnits = $property->units->where('is_active', true);
                $occupiedUnits = $activeUnits->filter(fn (PropertyUnit $unit) => $this->isUnitOccupied($unit, $to))->count();
                $purchasePrice = (float) $property->price;
                $collectedAll = (float) $propertyPayments->sum(fn (Payment $payment) => (float) $payment->amount);

                return [
                    'property' => $property,
                    'purchase_price' => $purchasePrice,
                    'period_invoiced' => (float) $propertyInvoices
                        ->filter(fn (Invoice $invoice) => $invoice->status !== InvoiceStatus::Cancelled && $invoice->issue_date->betweenIncluded($from, $to))
                        ->sum(fn (Invoice $invoice) => (float) $invoice->total),
                    'period_collected' => (float) $propertyPayments
                        ->filter(fn (Payment $payment) => $payment->paid_at->betweenIncluded($from, $to))
                        ->sum(fn (Payment $payment) => (float) $payment->amount),
                    'all_collected' => $collectedAll,
                    'open_balance' => (float) $propertyInvoices
                        ->sum(fn (Invoice $invoice) => $this->outstandingAmount($invoice, $to)),
                    'remaining_to_recoup' => max($purchasePrice - $collectedAll, 0),
                    'net_against_purchase' => $collectedAll - $purchasePrice,
                    'payback_rate' => $purchasePrice > 0 ? round(($collectedAll / $purchasePrice) * 100, 2) : 0.0,
                    'occupancy_rate' => $activeUnits->count() > 0 ? round(($occupiedUnits / $activeUnits->count()) * 100, 2) : 0.0,
                    'occupied_units' => $occupiedUnits,
                    'active_units' => $activeUnits->count(),
                ];
            })
            ->sortByDesc('all_collected')
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function tenantBalances(Collection $invoices, Collection $payments, CarbonInterface $to): Collection
    {
        return $invoices
            ->groupBy('lease.tenant_profile_id')
            ->map(function (Collection $tenantInvoices) use ($payments, $to): array {
                /** @var Invoice $firstInvoice */
                $firstInvoice = $tenantInvoices->first();
                $tenantId = $firstInvoice->lease->tenant_profile_id;
                $tenantPayments = $payments->filter(fn (Payment $payment) => $payment->invoice->lease->tenant_profile_id === $tenantId);

                return [
                    'tenant' => $firstInvoice->lease->tenantProfile,
                    'invoiced' => (float) $tenantInvoices
                        ->filter(fn (Invoice $invoice) => $invoice->status !== InvoiceStatus::Cancelled)
                        ->sum(fn (Invoice $invoice) => (float) $invoice->total),
                    'paid' => (float) $tenantPayments->sum(fn (Payment $payment) => (float) $payment->amount),
                    'open_balance' => (float) $tenantInvoices->sum(fn (Invoice $invoice) => $this->outstandingAmount($invoice, $to)),
                    'overdue_count' => $tenantInvoices->filter(fn (Invoice $invoice) => $invoice->due_date->lt($to) && $this->outstandingAmount($invoice, $to) > 0)->count(),
                ];
            })
            ->sortByDesc('open_balance')
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function overdueInvoices(Collection $overdueInvoices, CarbonInterface $to): Collection
    {
        return $overdueInvoices
            ->map(fn (Invoice $invoice): array => [
                'invoice' => $invoice,
                'outstanding' => $this->outstandingAmount($invoice, $to),
                'days_late' => $invoice->due_date->diffInDays($to),
            ])
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function occupancyByProperty(Collection $properties, CarbonInterface $to): Collection
    {
        return $properties->map(function (Property $property) use ($to): array {
            $activeUnits = $property->units->where('is_active', true);
            $occupied = $activeUnits->filter(fn (PropertyUnit $unit) => $this->isUnitOccupied($unit, $to))->count();
            $vacant = max($activeUnits->count() - $occupied, 0);

            return [
                'property' => $property,
                'active_units' => $activeUnits->count(),
                'occupied_units' => $occupied,
                'vacant_units' => $vacant,
                'occupancy_rate' => $activeUnits->count() > 0 ? round(($occupied / $activeUnits->count()) * 100, 2) : 0.0,
            ];
        })->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function vacantUnits(Collection $properties, CarbonInterface $to): Collection
    {
        return $properties
            ->flatMap(function (Property $property) use ($to): Collection {
                return $property->units
                    ->where('is_active', true)
                    ->filter(fn (PropertyUnit $unit) => ! $this->isUnitOccupied($unit, $to))
                    ->map(fn (PropertyUnit $unit): array => [
                        'property' => $property,
                        'unit' => $unit,
                    ]);
            })
            ->values();
    }

    /**
     * @return array<string, array{invoiced: float, collected: float}>
     */
    private function invoiceMix(Collection $periodInvoices, Collection $periodPayments): array
    {
        $mix = [];

        foreach (['standard', 'utility'] as $kind) {
            $kindInvoices = $periodInvoices->filter(fn (Invoice $invoice) => $invoice->kind->value === $kind);
            $mix[$kind] = [
                'invoiced' => (float) $kindInvoices->sum(fn (Invoice $invoice) => (float) $invoice->total),
                'collected' => (float) $periodPayments
                    ->filter(fn (Payment $payment) => $payment->invoice->kind->value === $kind)
                    ->sum(fn (Payment $payment) => (float) $payment->amount),
            ];
        }

        return $mix;
    }

    /**
     * @return array<string, mixed>
     */
    private function chartData(Collection $properties, Collection $periodInvoices, Collection $periodPayments): array
    {
        return [
            'property_collection' => $properties->map(function (Property $property) use ($periodInvoices, $periodPayments): array {
                $propertyInvoiced = (float) $periodInvoices
                    ->filter(fn (Invoice $invoice) => $invoice->lease->propertyUnit->property_id === $property->id)
                    ->sum(fn (Invoice $invoice) => (float) $invoice->total);
                $propertyCollected = (float) $periodPayments
                    ->filter(fn (Payment $payment) => $payment->invoice->lease->propertyUnit->property_id === $property->id)
                    ->sum(fn (Payment $payment) => (float) $payment->amount);

                return [
                    'label' => $property->name,
                    'invoiced' => $propertyInvoiced,
                    'collected' => $propertyCollected,
                ];
            })->values(),
        ];
    }

    private function outstandingAmount(Invoice $invoice, CarbonInterface $asOf): float
    {
        $paid = (float) $invoice->payments
            ->filter(fn (Payment $payment) => $payment->paid_at->lte($asOf))
            ->sum(fn (Payment $payment) => (float) $payment->amount);

        return max((float) $invoice->total - $paid, 0);
    }

    private function isUnitOccupied(PropertyUnit $unit, CarbonInterface $at): bool
    {
        return $unit->leases->contains(function ($lease) use ($at): bool {
            return $lease->status === LeaseStatus::Active
                && $lease->start_date->lte($at)
                && ($lease->end_date === null || $lease->end_date->gte($at));
        });
    }
}

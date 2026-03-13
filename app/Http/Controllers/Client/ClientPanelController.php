<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Meter;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\TenantProfile;
use App\Services\InvoiceStatusService;
use App\Services\OwnerReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientPanelController extends Controller
{
    public function __invoke(Request $request, OwnerReportService $ownerReportService, InvoiceStatusService $invoiceStatusService): View
    {
        $user = $request->user();

        return view('client.panel', [
            'user' => $user,
            'ownerDashboard' => $user->isOwner()
                ? $this->ownerDashboardData($user, $ownerReportService)
                : null,
            'tenantDashboard' => $user->isTenant()
                ? $this->tenantDashboardData($user, $invoiceStatusService)
                : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function ownerDashboardData(\App\Models\User $owner, OwnerReportService $ownerReportService): array
    {
        $propertiesCount = Property::query()->where('user_id', $owner->id)->count();
        $unitsCount = PropertyUnit::query()
            ->whereHas('property', fn ($query) => $query->where('user_id', $owner->id))
            ->count();
        $tenantsCount = TenantProfile::query()->where('owner_id', $owner->id)->count();
        $activeLeasesCount = Lease::query()
            ->whereHas('propertyUnit.property', fn ($query) => $query->where('user_id', $owner->id))
            ->where('status', \App\Enums\LeaseStatus::Active->value)
            ->count();

        $monthlyReport = $ownerReportService->build($owner, ['period' => 'this_month']);
        $yearReport = $ownerReportService->build($owner, ['period' => 'this_year']);

        $invoices = Invoice::query()
            ->whereHas('lease.propertyUnit.property', fn ($query) => $query->where('user_id', $owner->id))
            ->with(['payments', 'lease.propertyUnit.property', 'lease.tenantProfile'])
            ->latest('issue_date')
            ->get();

        $paidInvoicesCount = $invoices->filter(fn (Invoice $invoice) => $this->outstandingAmount($invoice) <= 0)->count();
        $unpaidInvoices = $invoices->filter(fn (Invoice $invoice) => $this->outstandingAmount($invoice) > 0)->values();
        $overdueInvoices = $unpaidInvoices
            ->filter(fn (Invoice $invoice) => $invoice->due_date->lt(now()))
            ->values();

        return [
            'counts' => [
                'properties' => $propertiesCount,
                'units' => $unitsCount,
                'tenants' => $tenantsCount,
                'active_leases' => $activeLeasesCount,
                'paid_invoices' => $paidInvoicesCount,
                'unpaid_invoices' => $unpaidInvoices->count(),
            ],
            'monthly' => $monthlyReport['overview'],
            'yearly' => $yearReport['overview'],
            'recent_invoices' => $invoices
                ->take(5)
                ->map(fn (Invoice $invoice): array => [
                    'number' => $invoice->number,
                    'property' => $invoice->lease->propertyUnit->property->name,
                    'unit' => $invoice->lease->propertyUnit->name,
                    'tenant' => $invoice->lease->tenantProfile->full_name,
                    'status' => $invoice->status->label(),
                    'total' => (float) $invoice->total,
                    'outstanding' => $this->outstandingAmount($invoice),
                ]),
            'overdue_invoices' => $overdueInvoices
                ->sortBy('due_date')
                ->take(5)
                ->map(fn (Invoice $invoice): array => [
                    'number' => $invoice->number,
                    'property' => $invoice->lease->propertyUnit->property->name,
                    'tenant' => $invoice->lease->tenantProfile->full_name,
                    'due_date' => $invoice->due_date,
                    'outstanding' => $this->outstandingAmount($invoice),
                ]),
            'top_properties' => $yearReport['property_performance']->take(3),
        ];
    }

    private function outstandingAmount(Invoice $invoice): float
    {
        $paid = (float) $invoice->payments->sum(fn (Payment $payment) => (float) $payment->amount);

        return max((float) $invoice->total - $paid, 0);
    }

    /**
     * @return array<string, mixed>
     */
    private function tenantDashboardData(\App\Models\User $tenant, InvoiceStatusService $invoiceStatusService): array
    {
        $tenantProfile = $tenant->tenantProfile;

        if (! $tenantProfile) {
            return [
                'tenant_profile' => null,
                'counts' => [
                    'active_leases' => 0,
                    'properties' => 0,
                    'paid_invoices' => 0,
                    'unpaid_invoices' => 0,
                    'meters' => 0,
                ],
                'outstanding_total' => 0.0,
                'next_invoice' => null,
                'leases' => collect(),
                'recent_invoices' => collect(),
            ];
        }

        $leases = Lease::query()
            ->where('tenant_profile_id', $tenantProfile->id)
            ->with(['propertyUnit.property', 'chargeRules'])
            ->orderByRaw('case when status = ? then 0 else 1 end', [\App\Enums\LeaseStatus::Active->value])
            ->orderByDesc('start_date')
            ->get();

        $invoices = Invoice::query()
            ->whereHas('lease', fn ($query) => $query->where('tenant_profile_id', $tenantProfile->id))
            ->with(['payments', 'lease.propertyUnit.property'])
            ->latest('issue_date')
            ->get()
            ->map(fn (Invoice $invoice) => $invoiceStatusService->refresh($invoice));

        $unpaidInvoices = $invoices
            ->filter(fn (Invoice $invoice) => $this->outstandingAmount($invoice) > 0)
            ->values();

        return [
            'tenant_profile' => $tenantProfile,
            'counts' => [
                'active_leases' => $leases->where('status', \App\Enums\LeaseStatus::Active)->count(),
                'properties' => $leases->pluck('propertyUnit.property_id')->filter()->unique()->count(),
                'paid_invoices' => $invoices->filter(fn (Invoice $invoice) => $this->outstandingAmount($invoice) <= 0)->count(),
                'unpaid_invoices' => $unpaidInvoices->count(),
                'meters' => Meter::query()
                    ->whereHas('propertyUnit.leases', fn ($query) => $query
                        ->where('tenant_profile_id', $tenantProfile->id)
                        ->where('status', \App\Enums\LeaseStatus::Active->value))
                    ->where('is_active', true)
                    ->count(),
            ],
            'outstanding_total' => $unpaidInvoices->sum(fn (Invoice $invoice) => $this->outstandingAmount($invoice)),
            'next_invoice' => $unpaidInvoices
                ->sortBy('due_date')
                ->map(fn (Invoice $invoice): array => [
                    'id' => $invoice->id,
                    'number' => $invoice->number,
                    'property' => $invoice->lease->propertyUnit->property->name,
                    'unit' => $invoice->lease->propertyUnit->name,
                    'due_date' => $invoice->due_date,
                    'outstanding' => $this->outstandingAmount($invoice),
                ])
                ->first(),
            'leases' => $leases->take(3),
            'recent_invoices' => $invoices
                ->take(5)
                ->map(fn (Invoice $invoice): array => [
                    'id' => $invoice->id,
                    'number' => $invoice->number,
                    'property' => $invoice->lease->propertyUnit->property->name,
                    'unit' => $invoice->lease->propertyUnit->name,
                    'due_date' => $invoice->due_date,
                    'status' => $invoice->status->label(),
                    'outstanding' => $this->outstandingAmount($invoice),
                    'total' => (float) $invoice->total,
                ]),
        ];
    }
}

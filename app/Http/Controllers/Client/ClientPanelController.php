<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\TenantProfile;
use App\Services\OwnerReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ClientPanelController extends Controller
{
    public function __invoke(Request $request, OwnerReportService $ownerReportService): View
    {
        $user = $request->user();

        return view('client.panel', [
            'user' => $user,
            'ownerDashboard' => $user->isOwner()
                ? $this->ownerDashboardData($user, $ownerReportService)
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
}

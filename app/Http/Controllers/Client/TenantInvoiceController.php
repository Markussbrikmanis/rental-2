<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\TenantProfile;
use App\Services\InvoiceDocumentService;
use App\Services\InvoiceStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TenantInvoiceController extends Controller
{
    public function index(Request $request, InvoiceStatusService $invoiceStatusService): View
    {
        /** @var TenantProfile|null $tenantProfile */
        $tenantProfile = $request->user()->tenantProfile;

        $invoices = $tenantProfile
            ? Invoice::query()
                ->whereHas('lease', fn ($query) => $query->where('tenant_profile_id', $tenantProfile->id))
                ->with(['lease.propertyUnit.property', 'payments'])
                ->latest('issue_date')
                ->get()
                ->map(fn (Invoice $invoice) => $invoiceStatusService->refresh($invoice))
            : collect();

        return view('client.tenant.invoices.index', [
            'tenantProfile' => $tenantProfile,
            'invoices' => $invoices,
        ]);
    }

    public function show(Request $request, Invoice $invoice, InvoiceStatusService $invoiceStatusService): View
    {
        $this->ensureTenantInvoice($request, $invoice);

        return view('client.tenant.invoices.show', [
            'invoice' => $invoiceStatusService->refresh($invoice->load([
                'lease.propertyUnit.property',
                'lease.tenantProfile',
                'lines',
                'payments',
            ])),
        ]);
    }

    public function download(Request $request, Invoice $invoice, InvoiceDocumentService $invoiceDocumentService): Response
    {
        $this->ensureTenantInvoice($request, $invoice);

        return $invoiceDocumentService->pdfResponse($invoice);
    }

    public function print(Request $request, Invoice $invoice, InvoiceDocumentService $invoiceDocumentService): View
    {
        $this->ensureTenantInvoice($request, $invoice);

        return $invoiceDocumentService->view($invoice);
    }

    private function ensureTenantInvoice(Request $request, Invoice $invoice): void
    {
        $tenantProfileId = $request->user()->tenantProfile?->id;

        abort_unless($tenantProfileId !== null && $invoice->lease->tenant_profile_id === $tenantProfileId, 404);
    }
}

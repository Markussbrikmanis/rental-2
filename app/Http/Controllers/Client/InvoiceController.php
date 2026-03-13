<?php

namespace App\Http\Controllers\Client;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceDocumentService;
use App\Services\InvoiceStatusService;
use App\Services\ReminderDispatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request, InvoiceStatusService $invoiceStatusService): View
    {
        $invoiceStatusService->refreshAll(now());

        return view('client.invoices.index', [
            'invoices' => Invoice::query()
                ->whereHas('lease.propertyUnit.property', fn ($query) => $query->where('user_id', $request->user()->id))
                ->with(['lease.propertyUnit.property', 'lease.tenantProfile', 'payments'])
                ->latest()
                ->get(),
            'statuses' => InvoiceStatus::cases(),
        ]);
    }

    public function show(Request $request, Invoice $invoice, InvoiceStatusService $invoiceStatusService): View
    {
        $this->ensureOwnedInvoice($request, $invoice);

        return view('client.invoices.show', [
            'invoice' => $invoiceStatusService->refresh($invoice->load([
                'lease.propertyUnit.property',
                'lease.tenantProfile',
                'lines',
                'payments',
                'reminders',
            ])),
            'customLine' => new \App\Models\InvoiceLine([
                'quantity' => 1,
                'unit_price' => 0,
                'tax' => 0,
            ]),
        ]);
    }

    public function update(Request $request, Invoice $invoice, InvoiceStatusService $invoiceStatusService): RedirectResponse
    {
        $this->ensureOwnedInvoice($request, $invoice);

        $validated = $request->validate(
            [
                'number' => ['required', 'string', 'max:255'],
                'issue_date' => ['required', 'date'],
                'due_date' => ['required', 'date'],
                'period_from' => ['required', 'date'],
                'period_to' => ['required', 'date', 'after_or_equal:period_from'],
                'status' => ['required', \Illuminate\Validation\Rule::in(\App\Enums\InvoiceStatus::values())],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        $invoice->update($validated);
        $invoiceStatusService->recalculate($invoice->fresh());

        return redirect()
            ->route('client.invoices.show', $invoice)
            ->with('status', __('app.rental.invoices.messages.updated', ['number' => $invoice->number]));
    }

    public function issue(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->ensureOwnedInvoice($request, $invoice);

        $invoice->update([
            'status' => InvoiceStatus::Issued,
        ]);

        return redirect()
            ->route('client.invoices.show', $invoice)
            ->with('status', __('app.rental.invoices.messages.issued', ['number' => $invoice->number]));
    }

    public function cancel(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->ensureOwnedInvoice($request, $invoice);

        $invoice->update([
            'status' => InvoiceStatus::Cancelled,
        ]);

        return redirect()
            ->route('client.invoices.show', $invoice)
            ->with('status', __('app.rental.invoices.messages.cancelled', ['number' => $invoice->number]));
    }

    public function send(Request $request, Invoice $invoice, ReminderDispatchService $reminderDispatchService): RedirectResponse
    {
        $this->ensureOwnedInvoice($request, $invoice);
        $reminderDispatchService->sendInvoice($invoice->load('lease.tenantProfile'));

        return redirect()
            ->route('client.invoices.show', $invoice)
            ->with('status', __('app.rental.invoices.messages.sent', ['number' => $invoice->number]));
    }

    public function remind(Request $request, Invoice $invoice, ReminderDispatchService $reminderDispatchService): RedirectResponse
    {
        $this->ensureOwnedInvoice($request, $invoice);
        $reminderDispatchService->sendOverdueInvoice($invoice->load('lease.tenantProfile'));

        return redirect()
            ->route('client.invoices.show', $invoice)
            ->with('status', __('app.rental.invoices.messages.reminded', ['number' => $invoice->number]));
    }

    public function destroy(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->ensureOwnedInvoice($request, $invoice);

        $number = $invoice->number;
        $invoice->delete();

        return redirect()
            ->route('client.invoices.index')
            ->with('status', __('app.rental.invoices.messages.deleted', ['number' => $number]));
    }

    public function download(Request $request, Invoice $invoice, InvoiceDocumentService $invoiceDocumentService): Response
    {
        $this->ensureOwnedInvoice($request, $invoice);

        return $invoiceDocumentService->pdfResponse($invoice);
    }

    public function print(Request $request, Invoice $invoice, InvoiceDocumentService $invoiceDocumentService): View
    {
        $this->ensureOwnedInvoice($request, $invoice);

        return $invoiceDocumentService->view($invoice);
    }

    private function ensureOwnedInvoice(Request $request, Invoice $invoice): void
    {
        abort_unless($invoice->lease->propertyUnit->property->user_id === $request->user()->id, 404);
    }
}

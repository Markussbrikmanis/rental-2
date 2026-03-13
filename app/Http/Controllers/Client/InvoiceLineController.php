<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Services\InvoiceStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoiceLineController extends Controller
{
    public function store(Request $request, Invoice $invoice, InvoiceStatusService $invoiceStatusService): RedirectResponse
    {
        $this->ensureOwnedInvoice($request, $invoice);

        $invoice->lines()->create($this->validatedData($request));
        $invoiceStatusService->recalculate($invoice->fresh());

        return redirect()
            ->route('client.invoices.show', $invoice)
            ->with('status', __('app.rental.invoice_lines.messages.created'));
    }

    public function update(Request $request, Invoice $invoice, InvoiceLine $line, InvoiceStatusService $invoiceStatusService): RedirectResponse
    {
        $this->ensureOwnedInvoice($request, $invoice);
        abort_unless($line->invoice_id === $invoice->id, 404);

        $validated = $this->validatedData($request);

        if ($line->source_type !== null) {
            $validated['is_manual_override'] = true;
        }

        $line->update($validated);
        $invoiceStatusService->recalculate($invoice->fresh());

        return redirect()
            ->route('client.invoices.show', $invoice)
            ->with('status', __('app.rental.invoice_lines.messages.updated'));
    }

    public function destroy(Request $request, Invoice $invoice, InvoiceLine $line, InvoiceStatusService $invoiceStatusService): RedirectResponse
    {
        $this->ensureOwnedInvoice($request, $invoice);
        abort_unless($line->invoice_id === $invoice->id, 404);
        abort_unless($line->source_type === null, 422);

        $line->delete();
        $invoiceStatusService->recalculate($invoice->fresh());

        return redirect()
            ->route('client.invoices.show', $invoice)
            ->with('status', __('app.rental.invoice_lines.messages.deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate(
            [
                'description' => ['required', 'string', 'max:255'],
                'quantity' => ['required', 'numeric', 'min:0.01'],
                'unit_price' => ['required', 'numeric', 'min:0'],
                'tax' => ['nullable', 'numeric', 'min:0'],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        $quantity = (float) $validated['quantity'];
        $unitPrice = (float) $validated['unit_price'];
        $tax = (float) ($validated['tax'] ?? 0);

        $validated['tax'] = $tax;
        $validated['line_total'] = round(($quantity * $unitPrice) + $tax, 2);

        return $validated;
    }

    private function ensureOwnedInvoice(Request $request, Invoice $invoice): void
    {
        abort_unless($invoice->lease->propertyUnit->property->user_id === $request->user()->id, 404);
    }
}
